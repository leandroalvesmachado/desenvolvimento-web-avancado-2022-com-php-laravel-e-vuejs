<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::resource('clientes', 'App\Http\Controllers\ClienteController');

Route::apiResource('carros', 'CarroController');
Route::apiResource('clientes', 'ClienteController');
Route::apiResource('locacoes', 'LocacaoController');
Route::apiResource('marcas', 'MarcaController');
Route::apiResource('modelos', 'ModeloController');


