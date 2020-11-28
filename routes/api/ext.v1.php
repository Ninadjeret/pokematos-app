<?php

use Illuminate\Support\Facades\Route;

Route::get('stops', 'Ext\StopController@index')->middleware(['extcan:stops.get']);
Route::get('raids', 'Ext\RaidController@index')->middleware(['extcan:raids.get']);
Route::post('raids', 'Ext\RaidController@store')->middleware(['extcan:raids.post']);