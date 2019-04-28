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
use Lumener\Helpers\ShellHelper;

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
    public function __construct()
    {
        parent::__construct();
        $this->plugin_path = LUMENER_STORAGE.'/plugins';
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
            $fname = basename($path);
            $this->info("Copying plugin file...");
        } else {
            if ($url) {
                $this->info("Downloading plugin...");
            } else {
                $url = config(
                    'lumener.adminer.plugins.plugin_php',
                    'https://raw.github.com/vrana/adminer/master/plugins/plugin.php'
                );
            }
            $fname = basename($url);
            $path = tempnam(sys_get_temp_dir(), $fname);
            $response = ShellHelper::get($url, ['sink' => $path]);
            if ($response && $response->getStatusCode() == '200') {
                $this->info("Lumener: Plugin downloaded.");
            } else {
                $this->error('Lumener: Could not retrieve plugin file. '
                .
                ($response ? "\r\n[{$response->getStatusCode()}] {$response->getReasonPhrase()} {(string)$response->getBody()}" : "Connection Failed.\r\n" . ShellHelper::$LastError));
                return;
            }
        }
        if (!is_dir($this->plugin_path)) {
            mkdir($this->plugin_path);
        }
        ShellHelper::copy($path, $this->plugin_path."/{$fname}");
    }
}
