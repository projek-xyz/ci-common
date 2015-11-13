<?php

if (! function_exists('display_override_hook')) {
    /**
     * Override default CI output display in order to make use of PHPUNIT
     *
     * @return  null
     */
    function display_override_hook()
    {
        return;
    }
}

if (! function_exists('pre_system_hook')) {
    /**
     * Make sure to load .env file before CI system boot
     *
     * @return  void
     */
    function pre_system_hook()
    {
        if (ENVIRONMENT != 'production' and !getenv('CLEARDB_DATABASE_URL')) {
            if (file_exists(APPPATH . '/.env')) {
                $dotenv = new Dotenv\Dotenv(APPPATH);
                $dotenv->load();

                get_config([
                    'base_url' => getenv('APP_BASE_URL'),
                    'log_threshold' => getenv('APP_LOG_LEVEL'),
                    'encryption_key' => getenv('APP_PRIVATE_KEY'),
                ]);
            }
        }

        if (file_exists($app_common = APPPATH.'core/App_Common.php')) {
            require_once $app_common;
        }
    }
}

if (! function_exists('list_third_parties')) {
    /**
     * Get list of all third_party libraries inside desired directory
     *
     * @param   string  $third_party_dir  Third party directory name
     *                                    Default: APPPATH.'third_party'
     * @return  array
     */
    function list_third_parties($third_party_dir = '')
    {
        $return = [];
        $third_party_dir = $third_party_dir ?: APPPATH.'third_party';

        if (is_dir($third_party_dir)) {
            foreach (glob($third_party_dir.'/*') as $third_party) {
                if (is_dir($third_party)) {
                    $return[] = realpath($third_party);
                }
            }
        }

        return $return;
    }
}

if (! function_exists('dd')) {
    /**
     * Debuging purposes
     *
     * @param   resource  $debug  Object or array
     * @return  string
     */
    function dd($debug = null)
    {
        if (! empty($debug)) {
            if (is_cli()) {
                $cli = new Projek\CI\Common\Console;
                return $cli->dump($debug);
            }

            echo '<pre>'.print_r($debug, true).'</pre>';
        }
    }
}
