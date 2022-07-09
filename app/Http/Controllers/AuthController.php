<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $tokenService;

    private $validation_rules_register = [
        'name' => 'required|max:200',
        'email' => 'required|email|max:200',
        'password' => 'required|max:40'
    ];

    private $validation_rules_login = [
        'email' => 'required|email|max:200',
        'password' => 'required|max:40'
    ];

    private $response = [];

    private function basic_responce()
    {
        return [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'status' => 200,
            'message' => '',
            'results' => []
        ];
    }

    public function __construct()
    {
        $this->response = $this->basic_responce();
        $this->tokenService = new TokenService();
    }

    public function register(Request $request)
    {
        $validator = $this->getValidationFactory()->make(
            $request->post(), 
            $this->validation_rules_register
        );

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $user_with_email = User::where('email', '=', $request->email)->first();
                if ($user_with_email != null) {
                    $this->response['status'] = 409;
                    $this->response['message'] = 'This email address is already being used';
                    return response()->json($this->response, $this->response['status']);
                }

                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);

                if ($user->save()) {
                    $this->response['message'] = 'Registration is successful';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Registration is failed';
                }

                $role_client = Role::where('name', '=', 'Client')->first();
                $user->roles()->attach($role_client);

                if ($user->save()) {
                    $this->response['message'] = 'Registration is successful';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Registration is failed';
                }

                $this->response['results'] = $user;
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function login(Request $request)
    {
        $validator = $this->getValidationFactory()->make(
            $request->post(), 
            $this->validation_rules_login
        );

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $user = User::where('email', '=', $request->email)->first();
                if ($user == null) {
                    $this->response['status'] = 401;
                    $this->response['message'] = 'Invalid email address or password';
                    return response()->json($this->response, $this->response['status']);
                }

                if (Hash::check($request->password, $user->password)) {
                    $apikey = $this->tokenService->makeApiKey();
                    User::where('email', '=', $request->email)->update(['api_key' => $apikey]);

                    $this->response['results'] = ['api_key' => $apikey];
                    $this->response['message'] = 'Login is successful';
                } else {
                    $this->response['status'] = 401;
                    $this->response['message'] = 'Invalid email address or password';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function logout(Request $request)
    {
        $validator = $this->getValidationFactory()->make(
            $request->post(),
            ['user_id' => 'required|numeric']
        );

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $user = User::find($request->user_id);
                if ($user == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'User Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($user->api_key != null) {
                    $user->api_key = null;
                    $user->save();
                    $this->response['message'] = 'Successfully logged out';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Failed to log out';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }
}
