<?php
/**
 * Copy theme files
 *
 * @author    Hesham A. Meneisi heshammeneisi@gmail.com
 * @copyright 2019 Hesham Meneisi
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Lumener\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * A command to update the file for adminer.php
 *
 * @author Charles A. Peterson <artistan@gmail.com>
 */
class StylizeCommand extends Command
{
    /**
     * @var Filesystem $files
     */
    protected $files;

    /**
     * @var String $theme
     */
    protected $theme;

    /**
     * @var String $css_path
     */
    protected $css_path;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lumener:stylize {--file=} {--url=}';

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $resources = realpath(dirname(__FILE__).'/../resources');
        $this->files = $files;
        $this->theme = realpath(dirname(__FILE__).'/../public/adminer.css');
        $this->css_path = base_path('public/adminer.css');
        $this->filename = $resources.'/adminer.php';
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $url = $this->option('url');
        if ($url) {
            $this->info("Downloading theme...");
            $path = tempnam(sys_get_temp_dir(), 'adminer.css');
            $response = $this->get($url, ['sink' => $path]);
            if ($response && $response->getStatusCode() == '200') {
                $this->copy($path, $this->css_path);
                $this->info("Lumener: Theme downloaded.");
            } else {
                $this->error('Lumener: Could not retrieve version information. '
                .
                ($response ? "\r\n[{$response->getStatusCode()}] {$response->getReasonPhrase()} {(string)$response->getBody()}" : "Connection Failed."));
                return;
            }
        } else {
            $path = $this->option('file');
            if ($path) {
                $this->info("Applying theme file...");
            } else {
                $this->info("Applying default theme...");
                $path = $this->theme;
            }
        }
        $this->copy($path, $this->css_path);
        info("Fixing adminer.css url...");
        $this->replace("\\\$I\\[\\]=\\\$Uc", "\$I\\[\\]='/'.\$Uc", $this->filename);
    }

    /**
     * @param       $uri
     * @param array $params
     *
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     */
    private function get($uri, $params=[])
    {
        try {
            $client = new Client(); //GuzzleHttp\Client
            return $client->request('GET', $uri, $params);
        } catch (GuzzleException $e) {
            $this->error($e->getMessage());
        }
        return false;
    }

    /**
     * copy files
     *
     * @param $string
     */
    private function copy($source, $dest)
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
    private function replace($pattern, $replacement, $file)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // TODO: Test this on windows
            echo shell_exec("cat \"{$file}\" | %{_ -replace \"$pattern\",\"{$replacement}\"} > \"{$file}\"");
        } else {
            $rule = '%([/])%';
            $pattern = preg_replace($rule, '\\\\$0', $pattern);
            $replacement = preg_replace($rule, '\\\\$0', $replacement);
            $arg = escapeshellarg("s/{$pattern}/{$replacement}/g");
            echo shell_exec("LC_ALL=C sed -i -r {$arg} \"{$file}\"");
        }
    }
}
