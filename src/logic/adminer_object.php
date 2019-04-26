<?php

function adminer_object()
{
    class Lumener extends Adminer
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
            if (config('lumener.adminer_autologin')) {
                $host = config('lumener.db_host', env("DB_HOST"));
                $port = config('lumener.db_port', env("DB_PORT"));
                $username = config('lumener.db_username', env("DB_USERNAME"));
                $password = config('lumener.db_password', env("DB_PASSWORD"));
                return array("{$host}:{$port}", $username, $password);
            }
            return parent::credentials();
        }

        public function database()
        {
            // database name, will be escaped by Adminer
            if (config('lumener.adminer_autologin')) {
                return config('lumener.db_database', env("DB_DATABASE"));
            }
            return parent::database();
        }

        public function css()
        {
            $I=array();
            $Uc="adminer.css";
            if (file_exists($Uc)) {
                $I[]='/'.$Uc;
            }
            return$I;
        }
    }

    if (file_exists($plugin_file)) {
        // required to run any plugin
        include_once $plugin_file;

        // autoloader
        foreach (glob($this->plugins."/*.php") as $filename) {
            include_once $this->plugins."/$filename";
        }

        // specify enabled plugins here
        $enabled = config('lumener.adminer_plugins');
        if ($enabled) {
            $plugins = [];
            foreach ($enabled as $name => $arguments) {
                $reflector = new ReflectionClass($name);
                $plugins[] = $reflector->newInstanceArgs($arguments);
            }
            return new Lumener($plugins);
        }
    }
    return new Lumener();
}
