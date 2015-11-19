<?php

if (! function_exists('get_months_assoc')) {
    /**
     * Get months list in associative array
     *
     * @return  array
     */
    function get_months_assoc($format = 'F')
    {
        // Make sure $format is valid
        if (! in_array($format, ['F', 'M', 'm', 'n'])) {
            return false;
        }

        $CI =& get_instance();

        $output = [];
        $localizable = false;

        // Load calendar language if $format is localizable
        if (! in_array($format, ['F', 'M'])) {
            $CI->lang->load('calendar');
            $localizable = true;
        }

        for ($i = 1; $i <= 12; $i++) {
            $month = date($format, mktime(0, 0, 0, $i, 1));
            $output[$i] = $localizable ? $CI->lang->line('cal_'.strtolower($month)) : $month;
        }

        return $output;
    }
}

// -----------------------------------------------------------------------------

if (! function_exists('get_years_assoc')) {
    /**
     * Get year list in associative array
     *
     * @param  int   $interfal Years interval from now
     * @return array
     */
    function get_years_assoc($interfal = 10)
    {
        $output = [];

        for ($i = 0; $i <= $interfal; $i++) {
            $year = $i === 0 ? date('Y') : date('Y', mktime(0, 0, 0, $i, 1, date('Y')-$i));
            $output[$year] = $year;
        }

        return $output;
    }
}

// -----------------------------------------------------------------------------

if (! function_exists('format_date')) {
    /**
     * Get translatable date format
     *
     * @param  string $str_date Date string
     * @param  string $format   Date format
     * @return string
     */
    function format_date($str_date, $format = '')
    {
        $format || $format = '%Y-%m-%d %H:%i:%s';
        $str_date  = !empty($str_date) ? strtotime($str_date) : '';
        $formats   = preg_split('/(%\w+|\s+)/i', $format, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $localizes = ['%D', '%l', '%F', '%M'];
        $dates     = [];

        $CI =& get_instance();

        if (!in_array( 'calendar_lang.php', $CI->lang->is_loaded, TRUE)) {
            $CI->lang->load('calendar');
        }

        foreach ($formats as $fmt) {
            if (strpos($fmt, '%') === 0) {
                $date = mdate($fmt, $strdate);
                if (in_array($fmt, $localizes) ) {
                    $date = $CI->lang->line('cal_'.strtolower($date));
                }
            } else {
                $date = $fmt;
            }
            $dates[] = $date;
        }

        return implode('', $dates);
    }
}
