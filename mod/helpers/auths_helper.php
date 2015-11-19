<?php

if (! function_exists('user_can')) {
    /**
     * Is current user allowed to $permission
     *
     * @param  string $permission
     * @return bool
     */
    function user_can($permission)
    {
        return get_instance()->auths->user_can($permission);
    }
}

if (! function_exists('current_user')) {
    /**
     * Get current logged in user data
     *
     * @param  string $session_key
     * @return mixed
     */
    function current_user($session_key)
    {
        return get_instance()->auths->get_current($session_key);
    }
}
