<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 23:12
 */

class Designer
{
    protected $html = [];

    public function __construct()
    {
        // получаем хтмльку для парсинга, если там будет не хтмлька то наверное наступит смерть
        // парсинг точно полетит
        if ($fh = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'main.html', 'r')) {
            while (!feof($fh)) {
                $this->html[] = fgets($fh);
            }
            fclose($fh);
        }
    }

    /**
     * Получение html с лого. $host - http_host для подставления в ссылку.
     *
     * @param $host
     * @return array
     */
    public function getLogoContents($host)
    {
//        src=["|'](.*?)["|']
        $logoBlock = $this->parse($this->html, 'logo');
        foreach ($logoBlock as &$row) {
            preg_match('%src=["|\'](.*?)["|\']%', $row, $check);
            if (!empty($check) && isset($check[1])) {
                $imgName = $check[1];
                $row = str_replace($imgName, $host.'/designer/'.$imgName, $row);
                break;
            }
        }
        return $logoBlock;
    }

    /**
     * Возвращает собранный head в виде массива.
     *
     * @param $pagename
     * @return array
     */
    public function getHeadContents($pagename)
    {
        $head = $this->parse($this->html, 'head');
        $titleHtml = $this->parse($head, 'title');
        $titleHtml = $this->switchPlaceholder($titleHtml, $pagename);
        $head = $this->fillBlockWithItems($head, 'title', [$titleHtml]);
        return $head;
    }

    /**
     * Возвращает собранный хтмл для главного меню. Передает в нее массив с названиями папок.
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
            $newItem = $this->switchPlaceholder($itemHtml, $item['title']);
            foreach ($newItem as &$row) {
                if (preg_match('/href/', $row)) {
                    $row = str_replace('href', "href=\"".$item['link']."\"", $row);
                }
            }
            $newItems[] = $newItem;
        }
        $mainMenuHtml = $this->fillBlockWithItems($mainMenuHtml, 'mainmenuitem', $newItems);
        return $mainMenuHtml;
    }

    /**
     * Собирает навигационное меню.
     *
     * @param $navItems
     * @param $pagename
     * @return array
     */
    public function getNavContents($navItems, $pagename)
    {
        $navMenuHtml = $this->parse($this->html, 'nav');
        $navMenuItemHtml = $this->parse($this->html, 'navitem');
        $newItems = [];
        $pageNameItem = $this->parse($navMenuHtml, 'pagename');
        $pageNameItem = $this->switchPlaceholder($pageNameItem, $pagename);
        $navMenuHtml = $this->fillBlockWithItems($navMenuHtml, 'pagename', [$pageNameItem]);
        foreach ($navItems as $item) {
            $newItem = $this->switchPlaceholder($navMenuItemHtml, $item['title']);
            foreach ($newItem as &$row) {
                if (preg_match('/href/', $row)) {
                    $row = str_replace('href', "href=\"".$item['link']."\"", $row);
                }
            }
            $newItems[] = $newItem;
        }
        $navMenuHtml = $this->fillBlockWithItems($navMenuHtml, 'navitem', $newItems);
        return $navMenuHtml;
    }

    /**
     * Собирает центральную часть страницы с текущим меню и контентом.
     *
     * @param $menuItems
     * @param array $content
     * @return array
     */
    public function getCenterContents($menuItems, $content = [])
    {
        $centerHtml = $this->parse($this->html, 'center');
        $menuBlock = $this->parse($centerHtml, 'current');
        $menuItemBlock = $this->parse($menuBlock, 'currentitem');
        $items = [];
        foreach ($menuItems as $item) {
            $temp = $this->switchPlaceholder($menuItemBlock, $item['title']);
            foreach ($temp as &$row) {
                if (preg_match('/href/', $row)) {
                    $row = str_replace('href', "href=\"".$item['link']."\"", $row);
                }
            }
            $items[] = $temp;
        }
        $menuBlock = $this->fillBlockWithItems($menuBlock, 'currentitem', $items);
        $centerHtml = $this->fillBlockWithItems($centerHtml, 'current', [$menuBlock]);
        if (!empty($content)) {
            $contentBlock = $this->parse($this->html, 'content');
            $contentBlock = $this->switchPlaceholder($contentBlock, [$content]);
            $centerHtml = $this->fillBlockWithItems($centerHtml, 'content', [$contentBlock]);
        }
        return $centerHtml;
    }

    /**
     * Лень две строки писать каждый раз.
     *
     * @param $source
     * @param $blockName
     * @param $items
     * @return array
     */
    public function fillBlockWithItems($source, $blockName, $items)
    {
        $result = $this->replaceBlockWithPlaceholder($source, $blockName);
        $result = $this->switchPlaceholder($result, $items);
        return $result;
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
            return $this->_switchPlaceholderArray($source, $items);
        } else {
            return $this->_switchPlaceholderString($source, $items);
        }
    }

    /**
     * Подмена плэйсхолдера на строку.
     *
     * @param array $source
     * @param string $items
     * @return array
     */
    public function _switchPlaceholderString($source = [], $items = "")
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
    public function _switchPlaceholderArray($source = [], $items = [], $once = true)
    {
        $result = [];
        $check = true;
        foreach ($source as $htmlRow) {
            if (preg_match('/<!--placeholder-->/', $htmlRow)) {
                if ($once && $check) {
                    foreach ($items as $item) {
                        foreach ($item as $row) {
                            $result[] = $row;
                        }
                    }
                }
                if ($once)
                    $check = false;
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
        $result = [];
        foreach ($range as $id) {
            $result[] = $html[$id];
        }
        return $result;
    }
}