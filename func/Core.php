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
    private $contentName = 'content.html';

    public function __construct()
    {
        $this->root = __DIR__ . '/..';
        $this->cwd = getcwd();
        $this->folderCfg = (include $this->cfgPath . 'folders.php');
        $this->httpUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . parse_url($_SERVER['HTTP_HOST'])['path'];
        $this->requestUri = parse_url($_SERVER['REQUEST_URI'])['path'];
    }

    public function getMainFolders()
    {
        $folders = scandir($this->root);
        $folders = $this->clearFolders($folders);
        foreach ($folders as $k => &$folder) {
            $checkFolder = scandir("$this->root/$folder");
            if (in_array($this->rusName, $checkFolder)) {
                $folderRusName = file_get_contents("$this->root/$folder/$this->rusName");
                $folder = [
                    'rusName' => $folderRusName,
                    'link' => $this->httpUri . "/" . $folder
                ];
            }
        }
        return $folders;
    }

    public function getNavFolders()
    {
        $itemsRaw = array_filter(explode('/', $this->requestUri));
        $items = [
            [
                'title' => 'Главная',
                'link' => $this->httpUri,
            ]
        ];
        $link = [];
        foreach ($itemsRaw as $item) {
            $link[] = $item;
            $items[] = [
                'title' => $item,
                'link' => $this->httpUri . "/" . implode("/", $link),
            ];
        }
        return $items;
    }

    public function getCurrentFolders()
    {
        $scanFolders = scandir($this->root . $this->requestUri);
        $scanFolders = $this->clearFolders($scanFolders, true);
        $uri = array_filter(explode('/', $this->requestUri));
        array_pop($uri);
        $uri = implode('/', $uri);
        $folders = [];
        foreach ($scanFolders as $folder) {
            if (!$this->checkSubDir($this->root . $this->requestUri . $folder . '/')) {
                continue;
            }
            $folderRusName = file_get_contents($this->root . $this->requestUri . $folder . '/' . $this->rusName);
            $folders[] = [
                'rusName' => $folderRusName,
                'link' => $this->httpUri . $this->requestUri . $folder,
            ];
        }
        if ($this->requestUri != '/')
            $folders = array_merge([['rusName' => "Назад", 'link' => $this->httpUri . '/' . $uri]], $folders);
        return $folders;
    }

    public function getContent()
    {
        $check = [];
        if (file_exists($this->root . $this->requestUri . $this->contentName)) {
            if ($fh = fopen($this->root . $this->requestUri . $this->contentName, 'r')) {
                while (!feof($fh)) {
                    $check[] = fgets($fh);
                }
                fclose($fh);
            }
        }
        return $check;
    }

    public function clearFolders($folders, $request = false)
    {
        foreach ($folders as $k => $folder) {
            if (!$request && !is_dir($this->root . '/' . $folder) || in_array($folder, $this->folderCfg['hide'])) {
                unset($folders[$k]);
            } elseif ($request && !is_dir($this->root . $this->requestUri . $folder) || in_array($folder, $this->folderCfg['hide'])) {
                unset($folders[$k]);
            }
        }
        return $folders;
    }

    /**
     * Чекает надо ли оторажать папку в меню слева.
     * Если в ней нет подпапок либо не содержит контента - ответ false.
     *
     * @param $path
     * @return bool
     */
    public function checkSubDir($path)
    {
        $toDelete = ['..', '.', $this->contentName, $this->rusName, 'index.php'];
        $items = scandir($path);
        $subFolders = array_diff($items, $toDelete);
        if (empty($subFolders) && !file_exists($path.$this->contentName))
            return false;
        return true;
    }
}