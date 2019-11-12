<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 23:12
 */
ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);

class Designer
{
    protected $html = [];

    public function __construct()
    {
        // получаем хтмльку для парсинга, если там будет не хтмлька то наверное наступит смерть
        // парсинг точно полетит
        if ($fh = fopen(__DIR__ . '\main.html', 'r')) {
            while (!feof($fh)) {
                $this->html[] = fgets($fh);
            }
            fclose($fh);
        }
    }

    /**
     * Возвращает собранный head в виде массива.
     *
     * @return array
     */
    public function getHeadContents()
    {
        $head = $this->parse($this->html, 'head');
        return $head;
    }

    /**
     * Получаем собранную хтмл для главного меню. Передаем в нее массив с названиями папок.
     *
     * @param array $mainMenuItems
     * @return array
     */
    public function getMainMenuContents($mainMenuItems = array())
    {
        $mainMenuHtml = $this->parse($this->html, 'main');
        $itemHtml = $this->parse($mainMenuHtml, "mainmenuitem");
        $newItems = [];
        foreach ($mainMenuItems as $item) {
            $newItem = $this->switchPlaceholder($itemHtml, $item['rusName']);
            foreach ($newItem as &$row) {
                if (preg_match('/href/', $row)) {
                    $row = str_replace('href', "href=\"".$item['link']."\"", $row);
                }
            }
            $newItems[] = $newItem;
        }
        $mainMenuHtml = $this->replaceBlockWithPlaceholder($mainMenuHtml, 'mainmenuitem');
        $mainMenuHtml = $this->switchPlaceholder($mainMenuHtml, $newItems);
        return $mainMenuHtml;
    }

    public function getNavContents($navItems)
    {
        $navMenuHtml = $this->parse($this->html, 'nav');
        $navMenuItemHtml = $this->parse($this->html, 'navitem');
        $newItems = [];
        foreach ($navItems as $item) {
            $newItem = $this->switchPlaceholder($navMenuItemHtml, $item['title']);
            foreach ($newItem as &$row) {
                if (preg_match('/href/', $row)) {
                    $row = str_replace('href', "href=\"".$item['link']."\"", $row);
                }
            }
            $newItems[] = $newItem;
        }
        $navMenuHtml = $this->replaceBlockWithPlaceholder($navMenuHtml, 'navitem');
        $navMenuHtml = $this->switchPlaceholder($navMenuHtml, $newItems);

//        var_dump($navMenuHtml);
        return $navMenuHtml;
    }

    /**
     * Подмена плэйсхолдера на что то переданное, строку или массив.
     * $source - эт массив с хтмлькой для выборки.
     *
     * @param array $source
     * @param string|array $items
     * @return array
     */
    public function switchPlaceholder($source, $items)
    {
        if (is_array($items)) {
            return $this->switchPlaceholderArray($source, $items);
        } else {
            return $this->switchPlaceholderString($source, $items);
        }
    }

    /**
     * Подмена плэйсхолдера на строку.
     *
     * @param array $source
     * @param string $items
     * @return array
     */
    public function switchPlaceholderString($source = [], $items = "")
    {
        foreach ($source as &$htmlRow) {
            if (preg_match('/<!--placeholder-->/', $htmlRow)) {
                $htmlRow = $items;
            }
        }
        return $source;
    }

    /**
     * Подмена плэйсхолдера на массив.
     * $items - должны приходить как массив массивов.
     *
     * @param array $source
     * @param array $items
     * @return array
     */
    public function switchPlaceholderArray($source = [], $items = [])
    {
        $result = [];
        foreach ($source as $htmlRow) {
            if (preg_match('/<!--placeholder-->/', $htmlRow)) {
                foreach ($items as $item) {
                    foreach ($item as $row) {
                        $result[] = $row;
                    }
                }
            } else {
                $result[] = $htmlRow;
            }
        }
        return $result;
    }

    /**
     * Убрать из массива с хтмл какой то блок ограниченный указанным комментарием,
     * чтобы подменить его потом на что либо с switchPlaceholder().
     *
     * @param $source
     * @param $blockName
     * @return array
     */
    public function replaceBlockWithPlaceholder($source, $blockName)
    {
        $skip = false;
        $result = [];
        foreach ($source as $row) {
            if ($skip) {
                if (preg_match('/<!--' . $blockName . '-->/', $row)) {
                    $skip = false;
                    $result[] = '<!--placeholder-->';
                }
            } elseif (preg_match('/<!--' . $blockName . '-->/', $row)) {
                $skip = true;
            } else {
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * Возвращает из массива-выборки массив содержащий хтмл внутри указанного комментария.
     *
     * @param array $html
     * @param string $tag
     * @return array
     */
    public function parse($html = array(), $tag = '')
    {
        $ids = [];
        foreach ($html as $id => $row) {
            if (preg_match('/<!--' . $tag . '-->/', $row)) {
                $ids[] = $id;
            }
        }
        $range = range($ids[0], $ids[1]);
        array_shift($range);
        array_pop($range);

//        var_dump($range);
        $result = [];
        foreach ($range as $id) {
            $result[] = $html[$id];
        }
        return $result;
    }

    /**
     * Вспомогательная функция для отображения массивов.
     *
     * @param array $array
     */
    public function showArray($array = [])
    {
        echo "<pre>";
        foreach ($array as $item) {
            if (is_array($item)) {
                foreach ($item as $row) {
                    print_r(htmlentities($row));
                }
            } else {
                print_r(htmlentities($item));
            }
        }
        echo "</pre>";
    }

}