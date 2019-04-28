<?php

namespace Lumener\Helpers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class ShellHelper
{
    public static $LastError;

    /**
     * @param       $uri
     * @param array $params
     *
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     */
    public static function get($uri, $params=[])
    {
        try {
            $client = new Client(); //GuzzleHttp\Client
            return $client->request('GET', $uri, $params);
        } catch (GuzzleException $e) {
            self::$LastError = $e->getMessage();
            return false;
        }
    }

    /**
     * copy files
     *
     * @param $string
     */
    public static function copy($source, $dest)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo shell_exec("copy \"{$source}\" \"{$dest}\"");
        } else {
            echo shell_exec("cp \"{$source}\" \"{$dest}\"");
        }
    }

    /**
     * Replace portions of the file
     * @param  string $pattern
     * @param  string $replacement
     * @param  string $file
     * @return void
     */
    public static function replace($pattern, $replacement, $file)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // TODO: Test this on windows
            echo shell_exec("cat \"{$file}\" | %{_ -replace \"$pattern\",\"{$replacement}\"} > \"{$file}\"");
        } else {
            $rule = '%([/])%';
            $pattern = preg_replace($rule, '\\\\$0', $pattern);
            $replacement = preg_replace($rule, '\\\\$0', $replacement);
            $arg = escapeshellarg("s/{$pattern}/{$replacement}/g");
            echo shell_exec("LC_ALL=C sed -i -r \"{$arg}\" \"{$file}\"");
        }
    }

    /**
     * Execute the sed command
     *
     * @param $string
     */
    public static function rename($string, $filename)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // TODO: Test this on windows
            echo shell_exec("cat \"{$filename}\" | %{_ -replace \"([\\[\\)\\(\\\;\\{\\}]|^)'{$string}\",\"$1adminer_{$string}\"} > \"{$filename}\"");
        } else {
            echo shell_exec('LC_ALL=C sed -i -r \'s/([)(\;{}]|^)'.$string.'/\1adminer_'.$string.'/g\' '."\"{$filename}\"");
        }
    }
}
