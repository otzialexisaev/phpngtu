<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 23:12
 */

class Designer
{
    public function display()
    {
//        $html = file_get_contents(__DIR__.'\main.html');
        $html = [];
        if ($fh = fopen(__DIR__.'\main.html', 'r')) {
            while (!feof($fh)) {
                $html[] = fgets($fh);
            }
            fclose($fh);
        }

        $this->displayMenu($html, ['asdasd', '123123']);
//        var_dump($html);
    }

    public function displayMenu($html = array(), $elements = array())
    {
        $menuitemHtml = $this->parse($html, 'mainitem');
        foreach ($elements as $element) {
            foreach ($menuitemHtml as $item) {
//            var_dump($item);
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
            if (preg_match('/<!--'.$tag.'-->/', $row)) {
                $ids[] = $id;
            }
        }
        $range = range($ids[0],$ids[1]);
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