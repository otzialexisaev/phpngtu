<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 22:48
 */

class Core
{
    private $root = null;
    private $cwd = null;
    private $cfgPath = __DIR__ . '/configs/';
    private $folderCfg = null;
    private $requestUri = null;
    private $httpUri = null;
    private $rusName = 'rusName.txt';

    public function __construct()
    {
        $this->root = __DIR__ . '\..\\';
        $this->cwd = getcwd();
        $this->folderCfg = (include $this->cfgPath . 'folders.php');
        $this->requestUri = parse_url($_SERVER['REQUEST_URI'])['path'];
        $this->httpUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . parse_url($_SERVER['HTTP_HOST'])['path'];
//        var_dump($parse);
//        var_dump($this->requestUri);
//        var_dump($this->httpUri);
    }

    public function getMainFolders($main = false)
    {
        $folders = array_diff(scandir($this->root), array('..', '.'));
        foreach ($folders as $k => $folder) {
            if (!is_dir($this->root."/".$folder) || in_array($folder, $this->folderCfg['hide'])) {
                unset($folders[$k]);
            }
        }
        foreach ($folders as $k => &$folder) {
            $checkFolder = scandir("$this->root/$folder");
            if (in_array($this->rusName, $checkFolder)) {
                $folderRusName = file_get_contents("$this->root/$folder/$this->rusName");
//                var_dump($folderRusName);
                $folder = [
                    'rusName' => $folderRusName,
                    'link' => $this->httpUri."/".$folder
                ];
            }
//            var_dump($checkFolder);
        }
//        var_dump($folders);
        if (!$main) {
            $folders[] = [
                'rusName' => 'Назад',
                'link' => '../'
            ];
        }
        return $folders;
    }
}