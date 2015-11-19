<?php

if (!function_exists('lang')) {
    /**
     * Validate email address
     *
     * @access public
     * @return bool
     */
    function lang($line)
    {
        $line = get_instance()->lang->line($line);
        $args = func_get_args();

        if (count($args) > 1) {
            $args = array_slice($args, 1);

            return vsprintf($line, $args);
        }

        return $line;
    }
}
