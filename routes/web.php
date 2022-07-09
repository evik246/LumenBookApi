<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api/'], function () use ($router) {
    // Логин и регистрация
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    // Просмотр книг
    $router->get('books', 'BookController@getBooks');
    $router->get('books/{id}', 'BookController@getBookById');

    // Просмотр языков, на которых написаны книги
    $router->get('languages', 'LanguageController@getLanguages');
    $router->get('languages/{id}', 'LanguageController@getLanguageById');

    // Просмотр авторов
    $router->get('authors', 'AuthorController@getAuthors');
    $router->get('authors/{id}', 'AuthorController@getAuthorById');

    // Просмотр жанров
    $router->get('genres', 'GenreController@getGenres');
    $router->get('genres/{id}', 'GenreController@getGenreById');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        // Выход из аккаунта (сброс токена)
        $router->post('logout', 'AuthController@logout');

        // Просмотр, добавление и удаление книг из избранного пользователя
        $router->post('user/books', 'ClientController@getFavouriteBooks');
        $router->post('user/books/{book_id}', 'ClientController@getFavouriteBookById');
        $router->post('user/books/add/{book_id}', 'ClientController@addFavouriteBookById');
        $router->delete('user/books/remove/{book_id}', 'ClientController@deleteFavouriteBookById');

        $router->group(['middleware' => 'admin'], function () use ($router) {
            // Экспорт книг в csv формате
            $router->get('csv', 'FileController@exportCSV');

            // Добавление, изменение и удаление языков
            $router->post('languages', 'LanguageController@createLanguage');
            $router->post('languages/{id}', 'LanguageController@updateLanguageById');
            $router->delete('languages/{id}', 'LanguageController@deleteLanguageById');

            // Добавление, изменение и удаление книг
            $router->post('books', 'BookController@createBook');
            $router->post('books/{id}', 'BookController@updateBookById');
            $router->delete('books/{id}', 'BookController@deleteBookById');

            // Добавление, изменение и удаление авторов
            $router->post('authors', 'AuthorController@createAuthor');
            $router->post('authors/{id}', 'AuthorController@updateAuthorById');
            $router->delete('authors/{id}', 'AuthorController@deleteAuthorById');

            // Добавление, изменение и удаление жанров
            $router->post('genres', 'GenreController@createGenre');
            $router->post('genres/{id}', 'GenreController@updateGenreById');
            $router->delete('genres/{id}', 'GenreController@deleteGenreById');

            // Просмотр доступных ролей для пользователя
            $router->get('roles', 'RoleController@getRoles');
            $router->get('roles/{id}', 'RoleController@getRoleById');

            // Контроль над пользователями
            $router->get('users', 'UserController@getUsers');
            $router->get('users/{client_id}', 'UserController@getUserById');
            $router->delete('users/{client_id}', 'UserController@deleteUserById');
            $router->put('users/{client_id}/roles/{role_id}', 'UserController@changeUserRole');

            $router->get('users/{client_id}/books', 'UserController@getFavouriteBooks');
            $router->get('users/{client_id}/books/{book_id}', 'UserController@getFavouriteBookById');
        });
    });
});
