<?php
namespace Projek\CI\Common\Console;

use Projek\CI\Console\Cli;
use Projek\CI\Console\Commands;
use Projek\CI\Console\Arguments\Manager;

class Install extends Commands
{
    protected $name = 'install';
    protected $description = 'lang:console_install_desc';

    /**
     * {inheridoc}
     */
    public function register(Manager $arguments)
    {
        $arguments->add([
            'help' => [
                'prefix' => 'h',
                'longPrefix' => 'help',
                'description' => Cli::lang('console_display_help'),
                'noValue' => true
            ],
            'noninteractive' => [
                'prefix' => 'n',
                'longPrefix' => 'noninteractive',
                'description' => 'Run non interactively',
                'noValue' => true
            ]
        ]);
    }

    /**
     * {inheridoc}
     */
    public function execute(Cli $console)
    {
        if (getenv('DYNO')) {
            $console->out('Heroku environments detected');
            return $this->setup_heroku($console);
        }

        if ($this->setup_config($console) === true) {
            $console->out('<bold><underline>New .env generated</underline></bold>');
            // $this->CI->load->library('migration');
            $this->setup_server($console);

            return $console->out('<green>'.Cli::lang('console_install_done').'</green>');
        }
    }

    /**
     * Setup Procfile for heroku environment
     *
     * @param  Projek\CI\Console\Cli $console CLI instance
     * @return Projek\CI\Console\Cli
     */
    protected function setup_config(Cli $console)
    {
        if (file_exists(APPPATH.'.env')) {
            $console->out('You already have .env in your APPPATH.');
            return true;
        }

        $arg = $console->argument_manager();

        if ($arg->defined('noninteractive')) {
            $console->out('<bold>Your have no instactive shell. please run <yellow>./app/cli install</yellow> manualy</bold>');
            return false;
        }

        $console->out('<bold><underline>we need to create the main configuration.</underline></bold>');

        $base_url = $console->input('   Application base url: ', '/');
        $db_host  = $console->input('      Database Hostname: ', 'localhost');
        $db_user  = $console->input('      Database Username: ', 'root');
        $db_pass  = $console->password('      Database Password: ');
        $db_name  = $console->input('          Database Name: ');
        $db_pref  = $console->input('  Database Table Prefix: ', 'app_');

        $replacement = [
            'APP_BASE_URL=\'/\'' => 'APP_BASE_URL=\''.$base_url.'\'',
            'APP_DB_HOST='       => 'APP_DB_HOST='.$db_host,
            'APP_DB_USER='       => 'APP_DB_USER='.$db_user,
            'APP_DB_PASS='       => 'APP_DB_PASS='.$db_pass,
            'APP_DB_NAME='       => 'APP_DB_NAME='.$db_name,
            'APP_DB_PREF='       => 'APP_DB_PREF='.$db_pref,
        ];

        $key = substr(md5(uniqid(mt_rand().serialize($replacement))), 0, 16);
        $replacement['APP_PRIVATE_KEY='] = 'APP_PRIVATE_KEY='.md5($key);

        copy(APPPATH.'env.txt', APPPATH.'.env');

        $file = APPPATH.'.env';
        $content = file_get_contents($file);
        $content = str_replace(array_keys($replacement), array_values($replacement), $content);
        file_put_contents($file, $content);

        return true;
    }

    /**
     * Setup server configuration
     *
     * @param  Projek\CI\Console\Cli $console CLI instance
     * @return Projek\CI\Console\Cli
     */
    protected function setup_server(Cli $console)
    {
        $server = $console->radio(
            '  What is your server application: ',
            ['apache', 'nginx']
        ) ?: 'apache';

        return call_user_func([$this, 'setup_'.$server], $console);
    }

    /**
     * Setup Procfile for heroku environment
     *
     * @param  Projek\CI\Console\Cli $console CLI instance
     * @return Projek\CI\Console\Cli
     */
    protected function setup_heroku(Cli $console)
    {
        if (file_exists(FCPATH.'Procfile')) {
            return $console->out('Procfile already generated');
        }

        $file = fopen(FCPATH.'Procfile', 'w');
        $content = 'web: vendor/bin/heroku-php-nginx public/';
        fwrite($file, $content);
        fclose($file);

        return $console->out('New Procfile generated');
    }

    /**
     * Setup Apache .htaccess
     *
     * @param  Projek\CI\Console\Cli $console     CLI instance
     * @param  string                $rewriteBase Apache rewrite base
     * @return Projek\CI\Console\Cli
     */
    protected function setup_apache(Cli $console, $rewriteBase = '/')
    {
        if (file_exists(FCPATH.'public/.htaccess')) {
            return $console->out('You already have .htaccess in your public dir');
        }

        $content = <<<HTACCESS
<IfModule mod_rewrite.c>
    Options +FollowSymLinks -Indexes
    RewriteEngine on

    RewriteBase $rewriteBase

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?/$1 [L]
</IfModule>

<IfModule !mod_rewrite.c>
    ErrorDocument 404 index.php
</IfModule>
HTACCESS;

        $file = fopen(FCPATH.'public/.htaccess', 'w');
        fwrite($file, $content);
        fclose($file);

        return $console->out('.htaccess generated in your public dir');
    }

    /**
     * Setup for NginX
     *
     * @param  Projek\CI\Console\Cli $console CLI instance
     * @return Projek\CI\Console\Cli
     */
    protected function setup_nginx(Cli $console)
    {
        return $console->out('currently we have no pre-installed config for nginx');
    }
}
