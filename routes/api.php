<?php

Route::get('/notes', 'NotesController@getNotes');
Route::post('/notes', 'NotesController@addNote');
Route::delete('/notes', 'NotesController@deleteNote');
Route::patch('/notes/{note}/pin', 'NotesController@pinNote');
Route::patch('/notes/{note}/complete', 'NotesController@completeNote');
Route::patch('/notes/{note}', 'NotesController@editNote');
Route::get('/users', 'NotesController@getAssignableUsers');
