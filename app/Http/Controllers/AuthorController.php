<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthorController extends Controller
{
    private $validation_rules_for_create = [
        'first_name' => 'required|max:100',
        'last_name' => 'required|max:100'
    ];

    private $validation_rules_for_update = [
        'first_name' => 'sometimes|required|max:100',
        'last_name' => 'sometimes|required|max:100'
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

    public function getAuthors()
    {
        try {
            $this->response['results'] = Author::all();
        } catch (\Throwable $th) {
            $this->response['status'] = 500;
            $this->response['message'] = $th->getMessage();
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function getAuthorById($id)
    {
        $validator = $this->getValidationFactory()->make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Author::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Author Not found';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function createAuthor(Request $request)
    {
        $validator = $this->getValidationFactory()->make($request->post(), $this->validation_rules_for_create);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Author::create($request->all());
                $this->response['message'] = 'Author has been successfully created';
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function updateAuthorById(Request $request, $id)
    {
        $validator = $this->getValidationFactory()->make(
                array_merge($request->all(), ['id' => $id]), 
                array_merge($this->validation_rules_for_update, ['id' => 'required|numeric'])
        );
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Author::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Author Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                $this->response['results']->fill($request->all());
                
                if ($this->response['results']->save()) {
                    $this->response['message'] = 'Author data has been successfully updated';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Author data failed to update';
                } 
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function deleteAuthorById($id)
    {
        $validator = $this->getValidationFactory()->make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Author::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Author Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($this->response['results']->delete()) {
                    $this->response['message'] = 'Author data removed successfully';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Author data failed to remove';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }
}
