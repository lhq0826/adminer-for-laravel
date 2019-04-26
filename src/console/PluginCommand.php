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
 * A command to require/update a plugin
 *
 * @author Charles A. Peterson <artistan@gmail.com>
 */
class PluginCommand extends Command
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
     * @var String $plugin_path
     */
    protected $plugin_path;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lumener:plugin {--file=} {--url=}';

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $resources = realpath(dirname(__FILE__).'/../resources');
        $this->files = $files;
        $this->theme = realpath(dirname(__FILE__).'/../public/adminer.css');
        $this->plugin_path = $resources.'/plugins';
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
        $path = $this->option('file');
        if ($path) {
            $this->info("Copying plugin file...");
        } else {
            if ($url) {
                $this->info("Downloading plugin...");
            } else {
                $url = config(
                    'lumener.plugin_source',
                    'https://raw.github.com/vrana/adminer/master/plugins/plugin.php'
                );
            }
            $path = tempnam(sys_get_temp_dir(), 'adminer.css');
            $response = $this->get($url, ['sink' => $path]);
            if ($response && $response->getStatusCode() == '200') {
                $this->copy($path, $this->plugin_path);
                $this->info("Lumener: Plugin downloaded.");
            } else {
                $this->error('Lumener: Could not retrieve plugin file. '
                .
                ($response ? "\r\n[{$response->getStatusCode()}] {$response->getReasonPhrase()} {(string)$response->getBody()}" : "Connection Failed."));
                return;
            }
        }
        $this->copy($path, $this->plugin_path);
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
}
