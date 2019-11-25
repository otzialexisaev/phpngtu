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
    private $folderCfg = null;
    public $requestUri = null;
    public $httpUri = null;
    private $rusName = 'rusName.txt';
    private $contentName = 'content.html';
    private $docRoot = null;
    public $offsetPath = null;

    public function __construct()
    {
        $this->docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
        $this->root = realpath(__DIR__ . '/..');
        $this->offsetPath = str_replace($this->docRoot, '', $this->root);
        $this->cwd = getcwd();
        $this->folderCfg = (include $this->cfgPath . 'folders.php');
        $this->httpUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . parse_url($_SERVER['HTTP_HOST'])['path'];
        $this->requestUri = parse_url($_SERVER['REQUEST_URI'])['path'];
    }

    /**
     * Получить русское название текущей страницы.
     *
     * @return bool|false|string
     */
    public function getCurrentPageRusName()
    {
        $path = $this->pathFromRequest();
        $name = $this->getRusName($path);
        return $name;
    }

    /**
     * Получаем корень системы + путь полученный из URL.
     *
     * @return false|string|null
     */
    public function pathFromRequest()
    {
        $path = $this->offsetFromRequest();
        if ($path == '') {
            return $this->root;
        } else {
            return $this->root . DIRECTORY_SEPARATOR . $path;
        }
    }

    /**
     * Получаем путь из URL, при необходимости обрезаем оффсет.
     * Этот путь нужен для сопоставления пути от корня системы и корня сервера.
     *
     * @return mixed|string
     */
    public function offsetFromRequest()
    {
        if ($this->requestUri == '/') {
            return '';
        }
        $path = str_replace($this->root, '', realpath($this->docRoot . $this->requestUri));
//        var_dump($path);
        return $path;
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
                    'link' => $this->httpUri . $this->offsetPath . '/' . $folder
                ];
            }
        }
        return $folders;
    }

    function str_replace_first($from, $to, $content)
    {
        $from = '/' . preg_quote($from, '/') . '/';

        return preg_replace($from, $to, $content, 1);
    }

    public function getNavFolders()
    {
        // todo сделать через строки а не массивы вдруг папки с одним названием
        $itemsRaw = [];
        if ($this->requestUri != '/') {
            $path = str_replace($this->root, '', realpath($this->docRoot . DIRECTORY_SEPARATOR . $this->requestUri));
            $itemsRaw = array_filter(explode(DIRECTORY_SEPARATOR, $path));
            foreach ($itemsRaw as &$row) {
                $row = str_replace(DIRECTORY_SEPARATOR, '', $row);
            }
        }
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
                'title' => $this->getRusName($this->root . '/' . implode('/', $link) . '/'),
                'link' => $this->httpUri . $this->offsetPath . '/' . implode("/", $link),
            ];
        }
        return $items;
    }

    /**
     * Возвращает русское название из переданного пути.
     *
     * @param $path
     * @return bool|false|string
     */
    public function getRusName($path)
    {
        if (!is_dir($path))
            return "Без названия";
        $rusName = false;
        if (file_exists($path . DIRECTORY_SEPARATOR . $this->rusName))
            $rusName = file_get_contents($path . DIRECTORY_SEPARATOR . $this->rusName);
        if ($rusName === false) {
            return "Без названия";
        }
        return $rusName;
    }

    public function getCurrentFolders()
    {
        $path = $this->pathFromRequest();
        $scanFolders = scandir($path);
        $scanFolders = $this->clearFolders($scanFolders, true);
        $uri = array_filter(explode('/', $this->requestUri));
        array_pop($uri);
        $uri = DIRECTORY_SEPARATOR . implode('/', $uri);
        $folders = [];
        foreach ($scanFolders as $folder) {
            if (!$this->checkSubDir($path . '/' . $folder . '/'))
                continue;

            $folderRusName = file_get_contents($path . '/' . $folder . '/' . $this->rusName);
            if ($this->requestUri != '/') {
                $link = $this->httpUri . $this->requestUri . $folder;
            } else {
                $link = $this->httpUri . $this->offsetPath . DIRECTORY_SEPARATOR . $folder;
            }
            $folders[] = [
                'rusName' => $folderRusName,
                'link' => $link,
            ];
        }
        if ($this->requestUri != '/') {
            if ($uri != $this->offsetPath) {
                $folders = array_merge([['rusName' => "Назад", 'link' => $this->httpUri . $uri]], $folders);
            } else {
                $folders = array_merge([['rusName' => "Назад", 'link' => $this->httpUri . '/']], $folders);
            }
        }
        return $folders;
    }

    public function getContent()
    {
        $check = [];
        $path = $this->pathFromRequest();
        if (file_exists($path . DIRECTORY_SEPARATOR . $this->contentName)) {
            if ($fh = fopen($path . DIRECTORY_SEPARATOR . $this->contentName, 'r')) {
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
        $path = $this->pathFromRequest();
        foreach ($folders as $k => $folder) {
            if (!$request && !is_dir($this->root . DIRECTORY_SEPARATOR . $folder) || in_array($folder, $this->folderCfg['hide'])) {
                unset($folders[$k]);
            } elseif ($request && !is_dir($path . DIRECTORY_SEPARATOR . $folder) || in_array($folder, $this->folderCfg['hide'])) {
                unset($folders[$k]);
            }
        }
        return $folders;
    }

    /**
     * Чекает надо ли оторажать папку в меню слева.
     * Если переданная директория или ее поддиректории не содержат
     * контента - ответ false.
     *
     * @param $path
     * @return bool
     */
    public function checkSubDir($path)
    {
        $toDelete = ['..', '.',
            $this->rusName, 'index.php'];
        $items = scandir($path);
        $subFolders = array_diff($items, $toDelete);
        if (in_array($this->contentName, $items)) {
            return true;
        } else {
            foreach ($subFolders as $folder) {
                if ($this->checkSubDir($path . $folder)) {
                    return true;
                }
            }
        }
        return false;
    }
}