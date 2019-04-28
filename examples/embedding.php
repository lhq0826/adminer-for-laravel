<?php
class AdminController
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function lumener()
    {
        // If you are using a Content Seucrity Policy, define it here
        define("LUMENER_CSP", [["form-action" => "'self'"]]);
        $controller = new \Lumener\Controllers\LumenerController($this->request);
        $content = $controller->index();
        return view('admin.dashboard', ['content' => $content]);
    }
}
// Don't forget to use {!! $content !!} in blade as $content is HTML
