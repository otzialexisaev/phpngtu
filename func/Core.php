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
    private $rusName = 'rusName.txt';

    public function __construct()
    {
        $this->root = __DIR__ . '\..\\';
        $this->cwd = getcwd();
        $this->folderCfg = (include $this->cfgPath . 'folders.php');
    }

    public function getMainFolders($main = false)
    {
        $folders = array_diff(scandir($this->root), array('..', '.'));
        foreach ($folders as $k => $folder) {
            if (!is_dir($folder) || in_array($folder, $this->folderCfg['hide'])) {
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
                    'link' => $folder
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