<?php
// This is an extension to the LumenerController@index function

global $lumener_controller;
$lumener_controller = $this;
function adminer_object()
{
    global $lumener_controller;
    function adminer_ob_flush()
    {
        // Nothing to do
    }
    $plugins = [];
    $plugin_file = $lumener_controller->plugins_path.'/plugin.php';
    if (file_exists($plugin_file)) {
        // required to run any plugin
        include_once $plugin_file;

        // autoloader
        foreach (glob($lumener_controller->plugins_path."/*.php") as $filename) {
            include_once $filename;
        }

        // specify enabled plugins here
        $enabled = config('lumener.adminer.plugins.enabled');
        if ($enabled) {
            foreach ($enabled as $name => $arguments) {
                $reflector = new ReflectionClass($name);
                $plugins[] = $reflector->newInstanceArgs($arguments);
            }
        }
    }

    if (empty($plugins)) {
        class_alias("Adminer", "ADMBase");
    } else {
        class_alias("AdminerPlugin", "ADMBase");
    }

    class Lumener extends ADMBase
    {
        public function name()
        {
            // custom name in title and heading
            return config('lumener.name', 'Lumener');
        }

        public function permanentLogin($j=false)
        {
            // key used for permanent login
            $key = config('lumener.adminer_perma_key');
            if ($key) {
                return $key;
            }
            return parent::permanentLogin($j);
        }

        public function credentials()
        {
            // server, username and password for connecting to database
            if (config('lumener.auto_login')) {
                $host = config('lumener.db.host', env("DB_HOST"));
                $port = config('lumener.db.port', env("DB_PORT"));
                $username = config('lumener.db.username', env("DB_USERNAME"));
                $password = config('lumener.db.password', env("DB_PASSWORD"));
                return array("{$host}:{$port}", $username, $password);
            }
            return parent::credentials();
        }

        public function css()
        {
            global $lumener_controller;
            $theme = LUMENER_STORAGE.'/adminer.css';
            if (file_exists($theme)) {
                return [$lumener_controller->request->getPathInfo()
                .'/resources?file=adminer%2Ecss&type=text%2Fcss'];
            }
            return [];
        }

        public function login($login, $password)
        {
            $allowed_users = config('lumener.security.allowed_users');
            $protected_users = config('lumener.security.protected_users');
            if (
                (!empty($allowed_users) && !in_array($login, $allowed_users))
                ||!empty($protected_users) && in_array($login, $protected_users)
            ) {
                abort(403);
            }
            return config('lumener.auto_login')
            || parent::login($login, $password);
        }

        public function csp()
        {
            if (defined("LUMENER_CSP")) {
                return LUMENER_CSP;
            }
            return parent::csp();
        }
    }

    // User extensions
    $ext = config('lumener.adminer.extension_file');
    if (file_exists($ext)) {
        return include($ext);
    }

    if (empty($plugins)) {
        return new Lumener();
    }
    return new Lumener($plugins);
}
