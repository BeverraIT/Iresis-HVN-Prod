<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('menu_to_tree')) {

    function menu_to_tree($list_menu_tree, $parent)
    {
        foreach ($list_menu_tree as $child):
            if ($child['parentid'] == $parent['id']) {
                $child = menu_to_tree($list_menu_tree, $child);
                $parent['child'][] = $child;
            }
        endforeach;

        return $parent;
    }
}

if (!function_exists('tree_to_html')) {

    function tree_to_html($menu)
    {
        $data_jstree['icon'] = $menu['icon'];
        
		if (!empty($menu['child'])) {
			$data_jstree['opened'] = true;
        }
        
        if (!empty($menu['selected']) && !empty($menu['uri'])) {
            $data_jstree['selected'] = true;
        }

        $html = '<li id="'.$menu['id'].'" data-jstree=\''.json_encode($data_jstree).'\'>'.$menu['name'];

        if (!empty($menu['child'])) {
            
            $html = $html.'<ul>';

            foreach ($menu['child'] as $child):
                $html = $html.tree_to_html($child);
            endforeach;

            $html = $html.'</ul>';

        }

        $html = $html.'</li>';

        return $html;
    }
}

if (!function_exists('tree_to_html_menu')) {

    function tree_to_html_menu($list_menu)
    {
        $html = '';
        foreach ($list_menu as $menu):
            $uri = empty($menu['uri']) ? '#' : $menu['uri'];

            if (empty($menu['child'])) {
                $html .= '<li><a href="'.$uri.'" class="link"><span class="'.$menu['icon'].'"></span> '.$menu['name'].'</a></li>';
            } else {
                $html .= '<li class="xn-openable"><a href="'.$uri.'"><span class="'.$menu['icon'].'"></span> <span class="xn-text">'.$menu['name'].'</span></a><ul>';
                $html .= tree_to_html_menu($menu['child']);
                $html .= '</ul></li>';
            }
        endforeach;

        return $html;
    }
}

if (!function_exists('get_parent_name_keys')) {

    function get_parent_name_keys($arr = [], $needle = "")
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($arr));
        $nameKeys = [ucfirst($needle)];
        foreach ($iterator as $key => $value) {
            if ($value === $needle) {
                $depth = $iterator->getDepth();
                while ($depth--){
                    if ($iterator->getSubIterator($depth)->offsetExists('name')) {
                        $nameKeys[] = $iterator->getSubIterator($depth)->offsetGet('name');
                    }
                }
            }
        }
        return array_reverse($nameKeys);
    }
}