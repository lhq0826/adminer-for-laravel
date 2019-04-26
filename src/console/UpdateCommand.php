<?php
/**
 * Update adminer
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
class UpdateCommand extends Command
{
    /**
     * @var Filesystem $files
     */
    protected $files;

    /**
     * @var String $version
     */
    protected $version;

    /**
     * @var String $filename
     */
    protected $filename;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lumener:update {--force}';

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;

        $resources = realpath(dirname(__FILE__).'/../resources');
        // $this->version = $resources.'/version';
        $this->filename = $resources.'/adminer.php';
        $this->tmpfile = $resources.'/tmp.php';
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $force = $this->option('force', false);
        if ($force) {
            $this->error("Force mode active.");
        }
        $current_version = false;
        try {
            if (file_exists($this->filename)) {
                $fn = fopen($this->filename, "r");
                for ($i=0; !$current_version && $i < 20 && !feof($fn); $i++) {
                    $line = fgets($fn, 30);
                    preg_match_all("/@version ((\d([\.-]|$))+)/", $line, $m);
                    if (!empty($m[1])) {
                        $current_version = $m[1][0];
                    }
                }
            }
        } catch (\Throwable $e) {
        }
        if ($current_version) {
            $this->info("Lumener: Current ".$current_version);
        } else {
            $this->error("Lumener: Adminer not found.");
        }
        $vsource = config(
            'lumener.adminer_version',
            'https://api.github.com/repos/vrana/adminer/releases/latest'
        );
        if (config('lumener.version_source', 'url') == 'url') {
            $this->info("Lumener: Checking latest adminer version...");
            $response = $this->get($vsource);
            if (!$response || $response->getStatusCode() != '200') {
                $this->error('Lumener: Could not retrieve version information from url. '
            .
            ($response ? "\r\n[{$response->getStatusCode()}] {$response->getReasonPhrase()} {(string)$response->getBody()}" : "Connection Failed."));
                return;
            }
            $latest_version = ltrim(json_decode((string) $response->getBody())->tag_name, 'v');
            $this->info("Lumener: Latest Adminer Version " . $latest_version);
        } else {
            $latest_version = $vsource;
            $this->info("Lumener: Required Adminer Version " . $latest_version);
        }
        if ($force || !file_exists($this->filename) || $latest_version != $current_version) {
            $this->info("Lumener: Downloading...");
            $url = config(
                'lumener.adminer_source',
                'https://github.com/vrana/adminer/releases/download/v{version}/adminer-{version}.php'
            );
            $url = str_replace("{version}", ltrim($latest_version, 'v'));
            $response = $this->get($url, ['sink' => $this->filename]);
            if ($response && $response->getStatusCode() == '200') {
                info("Renaming redundant variables...");
                $this->renameRedundant();
                // $this->files->put($this->version, $latest_version);
                $this->info("Lumener: Updated!");
            } else {
                $this->error('Lumener: Could not download adminer.'
                .
                ($response ? "\r\n[{$response->getStatusCode()}] {$response->getReasonPhrase()} {(string)$response->getBody()}" : "Connection Failed."));
                return;
            }
        } else {
            $this->info('Lumener: Up to date.');
        }
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
     * Rename functions already defined in Laravel/Lumen public helper
     */
    private function renameRedundant()
    {
        foreach (config('lumener.redundant_vars', ['redirect','cookie','view']) as $var) {
            $this->rename($var);
        }
    }

    /**
     * Execute the sed command
     *
     * @param $string
     */
    private function rename($string)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // TODO: Test this on windows
            echo shell_exec("cat \"{$this->filename}\" | %{_ -replace \"([\\[\\)\\(\\\;\\{\\}]|^)'{$string}\",\"$1adminer_{$string}\"} > \"{$this->filename}\"");
        } else {
            echo shell_exec('LC_ALL=C sed -i -r \'s/([)(\;{}]|^)'.$string.'/\1adminer_'.$string.'/g\' '."\"{$this->filename}\"");
        }
    }
}
