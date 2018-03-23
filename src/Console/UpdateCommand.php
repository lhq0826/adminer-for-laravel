<?php
/**
 * Update Adminer File
 *
 * @author    Charles A. Peterson <artistan@gmail.com>
 * @copyright 2017 Charles A. Peterson
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Simple\Adminer\Console;

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
     * The console command name.
     *
     * @var string
     */
    protected $name = 'adminer:update';

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
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add \Eloquent helper to \Eloquent\Model';

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;

        $resources = realpath(dirname(__FILE__).'/../resources');
        $this->version = $resources.'/version';
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
        $latest_version = $this->get('https://api.github.com/repos/vrana/adminer/releases/latest');
        if(!$latest_version || $latest_version->getStatusCode() != '200'){
            $this->error('Adminer: latest release no found??');
            return;
        }
        $latest_version = json_decode((string) $latest_version->getBody())->tag_name;
        try {
            $last_version = $this->files->get($this->version);
        } catch (\Exception $e) {
            // do not care if file not found...
            $this->info('Adminer: no last version, welcome!');
            $last_version = false;
        }
        if($latest_version != $last_version){
            $result = $this->get('http://www.adminer.org/latest.php',['sink' => $this->filename]);
            if($result && $result->getStatusCode() == '200'){
                $this->all_sed_and_done();
                $this->files->put($this->version, $latest_version);
                $this->info('Adminer: updated!');
            } else {
                $this->error('Adminer: '.$result->getStatusCode().' :: file not downloaded?');
            }
        } else {
            $this->info('Adminer: already latest version!');
        }
    }

    /**
     * @param       $uri
     * @param array $params
     *
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     */
    private function get($uri,$params=[]){
        try {
            $client = new Client(); //GuzzleHttp\Client
            return $client->request('GET', $uri, $params);
        } catch (GuzzleException $e) {
            $this->error($e->getMessage());
        }
        return false;
    }

    /**
     * call replace on functions that are already defined
     */
    private function all_sed_and_done(){
        foreach(['redirect','cookie','view'] as $replace)
            $this->sed($replace);
    }

    /**
     * sed prefix functions that are already defined with adminer_
     *
     * @param $string
     */
    private function sed($string){
        echo shell_exec('LC_ALL=C sed -i -r \'s/([)\;]|^)'.$string.'/\1adminer_'.$string.'/g\' '.$this->filename);
    }
}
