<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (\Solarium\Client $client) {
    try {
        $result = $client->ping($client->createPing());

        $status = $result->getStatus();
    } catch (\Solarium\Exception\ExceptionInterface $e) {
        $status = -1;
    }

    return view('welcome')->with(compact('status'));
});
