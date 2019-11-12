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
        $this->httpUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . parse_url($_SERVER['HTTP_HOST'])['path'];
        $this->requestUri = parse_url($_SERVER['REQUEST_URI'])['path'];
    }

    public function getMainFolders()
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
                $folder = [
                    'rusName' => $folderRusName,
                    'link' => $this->httpUri."/".$folder
                ];
            }
        }
        return $folders;
    }

    public function getNavFolders()
    {
        $itemsRaw = array_filter(explode('/',$this->requestUri));
        $items = [
            [
                'title' => 'Главная',
                'link' => $this->httpUri."/",
            ]
        ];
        $link = [];
        foreach ($itemsRaw as $item) {
            $link[] = $item;
            $items[] = [
                'title' => $item,
                'link' => $this->httpUri."/".implode("/", $link),
            ];
        }
        return $items;
    }

    public function getCurrentFolders()
    {

    }
}