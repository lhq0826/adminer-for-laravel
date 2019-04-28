<?php
namespace Lumener\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class LumenerController extends Controller
{
    protected $adminer;
    protected $adminer_object;
    protected $plugins_path;
    protected $allowed_dbs;
    protected $protected_dbs;
    protected $request;
    public function __construct(Request $request)
    {
        if (method_exists(\Route::class, 'hasMiddlewareGroup')
        && \Route::hasMiddlewareGroup('lumener')) {
            $this->middleware('lumener');
        }
        // LumenerServiceProvider::register holds the middleware register so it does not need addeed manually.
        // User-defined middleware is handled during route definition for Lumen
        $this->adminer = LUMENER_STORAGE.'/adminer.php';
        $this->adminer_object = __DIR__.'/../logic/adminer_object.php';
        $this->plugins_path = LUMENER_STORAGE.'/plugins';
        $this->request = $request;
    }

    public function index()
    {
        if ($this->request->cookie('adminer_logged_out')
            && config('lumener.logout_redirect')) {
            return redirect(config('lumener.logout_redirect'));
        }
        if (isset($_POST['logout'])) {
            $t = encrypt(time());
            $h = "Set-Cookie: adminer_logged_out={$t}; expires=".gmdate(
                "D, d M Y H:i:s",
                time() + config('lumener.logout_cooldown', 10)
            )." GMT; path=".preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"]);
            header($h);
        }
        if (file_exists($this->adminer)) {
            return $this->_runAdminer();
        } else {
            return '<div style="text-align:center;color: red;
                                margin-top: 200px;font-weight:bold;">
                      Adminer was NOT found.
                      Run <span style="color:lightgreen;background:black;
                                       padding: 5px;border: 5px dashed white;">
                                       php artisan lumener:update --force</span>
                                       to fix any issues.
                    </div>
            ';
        }
    }

    public function update()
    {
        Artisan::call('lumener:update');
        return nl2br(Artisan::output());
    }

    public function getResource()
    {
        $file = $this->request->get('file');
        $path = realpath(LUMENER_STORAGE."/{$file}");
        // Prevent risky file fetching
        // This check is very important, it's a major security risk to allow
        // Fetching files outside the LUMENER_STORAGE directory
        if (
            $path === false
            || strncmp($path, LUMENER_STORAGE, strlen(LUMENER_STORAGE)) !== 0
        ) {
            abort(403);
        }
        $type = $this->request->get('type', mime_content_type($path));
        return response()->download($path, $file, ["Content-Type"=>$type]);
    }

    public function isDBBlocked($db)
    {
        return
        (
            $this->allowed_dbs !== null
            && !in_array($db, $this->allowed_dbs)
        )
        ||
        (
            $this->protected_dbs !== null
            && in_array($db, $this->protected_dbs)
        );
    }

    private function _runAdminer()
    {
        if (!isset($_GET['username']) && !isset($_POST['auth'])
            && config('lumener.auto_login')
            && !$this->request->cookie('adminer_logged_out')) {
            // Skip login screen
            $_GET['username'] =
                config('lumener.db.username', env("DB_USERNAME"));
            $_GET['db'] =
                config('lumener.db.database', env("DB_DATABASE"));
            // Password is set in the adminer extension
        }
        // Security Check
        $this->allowed_dbs = config('lumener.security.allowed_db');
        $this->protected_dbs = config('lumener.security.protected_db');

        if ((isset($_GET['db']) && $_GET['db']
            && $this->isDBBlocked($_GET['db']))
        || (isset($_POST['auth']['db']) && $_POST['auth']['db']
            && $this->isDBBlocked($_POST['auth']['db']))) {
            abort(403);
        }

        $content =
            $this->_runGetBuffer([$this->adminer_object, $this->adminer]);

        if (strpos($content, "<!DOCTYPE html>") === false) {
            die($content);
        }
        return $content;
    }

    private function _runGetBuffer($files, $allowed_exceptions=[E_WARNING])
    {
        // Require files
        ob_implicit_flush(0);
        ob_start();
        try {
            foreach ($files as $file) {
                require($file);
            }
        } catch (\Exception $e) {
            if (!in_array($e->getSeverity(), $allowed_exceptions)) {
                throw $e;
            }
        }
        $content = "";
        while ($level = ob_get_clean()) {
            $content = $level . $content;
        }
        return $content;
    }
}
