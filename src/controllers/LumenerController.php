<?php
namespace Lumener\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Cookie;

class LumenerController extends Controller
{
    protected $adminer;
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
        if ($this->request->cookie('adminer_logged_out') && config('lumener.logout_redirect')) {
            return redirect(config('lumener.logout_redirect'))
            ->withCookie(Cookie::create("adminer_logged_out", null, 0));
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
            if (!isset($_GET['username']) && !isset($_POST['auth'])
                && config('lumener.auto_login')
                && !$this->request->cookie('adminer_logged_out')) {
                // Skip login screen
                $_GET['username'] = config('lumener.db.username', env("DB_USERNAME"));
                $_GET['db'] = config('lumener.db.database', env("DB_DATABASE"));
            }
            // Security Check
            $allowed_db = config('lumener.security.allowed_db');
            $protected_db = config('lumener.security.protected_db');
            $db_blocked = function ($db) use ($allowed_db, $protected_db) {
                return ($allowed_db !== null && !in_array($db, $allowed_db))
                    ||($protected_db !== null && in_array($db, $protected_db));
            };
            if ((isset($_GET['db']) && $_GET['db'] && $db_blocked($_GET['db']))
            || (isset($_POST['auth']['db']) && $_POST['auth']['db'] && $db_blocked($_POST['auth']['db']))) {
                abort(403);
            }

            // Require files
            ob_implicit_flush(0);
            ob_start();
            try {
                require($this->adminer_object);
                require($this->adminer);
            } catch (\Exception $e) {
                if ($e->getSeverity() != E_WARNING) {
                    throw $e;
                }
            }
            $content = "";
            while ($level = ob_get_clean()) {
                $content = $level . $content;
            }

            if (strpos($content, "<!DOCTYPE html>") === false) {
                die($content);
            }
            return $content;
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
        if (stripos($file, '..') !== false) {
            abort(403);
        }
        $path = LUMENER_STORAGE."/{$file}";
        $type = $this->request->get('type', mime_content_type($path));
        return response()->download($path, $file, ["Content-Type"=>$type]);
    }
}
