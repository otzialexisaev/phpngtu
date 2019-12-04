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
     * Возвращает элементы главного меню как
     * [
     *      [
     *          'title' => *русское_название*,
     *          'link' => *ссылка*
     *      ],...
     * ]
     *
     * @return array|false|mixed
     */
    public function getMainFolders()
    {
        $folders = scandir($this->root);
        $folders = $this->clearFolders($folders);
        foreach ($folders as $k => &$folder) {
            if (!$this->checkSubDir($this->root . '/' . $folder . '/'))
                unset($folders[$k]);
            $checkFolder = scandir("$this->root/$folder");

            if (in_array($this->rusName, $checkFolder)) {
                $folderRusName = file_get_contents("$this->root/$folder/$this->rusName");
                $folder = [
                    'title' => $folderRusName,
                    'link' => $this->httpUri . $this->offsetPath . '/' . $folder
                ];
            }
        }
        return $folders;
    }

    /**
     * Возвращает элементы навигационного меню как
     * [
     *      [
     *          'title' => *русское_название*,
     *          'link' => *ссылка*
     *      ],...
     *      + элемент с ссылкой на главную
     * ]
     *
     * @return array
     */
    public function getNavFolders()
    {
        $itemsRaw = [];
        if ($this->requestUri != '/' && $this->requestUri != '/index.php') {
            $path = str_replace($this->root, '', realpath($this->docRoot . DIRECTORY_SEPARATOR . $this->requestUri));
            $itemsRaw = array_filter(explode(DIRECTORY_SEPARATOR, $path));
            foreach ($itemsRaw as $k => &$row) {
                if ($row == 'index.php') {
                    unset($itemsRaw[$k]);
                    continue;
                }
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
     * Возвращает элементы текущего меню как
     * [
     *      [
     *          'title' => *русское_название*,
     *          'link' => *ссылка*
     *      ],...
     *      + элемент с ссылкой на уровень ниже
     * ]
     *
     * @return array
     */
    public function getCurrentFolders()
    {
        $path = $this->pathFromRequest();
        $path = str_replace('//', '/', $path);
        $path = str_replace(DIRECTORY_SEPARATOR . 'index.php', '', $path);
        $scanFolders = scandir($path);
        $scanFolders = $this->clearFolders($scanFolders, true);
        $uri = array_filter(explode('/', $this->requestUri));
        if (($key = array_search('index.php', $uri)) !== false) {
            unset($uri[$key]);
        }
        array_pop($uri);
        $uri = DIRECTORY_SEPARATOR . implode('/', $uri);
        $folders = [];
        foreach ($scanFolders as $folder) {
            if (!$this->checkSubDir($path . '/' . $folder . '/'))
                continue;

            $folderRusName = file_get_contents($path . '/' . $folder . '/' . $this->rusName);
            if ($this->requestUri != '/' && $this->requestUri != '/index.php') {
                $link = $this->httpUri . str_replace('index.php','',$this->requestUri) . $folder;
            } else {
                $link = $this->httpUri . $this->offsetPath . DIRECTORY_SEPARATOR . $folder;
            }
            $folders[] = [
                'title' => $folderRusName,
                'link' => $link,
            ];
        }
        if ($this->requestUri != '/' && $this->requestUri != '/index.php') {
            if ($uri != $this->offsetPath) {
                $folders = array_merge([['title' => "Назад", 'link' => $this->httpUri . $uri]], $folders);
            } else {
                $folders = array_merge([['title' => "Назад", 'link' => $this->httpUri . '/']], $folders);
            }
        }
        return $folders;
    }

    /**
     * Возвращает контент если есть по текущей ссылке.
     *
     * @return array
     */
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

    /**
     * Возвращает русское название текущей страницы.
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
     * Возвращает корень системы + путь полученный из URL.
     *
     * @return false|string|null
     */
    public function pathFromRequest()
    {
        $path = $this->offsetFromRequest();
        if ($path == '') {
            return str_replace(DIRECTORY_SEPARATOR . 'index.php','/',$this->root);
        } else {
            return str_replace(DIRECTORY_SEPARATOR . 'index.php','/',$this->root . DIRECTORY_SEPARATOR . $path);
        }
    }

    /**
     * Возвращает путь из URL, при необходимости обрезает оффсет.
     * Этот путь нужен для сопоставления пути от корня системы и корня сервера.
     *
     * @return mixed|string
     */
    public function offsetFromRequest()
    {
        if ($this->requestUri == '/' || $this->requestUri == '/index.php') {
            return '';
        }
        $path = str_replace($this->root, '', realpath($this->docRoot . $this->requestUri));
//        var_dump($path);
        return $path;
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
     * Проверяет надо ли оторажать папку в текущем меню.
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