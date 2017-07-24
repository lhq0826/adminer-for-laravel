<?php
namespace Simple\Adminer\Controllers;

use Illuminate\Routing\Controller;

class AdminerController extends Controller
{
    public function __construct()
    {
        if(\Route::hasMiddlewareGroup('adminer')){
            $this->middleware('adminer');
        }
    	// AdminerServiceProvider::register holds the middleware register so it does not need addeed manually.
    }

    public function index()
    {
        require(__DIR__.'/../resources/adminer-4.3.0-en.php');
    }
}
