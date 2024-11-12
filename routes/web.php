<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvToJsonController;
use App\Http\Controllers\ExcelToJsonSingleController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/convert-csv', [CsvToJsonController::class, 'convert']);

Route::get('/convert', function () {
    return view('convert');
});



Route::post('/convert-excel-to-json', [ExcelToJsonSingleController::class, 'convert']);
