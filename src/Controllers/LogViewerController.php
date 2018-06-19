<?php namespace Genetsis\LogViewer\Controllers;

use Genetsis\Admin\Controllers\AdminController;
use Genetsis\LogViewer\LogViewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogViewerController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        \View::share('section', 'logs');
    }


    public function index($file = null, Request $request, LogViewer $logViewer) {

        if ($file === null) {
            $file = $logViewer->getLastModifiedLog();
        }

        $offset = $request->get('offset');
        $viewer = new LogViewer($file);

        $logs = $viewer->fetch($offset);


        $size = static::bytesToHuman($viewer->getFilesize());

        return view('logviewer::logviewer.index', compact('viewer','logs', 'offset', 'size'));
    }

    public function delete($file) {
        try {
            $viewer = new LogViewer($file);
            $viewer->delete();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return redirect(route('log-viewer-home'));
    }

    protected static function bytesToHuman($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2).' '.$units[$i];
    }
}
