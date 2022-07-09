<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function exportCSV() 
    {
        $filename = 'data.csv';
        $handle = fopen($filename, 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        $headers = array(
            "Content-Encoding" => "UTF-8",
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=data.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $books = Book::all();
        fputcsv($handle, array('ID', 'Title', 'Year', 'Cover', 'Language', 'Author', 'Genre', 'Pages', 'Description'), ';');

        foreach ($books as $book) {
            $language = $book->language()->first();
            $authors = $book->authors()->get();
            $genres = $book->genres()->get();

            $author_names = [];
            foreach ($authors as $author) {
                $author_names[] = $author->first_name . ' ' . $author->last_name;
            }

            $genres_names = [];
            foreach ($genres as $genre) {
                $genres_names[] = $genre->name;
            }

            $csvSingle['ID'] = $book->id;
            $csvSingle['Title'] = $book->title;
            $csvSingle['Year'] = $book->year;
            $csvSingle['Cover'] = $book->cover;
            $csvSingle['Language'] = $language->name;
            $csvSingle['Author'] = join(', ', $author_names);
            $csvSingle['Genre'] = join(', ', $genres_names);
            $csvSingle['Pages'] = $book->pages;
            $csvSingle['Description'] = $book->description;

            fputcsv($handle, $csvSingle, ';');
        }
        fclose($handle);
        return response()->download($filename, 'data.csv', $headers);
    }
}
