<?php
/**
 * @name        CodeIgniter HMVC Modules
 * @author      Jens Segers
 * @link        http://www.jenssegers.be
 * @license     MIT License Copyright (c) 2012 Jens Segers
 *
 * @author      hArpanet
 * @link        http://harpanet.com
 *              Updated for CI 3.0-dev.
 */
namespace Projek\CI\Common\Module;

use CI_Loader;

class Loader extends CI_Loader
{
    /**
     * List of loaded controllers
     *
     * @var array
     * @access protected
     */
    protected $_ci_controllers = [];

    /**
     * Module instance
     *
     * @var Projek\CI\Common\Module
     */
    protected $module;

    /**
     * Constructor
     *
     * Add the current module to all paths permanently
     */
    public function __construct()
    {
        parent::__construct();

        // Get current module from the router
        $router = &$this->_ci_get_component('router');
        $this->module = $router->getModule()->add($this);
    }

    /**
     * Controller Loader
     *
     * This function lets users load and hierarchical controllers to enable HMVC support
     *
     * @param  string  the uri to the controller
     * @param  array   parameters for the requested method
     * @param  boolean return the result instead of showing it
     * @return void
     */
    public function controller($uri, $params = [], $return = false)
    {
        // No valid module detected, add current module to uri
        list($module) = $this->module->detect($uri);
        if (!isset($module)) {
            // $router = &$this->_ci_get_component('router');
            if ($module = $this->module->getCurrent()) {
                $uri    = $module . '/' . $uri;
            }
        }

        // Add module
        $this->module->add($this, $module);
        // Execute the controller method and capture output
        $void = $this->_load_controller($uri, $params, $return);
        // Remove module
        $this->module->remove($this);

        return $void;
    }

    /**
     * Load Widget
     *
     * This function provides support to Jens Segers Template Library for loading
     * widget controllers within modules (place in module/widgets folder).
     * @author hArpanet - 23-Jun-2014
     *
     * @param  string $widget  Must contain Module name if widget within a module
     *                         (eg. test/nav  where module name is 'test')
     * @return array|false
     */
    public function widget($widget)
    {

        // Detect module
        if (list($module, $widget) = $this->module->detect($widget)) {
            // Module already loaded
            if ($this->module->loaded($module)) {
                return [$module, $widget];
            }

            // Add module
            $this->module->add($this, $module);
            // Look again now we've added new module path
            $void = $this->widget($module . '/' . $widget);
            // Remove module if widget not found within it
            if (!$void) {
                $this->module->remove($this);
            }

            return $void;

        } else {
            // widget not found in module
            return false;
        }
    }

    /**
     * Controller loader
     *
     * This function is used to load and instantiate controllers
     *
     * @param  string
     * @param  array
     * @param  boolean
     * @return object
     */
    private function _load_controller($uri = '', $params = [], $return = false)
    {
        $router = &$this->_ci_get_component('router');

        // Back up current router values (before loading new controller)
        $backup = [];
        foreach (['directory', 'class', 'method', 'module'] as $prop) {
            if ($prop == 'module') {
                $prop = $this->module->getCurrent();
            }
            $backup[$prop] = $router->{$prop};
        }

        // Locate the controller
        $segments = $router->locate(explode('/', $uri));
        $class    = isset($segments[0]) ? $segments[0] : false;
        $method   = isset($segments[1]) ? $segments[1] : "index";

        // Controller not found
        if (!$class) {
            return;
        }

        if (!array_key_exists(strtolower($class), $this->_ci_controllers)) {
            // Determine filepath
            $filepath = APPPATH . 'controllers/' . $router->fetch_directory() . $class . '.php';
            // Load the controller file
            if (file_exists($filepath)) {
                include_once $filepath;
            }
            // Controller class not found, show 404
            if (!class_exists($class)) {
                show_404("{$class}/{$method}");
            }
            // Create a controller object
            $this->_ci_controllers[strtolower($class)] = new $class();
        }

        $controller = $this->_ci_controllers[strtolower($class)];

        // Method does not exists
        if (!method_exists($controller, $method)) {
            show_404("{$class}/{$method}");
        }

        // Restore router state
        foreach ($backup as $prop => $value) {
            $router->{$prop} = $value;
        }

        // Capture output and return
        ob_start();
        $result = call_user_func_array([$controller, $method], $params);

        // Return the buffered output
        if ($return === true) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        // Close buffer and flush output to screen
        ob_end_flush();

        // Return controller return value
        return $result;
    }
}
