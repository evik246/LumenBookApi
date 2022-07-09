<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
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

    public function getUsers()
    {
        try {
            $this->response['results'] = User::with('roles')->get();
        } catch (\Throwable $th) {
            $this->response['status'] = 500;
            $this->response['message'] = $th->getMessage();
        }
        return response()->json($this->response);
    }

    public function getUserById($client_id)
    {
        $validator = $this->getValidationFactory()->make(
            ['client_id' => $client_id], 
            ['client_id' => 'required|numeric']
        );

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = User::with('roles')->find($client_id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'User Not found';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function deleteUserById($client_id)
    {
        $validator = $this->getValidationFactory()->make(
            ['client_id' => $client_id], 
            ['client_id' => 'required|numeric']
        );

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = User::with('roles')->find($client_id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'User Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($this->response['results']->delete()) {
                    $this->response['message'] = 'User data removed successfully';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'User data failed to remove';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function changeUserRole($client_id, $role_id)
    {
        $validator = $this->getValidationFactory()->make(
            array_merge(['client_id' => $client_id], ['role_id' => $role_id]), 
            array_merge(['client_id' => 'required|numeric'], ['role_id' => 'required|numeric'])
        );

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $user = User::find($client_id);
                if ($user == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'User Not found';
                    return response()->json($this->response, $this->response['status']);
                }
                
                $role = Role::find($role_id);
                if ($role == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Role Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($user->roles()->find($role->id) != null && 
                    $role->name == $user->roles()->find($role->id)->name) {
                    $this->response['status'] = 409;
                    $this->response['message'] = 'User has already this role';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($role->name == 'Admin' || $role->name == 'Client') {
                    $user->roles()->detach();
                    $user->roles()->attach($role);
                    $this->response['message'] = 'User role has been changed';
                } else {
                    $user->roles()->attach($role);
                    $this->response['message'] = 'User role has been added';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function getFavouriteBooks($client_id)
    {
        $validator = $this->getValidationFactory()->make(
            ['client_id' => $client_id], 
            ['client_id' => 'required|numeric']
        );

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $user = User::find($client_id);

                if ($user == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'User Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                $fav_books = $user->books()->with('language')->with('authors')->with('genres')->get();
                $this->response['results'] = $fav_books;
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function getFavouriteBookById($client_id, $book_id)
    {
        $validator = $this->getValidationFactory()->make(
            array_merge(['client_id' => $client_id], ['book_id' => $book_id]), 
            array_merge(['client_id' => 'required|numeric'], ['book_id' => 'required|numeric'])
        );

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $user = User::find($client_id);

                if ($user == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'User Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                $book = Book::find($book_id);
                if ($book == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Book Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                $fav_book = $user->books()->with('language')->with('authors')->with('genres')->find($book_id);
                if ($fav_book == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Book has been not found in favorites';
                    return response()->json($this->response, $this->response['status']);
                }

                $this->response['results'] = $fav_book;
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }
}
