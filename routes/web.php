<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Web routes are handled by Filament admin panel.
| The main frontend is served by Next.js separately.
|
*/

Route::get('/', function () {
    return redirect('/admin');
});
