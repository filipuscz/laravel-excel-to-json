<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvToJsonController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/convert-csv', [CsvToJsonController::class, 'convert']);

Route::get('/convert', function () {
    return view('convert');
});

use App\Http\Controllers\ExcelToJsonController;

Route::post('/convert-excel-to-json', [ExcelToJsonController::class, 'convert']);
