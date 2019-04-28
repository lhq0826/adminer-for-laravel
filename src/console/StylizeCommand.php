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
 * A command to update the file for adminer.php
 *
 * @author Charles A. Peterson <artistan@gmail.com>
 */
class StylizeCommand extends Command
{
    /**
     * @var String $theme
     */
    protected $theme;

    /**
     * @var String $css_path
     */
    protected $css_path;

    /**
     * @var String $filename
     */
    protected $filename;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lumener:stylize {--file=} {--url=}';

    public function __construct()
    {
        parent::__construct();
        $resources = realpath(dirname(__FILE__).'/../resources');
        $this->theme = $resources.'/default.css';
        $this->css_path = LUMENER_STORAGE.'/adminer.css';
        $this->filename = LUMENER_STORAGE.'/adminer.php';
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
            $response = ShellHelper::get($url, ['sink' => $path]);
            if ($response && $response->getStatusCode() == '200') {
                $this->info("Lumener: Theme downloaded.");
            } else {
                $this->error('Lumener: Could not retrieve theme file. '
                .
                ($response ? "\r\n[{$response->getStatusCode()}] {$response->getReasonPhrase()} {(string)$response->getBody()}" : "Connection Failed.\r\n" . ShellHelper::$LastError));
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
        ShellHelper::copy($path, $this->css_path);
        // info("Fixing adminer.css url...");
        // ShellHelper::replace("\\\$I\\[\\]=\\\$Uc", "\$I\\[\\]='/'.\$Uc", $this->filename);
    }
}
