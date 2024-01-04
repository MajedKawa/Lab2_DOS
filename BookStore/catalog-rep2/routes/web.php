<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/catalog', 'CatalogController@index'); // To list all books
$router->get('/catalog/{id}', 'CatalogController@show'); // To view a specific book
$router->get('/catalog/search/{topic}', 'CatalogController@search'); // To search the books based on the topic
$router->put('/catalog/{id}', 'CatalogController@update'); // To update a book

