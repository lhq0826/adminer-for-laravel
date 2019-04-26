<?php
return [
  /* For Lumen, a route that has ("as" => "lumener") will be automatically
    merged into main route while keeping its original path and middleware.
    For Laravel, this MUST be identical to any route with custom middleware. */
  "route" => "lumener",
  "redundant_vars" => ['redirect','cookie','view'],
  // adminer_version can be exact (e.g. v4.7.1) if version_source is NOT "url"
  "adminer_version" => "https://api.github.com/repos/vrana/adminer/releases/latest",
  "version_source" => "url",
  "adminer_source" => "http://www.adminer.org/latest.php",
];
