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

use CI_Router;
use Projek\CI\Common\Module;

class Router extends CI_Router
{
    /**
     * Current module instance
     *
     * @var \Projek\CI\Common\Module
     */
    protected $module;

    /**
     * Constructor
     *
     * Runs the route mapping function.
     */
    public function __construct()
    {
        $this->config = &load_class('Config', 'core');
        $this->module = new Module($this->config);

        parent::__construct();
    }

    /**
     * {inheritdoc}
     */
    protected function _validate_request($segments)
    {
        if (count($segments) == 0) {
            return $segments;
        }

        // Locate the controller with modules support
        if ($located = $this->locate($segments)) {
            return $located;
        }

        // Is there a 404 override?
        if (!empty($this->routes['404_override'])) {
            $segments = explode('/', $this->routes['404_override']);
            if ($located = $this->locate($segments)) {
                return $located;
            }
        }

        // Nothing else to do at this point but show a 404
        return parent::_validate_request($segments);
    }

    /**
     * {inheritdoc}
     */
    protected function _parse_routes()
    {
        // Apply the current module's routing config
        foreach ($this->module->getList('module') as $module) {
            $mod_path = $this->module->getModPath($module->name);
            if (file_exists($mod_path.'config/routes.php')) {
                include $mod_path.'config/routes.php';
                $route = (!isset($route) or !is_array($route)) ? [] : $route;
                $this->routes = array_merge($route, $this->routes);
                unset($route);
            }
        }

        // Let parent do the heavy routing
        return parent::_parse_routes();
    }

    /**
     * {inheritdoc}
     */
    public function locate($segments)
    {
        // anon function to ucfirst a string if CI ver > 2 (for backwards compatibility)
        $_ucfirst = function ($cn) {
            return (intval(substr(CI_VERSION, 0, 1)) > 2) ? ucfirst($cn) : $cn;
        };

        if ($mod_sgmt = array_search($segments, $this->routes) !== false) {
            $segments = $mod_sgmt;
        }

        // var_dump($segments);
        list($firstSegment, $directory, $controller) = array_pad($segments, 3, null);

        // var_dump($this->uri->segments);
        $module = $this->module->get($firstSegment);

        if ($module === null) {
            return;
        }

        // if (($module = $this->module->get($firstSegment)) !== null) {
        // }
        $controllerPath = $module->path . 'mod/controllers/';
        $relative = $controllerPath;

        // Make path relative to controllers directory
        $start = rtrim(realpath(APPPATH), '/');
        $parts = explode('/', str_replace('\\', '/', $start));

        // Iterate all parts and replace absolute part with relative part
        for ($i = 1; $i <= count($parts); $i++) {
            $relative = str_replace(implode('/', $parts) . '/', str_repeat('../', $i), $relative, $count);
            array_pop($parts);

            // Stop iteration if found
            if ($count) {
                break;
            }
        }

        if (is_dir($controllerPath)) {
            $this->module->setCurrent($module->name);
            $this->directory = $relative;

            if ($directory && is_file($controllerPath . $_ucfirst($directory) . '.php')) {
                $this->class = $directory;
                return array_slice($segments, 1);
            }

            // Module sub-directory?
            if ($directory && is_dir($controllerPath . $directory . '/')) {
                $controllerPath = $controllerPath . $directory . '/';
                $this->directory .= $directory . '/';

                // Module sub-directory controller?
                if (is_file($controllerPath . $_ucfirst($directory) . '.php')) {
                    return array_slice($segments, 1);
                }

                // Module sub-directory  default controller?
                if (is_file($controllerPath . $_ucfirst($this->default_controller) . '.php')) {
                    $segments[1] = $this->default_controller;
                    return array_slice($segments, 1);
                }

                // Module sub-directory sub-controller?
                if ($controller && is_file($controllerPath . $_ucfirst($controller) . '.php')) {
                    return array_slice($segments, 2);
                }
            }

            // Module controller?
            if (is_file($controllerPath . $_ucfirst($module->name) . '.php')) {
                return $segments;
            }

            // Module default controller?
            if (is_file($controllerPath . $_ucfirst($this->default_controller) . '.php')) {
                $segments[0] = $this->default_controller;
                return $segments;
            }
        }

        // Root folder controller?
        if (is_file(APPPATH . 'controllers/' . $_ucfirst($module->name) . '.php')) {
            return $segments;
        }

        // Sub-directory controller?
        if ($directory && is_file(APPPATH . 'controllers/' . $module->name . '/' . $_ucfirst($directory) . '.php')) {
            $this->directory = $module->name . '/';
            return array_slice($segments, 1);
        }

        // Default controller?
        if (is_file(APPPATH . 'controllers/' . $module->name . '/' . $_ucfirst($this->default_controller) . '.php')) {
            $segments[0] = $this->default_controller;
            return $segments;
        }
    }

    /**
     * {inheritdoc}
     */
    protected function _set_default_controller()
    {
        // controller in APPPATH/controllers takes priority over module with same name
        parent::_set_default_controller();

        // see if parent found a controller
        $class = $this->fetch_class();

        if (empty($class)) {

            // no 'normal' controller found,
            // get the class/method from the default_controller route
            if (sscanf($this->default_controller, '%[^/]/%s', $class, $method) !== 2) {
                $method = 'index';
            }

            // try to locate default controller in modules
            if ($located = $this->locate([$class, $class, $method])) {
                log_message('debug', 'No URI present. Default module controller set.');
            }
        }

        // Nothing found - this will trigger 404 later
    }

    /**
     * Get module instance
     *
     * @return Projek\CI\Common\Module
     */
    public function getModule()
    {
        return $this->module;
    }
}
