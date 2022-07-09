<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Book;
use Carbon\Carbon;

class ClientController extends Controller
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

    public function getFavouriteBooks(Request $request)
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

                $fav_books = $user->books()->with('language')->with('authors')->with('genres')->get();
                $this->response['results'] = $fav_books;
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function getFavouriteBookById(Request $request, $book_id)
    {
        $validator = $this->getValidationFactory()->make(
            array_merge($request->post(), ['book_id' => $book_id]),
            array_merge(['user_id' => 'required|numeric'], ['book_id' => 'required|numeric'])
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

    public function addFavouriteBookById(Request $request, $book_id)
    {
        $validator = $this->getValidationFactory()->make(
            array_merge($request->post(), ['book_id' => $book_id]),
            array_merge(['user_id' => 'required|numeric'], ['book_id' => 'required|numeric'])
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

                $book = Book::find($book_id);
                if ($book == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Book Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                $fav_book = $user->books()->find($book_id);
                if ($fav_book != null) {
                    $this->response['status'] = 409;
                    $this->response['message'] = 'Book already exists in favorites';
                    return response()->json($this->response, $this->response['status']);
                }

                $user->books()->attach($book);
                if ($user->save()) {
                    $this->response['results'] = $book->with('language')->with('authors')->with('genres')->get();
                    $this->response['message'] = 'Book has been added to favourite';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Book failed to add';
                }

            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function deleteFavouriteBookById(Request $request, $book_id)
    {
        $validator = $this->getValidationFactory()->make(
            array_merge($request->post(), ['book_id' => $book_id]),
            array_merge(['user_id' => 'required|numeric'], ['book_id' => 'required|numeric'])
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

                $book = Book::find($book_id);
                if ($book == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Book Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                $fav_book = $user->books()->find($book_id);
                if ($fav_book == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Book has been not found in favorites';
                    return response()->json($this->response, $this->response['status']);
                }

                $user->books()->detach($book);
                if ($user->save()) {
                    $this->response['results'] = $book->with('language')->with('authors')->with('genres')->get();
                    $this->response['message'] = 'Book has been removed from favourite';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Book failed to remove';
                }

            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }
}
