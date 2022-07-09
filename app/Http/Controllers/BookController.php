<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Language;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BookController extends Controller
{
    private $validation_rules_for_create = [
        'title' => 'required|max:250',
        'language_id' => 'required|numeric',
        'pages' => 'nullable|numeric|gt:0',
        'description' => 'nullable',
        'cover' => 'required|mimes:png,jpg|max:2048',
        'author_ids' => 'required|array|min:1',
        'author_ids.*' => 'required|numeric',
        'genre_ids' => 'required|array|min:1',
        'genre_ids.*' => 'required|numeric'
    ];

    private $validation_rules_for_update = [
        'title' => 'sometimes|required|max:250',
        'language_id' => 'sometimes|required|numeric',
        'pages' => 'nullable|numeric|gt:0',
        'description' => 'nullable',
        'cover' => 'sometimes|required|mimes:png,jpg|max:2048',
        'author_ids' => 'sometimes|required|array|min:1',
        'author_ids.*' => 'required|numeric',
        'genre_ids' => 'sometimes|required|array|min:1',
        'genre_ids.*' => 'required|numeric'
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

    public function getBooks()
    {
        try {
            $this->response['results'] = Book::with('language')->with('authors')->with('genres')->get();
        } catch (\Throwable $th) {
            $this->response['status'] = 500;
            $this->response['message'] = $th->getMessage();
        }
        return response()->json($this->response);
    }

    public function getBookById($id)
    {
        $validator = $this->getValidationFactory()->make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $this->response['results'] = Book::with('language')->with('authors')->with('genres')->find($id);

                if ($this->response['results'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Book Not found';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function createBook(Request $request)
    {
        $rules = array_merge(
            $this->validation_rules_for_create, 
            ['year' => 'required|digits:4|integer|min:1900|max:'.(date('Y')+1)]
        );
        $validator = $this->getValidationFactory()->make($request->all(), $rules);

        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $language = Language::find($request->language_id);
                if ($language == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Language Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($request['author_ids'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Authors Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($request['genre_ids'] == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Genres Not found';
                    return response()->json($this->response, $this->response['status']);
                }
                
                $authors = array();
                foreach ($request['author_ids'] as $authorId) {
                    $author = Author::find($authorId);
                    if ($author == null) {
                        $this->response['status'] = 404;
                        $this->response['message'] = 'Author Not found';
                        return response()->json($this->response, $this->response['status']);
                    }
                    array_push($authors, $authorId);
                }

                $genres = array();
                foreach ($request['genre_ids'] as $genreId) {
                    $genre = Genre::find($genreId);
                    if ($genre == null) {
                        $this->response['status'] = 404;
                        $this->response['message'] = 'Genre Not found';
                        return response()->json($this->response, $this->response['status']);
                    }
                    array_push($genres, $genreId);
                }

                $book = new Book();
                $book->language()->associate($language)->fill($request->all());
                
                if ($book->save()) {
                    $this->response['message'] = 'Book data has been successfully created';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Book data failed to create';
                    return response()->json($this->response, $this->response['status']);
                }
                
                $book->authors()->attach($authors);
                
                $book->genres()->attach($genres);
                
                if ($request->hasFile('cover')) {
                    $file = $request->file('cover');
                    $new_name = time() . $file->getClientOriginalName();
                    $file->move('images/bookcovers', $new_name);
                    $book->cover = $new_name;
                } else {
                    $this->response['status'] = 422;
                    $this->response['message'] = 'No book cover selected';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($book->save()) {
                    $this->response['message'] = 'Book data has been successfully created';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Book data failed to create';
                }

                $this->response['results'] = $book->with('language')->with('authors')->with('genres')->find($book->id);
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function updateBookById(Request $request, $id)
    {
        $rules = array_merge(
            $this->validation_rules_for_update, 
            ['year' => 'sometimes|required|digits:4|integer|min:1900|max:'.(date('Y')+1)],
            ['id' => 'required|numeric']
        );
        $data = array_merge($request->all(), ['id' => $id]);
        $validator = $this->getValidationFactory()->make($data, $rules);
        
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $book = Book::find($id);
                if ($book == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Book Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($request->language_id != null) {
                    $language = Language::find($request->language_id);
                    if ($language == null) {
                        $this->response['status'] = 404;
                        $this->response['message'] = 'Language Not found';
                        return response()->json($this->response, $this->response['status']);
                    }
                    $book->language()->associate($language);
                }

                $authors = array();
                if ($request['author_ids'] != null) {
                    foreach ($request['author_ids'] as $authorId) {
                        $author = Author::find($authorId);
                        if ($author == null) {
                            $this->response['status'] = 404;
                            $this->response['message'] = 'Author Not found';
                            return response()->json($this->response, $this->response['status']);
                        }
                        array_push($authors, $authorId);
                        $book->authors()->detach();
                    }
                    $book->authors()->attach($authors);
                }

                $genres = array();
                if ($request['genre_ids'] != null) {
                    foreach ($request['genre_ids'] as $genreId) {
                        $genre = Author::find($genreId);
                        if ($genre == null) {
                            $this->response['status'] = 404;
                            $this->response['message'] = 'Genre Not found';
                            return response()->json($this->response, $this->response['status']);
                        }
                        array_push($genres, $genreId);
                        $book->genres()->detach();
                    }
                    $book->genres()->attach($genres);
                }

                $new_cover = $book->cover;
                $book->fill($request->all());

                if ($request->hasFile('cover')) {
                    if (File::exists('images/bookcovers/' . $new_cover)) {
                        File::delete('images/bookcovers/' . $new_cover);
                    } else {
                        $this->response['status'] = 500;
                        $this->response['message'] = 'Book cover failed to remove';
                        return response()->json($this->response, $this->response['status']);
                    }

                    $file = $request->file('cover');
                    $new_name = time() . $file->getClientOriginalName();
                    $file->move('images/bookcovers', $new_name);
                    $book->cover = $new_name;
                }

                if ($book->save()) {
                    $this->response['message'] = 'Book data has been successfully updated';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Book data failed to update';
                } 

                $this->response['results'] = $book->with('language')->with('authors')->with('genres')->find($book->id);
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }

    public function deleteBookById($id)
    {
        $validator = $this->getValidationFactory()->make(['id' => $id], ['id' => 'required|numeric']);
        if ($validator->fails()) {
            $this->response['status'] = 422;
            $this->response['message'] = $validator->errors();
        } else {
            try {
                $book = Book::find($id);

                if ($book == null) {
                    $this->response['status'] = 404;
                    $this->response['message'] = 'Book Not found';
                    return response()->json($this->response, $this->response['status']);
                }

                if (File::exists('images/bookcovers/' . $book->cover)) {
                    File::delete('images/bookcovers/' . $book->cover);
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Book cover failed to remove';
                    return response()->json($this->response, $this->response['status']);
                }

                if ($book->delete()) {
                    $this->response['message'] = 'Book data removed successfully';
                } else {
                    $this->response['status'] = 500;
                    $this->response['message'] = 'Book data failed to remove';
                }
            } catch (\Throwable $th) {
                $this->response['status'] = 500;
                $this->response['message'] = $th->getMessage();
            }
        }
        return response()->json($this->response, $this->response['status']);
    }
}
