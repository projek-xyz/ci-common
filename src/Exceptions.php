<?php
namespace Projek\CI\Common;

use CI_Exceptions;
use Projek\CI\Console\ExceptionsTrait;

class Exceptions extends CI_Exceptions
{
    use ExceptionsTrait;

    private $views;
    private $_template_path;

    public function __construct()
    {
        $config =& load_class('Config');
        $module = new Module($config);
        $this->views = new Views($module);

        if (! $_template_path = config_item('error_views_path')) {
            $_template_path = VIEWPATH . '/errors/';
        }

        $this->_template_path = str_replace(VIEWPATH, '', $_template_path);
    }

    /**
     * {inheritdoc}
     */
    public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        if ($template == 'error_db') {
            $CI =& get_instance();
            if ( ! isset($CI->db) or false === $CI->load->database()) {
                $heading = 'You must create a database first';
            }
        }

        $traces = null;
        if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE) {
            $traces = debug_backtrace();
        }

        return $this->_render_error($template, [
            'heading' => $heading,
            'message' => $message,
            'traces' => $traces,
        ], $status_code);
    }

    /**
     * {inheritdoc}
     */
    public function show_exception($exception)
    {
        $heading = get_class($exception);
        $message = $exception->getMessage();

        if (!$message) {
            $message = '(null)';
        }

        $traces = null;
        if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE) {
            $traces = $exception->getTrace();
        }

        echo $this->_render_error('error_exception', [
            'heading' => get_class($exception),
            'message' => $message,
            'filename' => str_replace(FCPATH, '', $exception->getFile()),
            'line' => $exception->getLine(),
            'traces' => $traces,
        ]);
    }

    /**
     * {inheritdoc}
     */
    // public function show_php_error($severity, $message, $filepath, $line)
    // {
    //     $traces = null;
    //     if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE) {
    //         $traces = debug_backtrace();
    //     }

    //     echo $this->_render_error('error_php', [
    //         'severity' => $severity,
    //         'message' => $message,
    //         'filename' => $filepath,
    //         'line' => $line,
    //         'traces' => $traces,
    //     ], 500);
    // }

    protected function _render_error($template, $data, $status_code = 500)
    {
        $config =& load_class('Config');

        if (is_cli()) {
            $console = new Console();

            $heading = $data['heading'];
            unset($data['heading']);

            $traces = [];
            if ($data['traces']) {
                $traces = $data['traces'];
                unset($data['traces']);
            }

            $this->render_cli_error($heading, $data, $traces);
            exit(1);
        }

        $module = new Module($config);
        $views = new Views($module);

        if (! $_template_path = config_item('error_views_path')) {
            $_template_path = VIEWPATH . '/errors/';
        }

        $_template_path = str_replace(VIEWPATH . '/', '', $_template_path);

        set_status_header($status_code);
        return $views->render($_template_path . 'html/' . $template, $data);
    }
}
