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
    private $docRoot = null;
    public $offsetPath = null;

    public function __construct()
    {
//        var_dump(DIRECTORY_SEPARATOR);
//        var_dump(DIR_BASE);
//        var_dump($_SERVER['DOCUMENT_ROOT']);
//        var_dump(dirname(__FILE__));
        $this->docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
        $this->root = realpath(__DIR__ . '/..');
        // todo через вычитание строк а не массивов
        $docArr = explode(DIRECTORY_SEPARATOR, $this->docRoot);
        $rootArr = explode(DIRECTORY_SEPARATOR, __DIR__);
        array_pop($rootArr);
        $this->offsetPath = implode('/', array_diff($rootArr, $docArr));
        $this->cwd = getcwd();
        $this->folderCfg = (include $this->cfgPath . 'folders.php');
        $this->httpUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . parse_url($_SERVER['HTTP_HOST'])['path'];
        $this->requestUri = parse_url($_SERVER['REQUEST_URI'])['path'];
//        $output = preg_split( '@[/|\\]@', $_SERVER['DOCUMENT_ROOT'] );
//        print_r($_SERVER);
//        var_dump($output);
    }

    public function getCurrentPageRusName()
    {
//        var_dump($this->root);
        $path = implode(DIRECTORY_SEPARATOR,
            array_diff(
                explode(DIRECTORY_SEPARATOR, realpath($this->docRoot . $this->requestUri)),
                explode(DIRECTORY_SEPARATOR, $this->docRoot),
                explode(DIRECTORY_SEPARATOR, $this->offsetPath)
            )
        ) . '/';
        $name = $this->getRusName($this->root . '/'. $path);
        return $name;
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
                    'link' => $this->httpUri . "/" . $this->offsetPath . '/' . $folder
                ];
            }
        }
        return $folders;
    }

    public function getNavFolders()
    {
        // todo сделать через строки а не массивы вдруг папки с одним названием
        $itemsRaw = array_filter(explode('/', $this->requestUri));
        $itemsRaw = array_diff($itemsRaw, explode(DIRECTORY_SEPARATOR, $this->offsetPath));
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
                'link' => $this->httpUri . "/" . $this->offsetPath . '/' . implode("/", $link),
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
        if (file_exists($path . $this->rusName))
            $rusName = file_get_contents($path . $this->rusName);
        if ($rusName === false) {
            return "Без названия";
        }
        return $rusName;
    }

    public function getCurrentFolders()
    {
        $path = implode(DIRECTORY_SEPARATOR,
            array_diff(
                explode(DIRECTORY_SEPARATOR, realpath($this->docRoot . $this->requestUri)),
                explode(DIRECTORY_SEPARATOR, $this->docRoot),
                explode(DIRECTORY_SEPARATOR, $this->offsetPath)
            )
        );
        var_dump(realpath($this->docRoot . $this->requestUri));
        var_dump(realpath($this->docRoot ));
        var_dump($path);
        $scanFolders = scandir($this->root . DIRECTORY_SEPARATOR . $path);
        $scanFolders = $this->clearFolders($scanFolders, true);
        // todo array filter убрать потому что вдруг повторяющиеся папки
        $uri = array_filter(explode('/', $this->requestUri));
        array_pop($uri);
        $uri = implode('/', $uri);
        var_dump($uri);
        $folders = [];
        foreach ($scanFolders as $folder) {
            if (!$this->checkSubDir($this->root . '/'.  $path . '/'. $folder . '/'))
                continue;

            $folderRusName = file_get_contents($this->root . '/'.  $path . '/'. $folder . '/' . $this->rusName);
            $folders[] = [
                'rusName' => $folderRusName,
                //todo двойные слэши на главной, в целом прибраться тут надо
                'link' => $this->httpUri . '/' . $this->offsetPath .'/' . $path .'/' . $folder,
            ];
        }
        if ($this->requestUri != '/')
            $folders = array_merge([['rusName' => "Назад", 'link' => $this->httpUri . '/' . $uri]], $folders);
        return $folders;
    }

    public function getContent()
    {
        $check = [];
        $path = implode(DIRECTORY_SEPARATOR,
            array_diff(
                explode(DIRECTORY_SEPARATOR, realpath($this->docRoot . $this->requestUri)),
                explode(DIRECTORY_SEPARATOR, $this->docRoot),
                explode(DIRECTORY_SEPARATOR, $this->offsetPath)
            )
        ).'/';
        var_dump(file_exists($this->root . '/'. $path . $this->contentName));
        if (file_exists($this->root . '/'. $path . $this->contentName)) {
            if ($fh = fopen($this->root . '/'. $path . $this->contentName, 'r')) {
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
        $path = implode(DIRECTORY_SEPARATOR,
                array_diff(
                    explode(DIRECTORY_SEPARATOR, realpath($this->docRoot . $this->requestUri)),
                    explode(DIRECTORY_SEPARATOR, $this->docRoot),
                    explode(DIRECTORY_SEPARATOR, $this->offsetPath)
                )
            );
        foreach ($folders as $k => $folder) {
            if (!$request && !is_dir($this->root . $path . $folder) || in_array($folder, $this->folderCfg['hide'])) {
                unset($folders[$k]);
            } elseif ($request && !is_dir($this->root . '/' . $path . '/' . $folder) || in_array($folder, $this->folderCfg['hide'])) {
                var_dump(is_dir($this->root  . '/' . $path . '/' . $folder));
                var_dump($this->root . '/' . $path . '/' . $folder);
                var_dump(is_dir($this->root . '/' . $path . '/' . $folder));
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
        var_dump($path);
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
//        if (empty($subFolders) && !file_exists($path.$this->contentName))
//            return false;
//        return true;
    }
}