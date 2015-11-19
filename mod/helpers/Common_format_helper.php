<?php

if (! function_exists('format_bytes')) {
    /**
     * Get filesize in bites
     *
     * @param  string $val File size in [kb,mb,gb,tb,pb]
     * @return int
     */
    function format_bytes($val)
    {
        if (!is_string($val)) {
            return FALSE;
        }

        $val  = trim($val);
        $last = strtolower($val[strlen($val)-1]);

        switch ($last) {
            case 'p': $val *= 1024;
            case 't': $val *= 1024;
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }

        return (int) $val;
    }
}

// -----------------------------------------------------------------------------

if (!function_exists('format_size')) {
    /**
     * Get formated file size from int
     *
     * @param  int  $size File size in integer
     * @return string
     */
    function format_size($size)
    {
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
        $y     = $sizes[0];

        for ($i = 1; (($i < count($sizes)) && ($size >= 1024)); $i++) {
            $size = $size / 1024;
            $y    = $sizes[$i];
        }

        return round($size, 2).' '.$y;
    }
}

// -----------------------------------------------------------------------------

/**
 * Get formated number
 *
 * @param  int    $number  Number tobe formated
 * @param  int    $decimal Decimal character count
 * @param  string $dec_spt Decimal separator
 * @param  string $tsn_spt Thousan separator
 * @return string
 */
function format_number($number, $decimal = 2, $dec_spt = '', $tsn_spt = '') {
    if (is_numeric($number) || is_double($number)) {
        $dec_spt or $dec_spt = ',';
        $tsn_spt or $tsn_spt = '.';

        return number_format($number, $decimal, $dec_spt, $tsn_spt);
    }

    return $number;
}

// -----------------------------------------------------------------------------

/**
 * Convert numeric character to roman
 *
 * @param  int    $num Number to convert
 * @return string
 */
function format_roman($num)
{
    $num = intval($num);
    $out = '';

    // roman_numerals array
    $romans = [
        'M'  => 1000, 'CM' => 900, 'D'  => 500, 'CD' => 400,
        'C'  => 100,  'XC' => 90,  'L'  => 50,  'XL' => 40,
        'X'  => 10,   'IX' => 9,   'V'  => 5,   'IV' => 4,
        'I'  => 1
    ];

    foreach ($romans as $roman => $number) {
        // divide to get  matches
        $matches = intval($num / $number);
        // assign the roman char * $matches
        $out .= str_repeat($roman, $matches);
        // substract from the number
        $num = $num % $number;
    }

    // return the res
    return $out;
}
