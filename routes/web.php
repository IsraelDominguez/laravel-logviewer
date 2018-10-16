<?php

Route::group(['middleware' => ['web']], function () {
    Route::prefix('admin')->group(function () {
        Route::group(['middleware' => ['role:SuperAdmin'],'namespace' => 'Genetsis\LogViewer\Controllers'], function () {
            Route::get('logviewer', 'LogViewerController@index')->name('log-viewer-home');
            Route::get('logviewer/{file}', 'LogViewerController@index')->name('log-viewer-file');
            Route::get('logviewer/{file}/delete', 'LogViewerController@delete')->name('log-viewer-delete');
        });
    });
});



