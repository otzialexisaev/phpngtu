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
    protected $html = null;

    public function __construct()
    {
        // получаем хтмльку для парсинга, если там будет не хтмлька то наверное наступит смерть
        // парсинг точно полетит
        $html = [];
        if ($fh = fopen(__DIR__ . '\main.html', 'r')) {
            while (!feof($fh)) {
                $html[] = fgets($fh);
            }
            fclose($fh);
        }
        $this->html = $html;
    }

    public function display()
    {
        $head = $this->getHeadContents();
        return $head;
    }

    public function getMainMenuContents($mainMenuItems = array())
    {
        $mainMenuHtml = $this->parse($this->html, 'main');
        return $mainMenuHtml;
    }

    //    возвращает html между комментов head и втыкает css (исправить)
    public function getHeadContents()
    {
        $head = $this->parse($this->html, 'head');

        //        aasdasdasdadasdasdasd
        foreach ($head as $htmlRow) {
            if (preg_match('/<!--placeholder-->/', $htmlRow)) {
//                $result[] = '<style>';
//                foreach ($css as $cssRow) {
//                    $result[] = $cssRow;
//                }
//                $result[] = '</style>';
//            } else {
//                $result[] = $htmlRow;
            }
        }
//        aasdasdasdadasdasdasd


//        $head = $this->addCssToHead($head);
        return $head;
    }

    private function addCssToHead($html = array())
    {
        $css = [];
        if ($fh = fopen(__DIR__ . '\main.css', 'r')) {
            while (!feof($fh)) {
                $css[] = fgets($fh);
            }
            fclose($fh);
        }
        $result = [];
        foreach ($html as $htmlRow) {
            if (preg_match('/<!--placeholder-->/', $htmlRow)) {
                $result[] = '<style>';
                foreach ($css as $cssRow) {
                    $result[] = $cssRow;
                }
                $result[] = '</style>';
            } else {
                $result[] = $htmlRow;
            }
        }
        return $result;
    }

    public function displayMenu($html = array(), $elements = array())
    {
        $menuitemHtml = $this->parse($html, 'mainitem');
        foreach ($elements as $element) {
            foreach ($menuitemHtml as $item) {
                if (preg_match('/<!--placeholder-->/', $item)) {
                    echo $element;
                }
                echo $item;
            }
        }
    }

    /**
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

    public function parsePlaceholder($html = array())
    {
        foreach ($html as $id => $row) {
            if (preg_match('/<!--placeholder-->/', $row)) {
                return $id;
            }
        }
        return false;
    }

}