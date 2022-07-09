<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LanguageController extends Controller
{
    private $validation_rules = [
        'name' => 'required|max:40'
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
    }

    public function getLanguages()
    {
        try {
            $this->response['results'] = Language::all();
        } catch (\Throwable $th) {
            $this->response['status'] = 500;
            $this->response['message'] = $th->getMessage();
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function getLanguageById($id)
    {
        $validator = $this->getValidationFactory()->make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Language::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Language Not found';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function createLanguage(Request $request)
    {
        $validator = $this->getValidationFactory()->make($request->post(), $this->validation_rules);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Language::create($request->all());
                $this->response['message'] = 'Language has been successfully created';
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function updateLanguageById(Request $request, $id)
    {
        $validator = $this->getValidationFactory()->make(
                array_merge($request->all(), ['id' => $id]), 
                array_merge($this->validation_rules, ['id' => 'required|numeric'])
        );
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Language::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Language Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                $this->response['results']->fill($request->all());
                
                if ($this->response['results']->save()) {
                    $this->response['message'] = 'Language data has been successfully updated';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Language data failed to update';
                } 
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function deleteLanguageById($id)
    {
        $validator = $this->getValidationFactory()->make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Language::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Language Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($this->response['results']->delete()) {
                    $this->response['message'] = 'Language data removed successfully';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Language data failed to remove!';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    
    }
}
