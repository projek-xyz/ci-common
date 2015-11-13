<?php

if (!function_exists('valid_email')) {
    /**
     * Validate email address
     *
     * @access  public
     * @return  bool
     */
    function valid_email($address)
    {
        if (function_exists('filter_var')) {
            return (bool) filter_var($address, FILTER_VALIDATE_EMAIL);
        } else {
            return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? false : true;
        }
    }
}
