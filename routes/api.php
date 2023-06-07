<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    SendHttpRequirementsController
};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/requirements', function (Request $request) {
    $conection = $request->input('event');
    if (isset($conection))
    {
        $obj = new SendHttpRequirementsController();
        $header = $obj->headers();
        $body = $obj->main($request);
        $response = Http::withHeaders($header)->post('https://api.inovstar.com/core/v2/api/chats/send-text', $body);
        return dd($response);
    }
    else
    {
        return $request->getContent();
    }
});
