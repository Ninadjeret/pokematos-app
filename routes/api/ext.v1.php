<?php

use Illuminate\Support\Facades\Route;

Route::get('name', 'Controller@test')->middleware(['extcan:raids.post']);