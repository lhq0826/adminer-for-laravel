<?php
namespace Simple\Adminer\Controllers;

use App\Http\Controllers\Controller;

class AdminerController extends Controller
{
    public function __construct()
    {
        $this->middleware('adminer');
    }

    public function index()
    {
        require(__DIR__.'/../resources/adminer-4.3.0-en.php');
    }
}