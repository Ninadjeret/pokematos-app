<?php

use Illuminate\Support\Facades\Route;

Route::post('raids', 'Ext\RaidController@store')->middleware(['extcan:raids.post']);