<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 22:48
 */

class Core
{
    public $root = null;
    private $cwd = null;
    private $cfgPath = __DIR__ . '/configs/';
    public $designerPath = __DIR__ . '/../designer';
    private $folderCfg = null;
    public $requestUri = null;
    public $httpUri = null;
    private $rusName = 'rusName.txt';
    private $contentName = 'content.html';

    public function __construct()
    {
//        var_dump(DIRECTORY_SEPARATOR);
//        var_dump(DIR_BASE);
//        var_dump($_SERVER['DOCUMENT_ROOT']);
//        var_dump(dirname(__FILE__));
//        var_dump(__DIR__.'/..');
        $this->root = __DIR__ . '/..';
        $this->cwd = getcwd();
        $this->folderCfg = (include $this->cfgPath . 'folders.php');
        $this->httpUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . parse_url($_SERVER['HTTP_HOST'])['path'];
        $this->requestUri = parse_url($_SERVER['REQUEST_URI'])['path'];
//        $output = preg_split( '@[/|\\]@', $_SERVER['DOCUMENT_ROOT'] );
//        print_r($_SERVER);
//        var_dump($output);
    }

    public function getImgPath()
    {
//        $output = preg_split( '@[/|\\]@', $_SERVER['DOCUMENT_ROOT'] );

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
                'link' => $this->httpUri . "/",
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
        $folders = scandir($this->root . $this->requestUri);
        $folders = $this->clearFolders($folders, true);
        $uri = array_filter(explode('/', $this->requestUri));
        array_pop($uri);
        $uri = implode('/', $uri);
        foreach ($folders as &$folder) {
            $folderRusName = file_get_contents($this->root . $this->requestUri . $folder . '/' . $this->rusName);
            $folder = [
                'rusName' => $folderRusName,
                'link' => $this->httpUri . $this->requestUri . $folder,
            ];
        }
        if ($this->requestUri != '/')
            $folders = array_merge([['rusName' => "Назад", 'link' => $this->httpUri.'/'.$uri]], $folders);
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
}