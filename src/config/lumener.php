<?php
return [
  /* For Lumen, a route that has ("as" => "lumener") will be automatically
    merged into main route while keeping its original path and middleware.
    For Laravel, this MUST be identical to any route with custom middleware. */
  "name" => "Lumener",
  "route" => "lumener",
  "redundant_vars" => ['redirect','cookie','view'],
  // adminer_version can be exact (e.g. v4.7.1) if version_source is NOT "url"
  "adminer_version" => "https://api.github.com/repos/vrana/adminer/releases/latest",
  "version_source" => "url",

  // Check https://github.com/vrana/adminer/releases for custom releases
  //  (e.g. adminer-{version}-mysql-en.php or editor-{version}.php)
  //  This format supports v4.2.5+
  "adminer_source" => "https://github.com/vrana/adminer/releases/download/v{version}/adminer-{version}.php",


  /**
   * Plugins
   */

  // plugin.php is required to use any plugin
  // Automatically used when no file/url is supplied for the plugin command
  "plugin_source" => "https://raw.github.com/vrana/adminer/master/plugins/plugin.php",

  // Uncomment this section to enable plugins
  // "adminer_plugins" => [
  //   // No constructor arguments
  //   // "AdminerDumpXml" => [],
  //   // With constructor arguments
  //   // "AdminerFileUpload" => ["data/"],
  // ],


  /**
   * Autologin Settings
   */
  "adminer_autologin" => false,

  // Uncomment this section to override .env values
  // "db_host" => "127.0.0.1",
  // "db_port" => 3306,
  // "db_username" => "root",
  // "db_password" => "toor",
  // "db_database" => "mydatabase"
];
