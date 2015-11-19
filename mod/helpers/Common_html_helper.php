<?php

if (! function_exists('parse_html_attrs')) {
    /**
     * Parsing html attributes,
     * Actually CI has built-in _attributes_to_string() function, but I wonder
     * It's belong to form_helper instead of html_helper. So I make it available
     * in form helper as well. NOTICE: this function will fallback to default
     * _attributes_to_string() if form_helper is loaded.
     *
     * @param  mixed  $attributes Attributes
     * @return string
     */
    function parse_html_attrs($attributes = null)
    {
        if (function_exists('_attributes_to_string')) {
            return _attributes_to_string($attributes);
        }

        if (empty($attributes)) {
            return '';
        }

        if (is_string($attributes)) {
            return $attributes;
        }

        if (is_object($attributes)) {
            $attributes = (array) $attributes;
        }

        if (is_array($attributes)) {
            $atts = [];
            foreach ($attributes as $key => $val) {
                $atts[] = $key.'="'.html_escape($val).'"';
            }

            return implode(' ', $atts);
        }

        return false;
    }
}

if (! function_exists('body_attr')) {
    /**
     * Get body attributes,
     * inspired by Wordpress body_class()
     *
     * @return  String
     */
    function body_attr($extra_class = null)
    {
        $attrs = ['id' => 'home', 'class' => 'page'];

        if ($segments = get_instance()->uri->segment_array()) {
            $attrs['id'] = implode('-', $segments);

            foreach ($segments as $segment => $path) {
                $prev = ($tmp = $segment - 1) > 0 ? $tmp : 0;
                if ($segment > $prev) {
                    $classes[$segment] = $classes[$prev].'-'.$segments[$segment];
                } else {
                    $classes[$segment] = $segments[$segment];
                }
            }
        }

        if ($extra_class !== null) {
            if (is_string($extra_class)) {
                $extra_class = explode(' ', $extra_class);
            }

            $classes += $extra_class;
            $attrs['class'] .= ' '.implode(' ', array_unique($classes));
        }

        return parse_html_attrs($attrs);
    }
}

if (! function_exists('lang_code')) {
    /**
     * Get language code based on language config
     *
     * @return string
     */
    function lang_code()
    {
        $language = config_item('language');
        $codes    = config_item('lang_codes');

        if (($code = array_search($language, $codes)) !== false) {
            return html_escape($code);
        }

        return 'en';
    }
}

if (! function_exists('icon')) {
    /**
     * Font Icons sortcut helper
     *
     * @param  string $name   Icon name you wanna use
     * @param  array  $extras Extra classes
     * @return string
     */
    function icon($name, array $extras = [])
    {
        $alias = config_item('fonticon_alias') ?: 'fa';
        $class = [$alias, $alias.'-'.$name];

        foreach ($extras as $extra) {
            $class[] = html_escape($alias.'-'.$extra);
        }

        return '<i class="'.implode(' ', $class).'"></i> ';
    }
}

if (! function_exists('grid_col')) {
    /**
     * Twitter Bootstrap grid column helper
     *
     * @param  mixed $column Grid columns [null|int|string|array]
     *                       default is null or (int) 12 and you'll get
     *                       col 12 grids (lg, md, sm and xs).  Int value if
     *                       you want to make it available for all grid.
     *                       String or Associative array to specify wich column
     *                       you wanna set.
     * @return string
     */
    function grid_col($column = null)
    {
        $column == null && $column = 12;

        if (is_int($column) || is_numeric($column)) {
            if ($column < 1) {
                return false;
            }
            $column = ['lg' => $column, 'md' => $column, 'sm' => $column, 'xs' => $column];
        }

        if (is_string($column)) {
            $cols = [];
            foreach (explode(',', $column) as $col) {
                list($screen, $grid) = explode('-', trim($col));
                $cols[$screen] = (int) $grid;
            }
            $column = $cols;
        }

        $result = [];
        $default = ['lg', 'md', 'sm', 'xs'];

        foreach (elements($default, $column, null) as $screen => $grid) {
            if (null !== $grid) {
                $result[] = html_escape('col-'.$screen.'-'.$grid);
            }
        }

        return implode(' ', $result);
    }
}
