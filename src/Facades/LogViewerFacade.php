<?php namespace Genetsis\LogViewer\Facades;

use Illuminate\Support\Facades\Facade;

class LogViewerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Logviewer';
    }
}