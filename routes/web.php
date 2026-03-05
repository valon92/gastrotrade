<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

// Temporary installer route for environments without SSH/Terminal access.
// IMPORTANT: Configure INSTALL_KEY in .env on the server and remove this route after first use.
// Note: Use a path that does not conflict with existing files like /artisan.
Route::get('/install', function (Request $request) {
    if ($request->query('key') !== env('INSTALL_KEY')) {
        abort(403);
    }

    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('db:seed', ['--force' => true]);

    return response()->json([
        'status' => 'ok',
        'migrate_output' => Artisan::output(),
    ]);
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
