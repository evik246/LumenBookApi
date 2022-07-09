<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GenreController extends Controller
{
    private $validation_rules = [
        'name' => 'required|max:50'
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

    public function getGenres()
    {
        try {
            $this->response['results'] = Genre::all();
        } catch (\Throwable $th) {
            $this->response['status'] = 500;
            $this->response['message'] = $th->getMessage();
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function getGenreById($id)
    {
        $validator = $this->getValidationFactory()->make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Genre::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Genre Not found';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function createGenre(Request $request)
    {
        $validator = $this->getValidationFactory()->make($request->post(), $this->validation_rules);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Genre::create($request->all());
                $this->response['message'] = 'Genre has been successfully created';
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function updateGenreById(Request $request, $id)
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
                $this->response['results'] = Genre::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Genre Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                $this->response['results']->fill($request->all());
                
                if ($this->response['results']->save()) {
                    $this->response['message'] = 'Genre data has been successfully updated';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Genre data failed to update';
                } 
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function deleteGenreById($id)
    {
        $validator = $this->getValidationFactory()->make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Genre::find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Genre Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($this->response['results']->delete()) {
                    $this->response['message'] = 'Genre data removed successfully';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Genre data failed to remove';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }
}
