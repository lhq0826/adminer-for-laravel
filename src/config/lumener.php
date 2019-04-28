<?php
return [
  "name" => "Lumener",
  "auto_login" => false,
  "logout_cooldown" => 10,  // Time until auto login is enabled again (seconds)
  // "logout_redirect" => "admin/dashboard",
  "storage" => base_path('storage/lumener'),

  /**
   *  Route
   */
   // For Lumen, a route that has ("as" => "lumener") will be automatically
   // merged into main route
  "route" =>
  [
    // namespace is Lumener, cannot be overriden
    "path" => "lumener",
    "options" =>[
      "as" => "lumener",
      "uses" => 'LumenerController@index',
      "middleware" => []
    ]
  ],

  /**
   * Adminer
   */
  "adminer" => [
    // Check https://github.com/vrana/adminer/releases for custom releases
    // (e.g. adminer-{version}-mysql-en.php or editor-{version}.php)
    // This url format supports v4.2.5+
    "source" => "https://github.com/vrana/adminer/releases/download/v{version}/adminer-{version}.php",
    // These functions will be replaced by adminer_{name} to avoid conflicts
    "rename_list" => ['redirect','cookie','view', 'exit', 'ob_flush'],
    // version can be exact (e.g. v4.7.1) if version_type is NOT "url"
    "version" => "https://api.github.com/repos/vrana/adminer/releases/latest",
    "version_type" => "url",
    /**
     * Plugins
     */
     "plugins" => [
        // plugin.php is required to use any plugin
        // Downloaded when no file/url is supplied to the lumener:plugin command
        "plugin_php" => "https://raw.github.com/vrana/adminer/master/plugins/plugin.php",
        // You must install the required plugins using the lumener:plugin command
        "enabled" => [
          // No constructor arguments
          // "AdminerDumpXml" => [],
          // With constructor arguments
          // "AdminerDatabaseHide" => [['information_schema', 'mysql']],
        ],
     ],

     // "extension_file" => base_path("app/Logic/LumenerExtension.php")
  ],

  /**
   * Security
   */
  // Uncomment any of the following lines to limit access by db/user
  // Note that *_protected overrides *_allowed in conflicts
  "security" => [
    // "allowed_db" => ['my_db'],
    // "protected_db" => ['information_schema', 'mysql'],
    // "allowed_users" => ['admin'],
    // "protected_users" => ['root']
  ],

  /**
   * Database Access Info (For Auto Login)
   */
  // Uncomment any of the following lines to override .env values
  "db" => [
    // "host" => "www.example.com",
    // "port" => 9999,
    // "username" => "root",
    // "password" => "toor",
    // "database" => "my_database"
  ]
];
