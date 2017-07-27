<?php
namespace Simple\Adminer\Controllers;

use Illuminate\Routing\Controller;

class AdminerController extends Controller
{
    protected $adminer;
    public function __construct()
    {
	    if(\Route::hasMiddlewareGroup('adminer')){
		    $this->middleware('adminer');
	    }
    	// AdminerServiceProvider::register holds the middleware register so it does not need addeed manually.

        $this->adminer = __DIR__.'/../resources/adminer.php';
    }

    public function index()
    {
        if(file_exists($this->adminer )){
            require($this->adminer);
        } else {
            return view('adminer::not_found');
        }
    }
}
