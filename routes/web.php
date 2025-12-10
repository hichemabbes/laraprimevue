<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-file', function () {
    $path = storage_path('app/templates/centres_model.xlsm');

    return response()->json([
        'exists' => file_exists($path),
        'path' => $path,
    ]);
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
