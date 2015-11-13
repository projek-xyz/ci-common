<?php
namespace Projek\CI\Common;

use CI_Config;

class Module
{
    protected $types = [
        'projek-ci-module' => 'module',
        'projek-ci-theme' => 'theme',
    ];

    protected $config;

    protected $current;

    protected $loaded = [];

    protected $list = [];

    protected $assets;

    public function __construct(CI_Config $config)
    {
        $this->config =& $config;

        // Process 'modules_locations' from config
        $locations = $this->config->item('modules_locations') ?: FCPATH . 'modules/';
        if (!is_array($locations)) {
            $locations = [$locations];
        }

        foreach ($this->getList('module') as $module) {
            $locations[] = $module->path . 'mod/';
        }

        // Make sure all paths are the same format
        sort($locations);
        foreach ($locations as &$location) {
            $location = realpath($location);
            $location = str_replace('\\', '/', $location);
            $location = rtrim($location, '/') . '/';
        }

        $this->config->set_item('modules_locations', $locations);
    }

    public function initialize()
    {
        $autoloadFile = $this->config->item('composer_autoload');
        $vendorDir = dirname($autoloadFile) . '/';
        $composer = @file_get_contents($vendorDir . 'composer/installed.json');
        if (!$composer) {
            throw new \Exception('Failed to load components, could not load composer/installed.json file');
        }

        $composer = json_decode($composer);
        $list = [];

        foreach ($composer as $package) {
            if (isset($this->types[$package->type]) && isset($package->extra)) {
                $extra = (array) $package->extra;
                $list[$package->name] = (object) [
                    'name' => isset($extra['projek-module-name']) ? $extra['projek-module-name'] : null,
                    'homepage' => isset($package->homepage) ? $package->homepage : 'http://feryardiant.me',
                    'description' => $package->description,
                    'authors' => $package->authors,
                    'type' => $this->types[$package->type],
                    'path' => $vendorDir . $package->name . '/',
                ];
            }
        }

        ksort($list);
        $this->list = (object) $list;
    }

    public function getList($type = null)
    {
        if (empty($this->list)) {
            $this->initialize();
        }

        if ($type !== null) {
            static $list;

            foreach ($this->list as $package => $module) {
                if ($module->type === $type) {
                    $list[$package] = $module;
                }
            }

            return $list;
        }

        return $this->list;
    }

    public function setCurrent($moduleName)
    {
        $this->current = $moduleName;
    }

    public function getCurrent()
    {
        return $this->current;
    }

    public function get($moduleName)
    {
        $module = null;

        foreach ($this->list as $item) {
            if ($item->name == $moduleName) {
                $module = $item;
                break;
            }
        }

        return $module;
    }

    public function getModPath($moduleName = '')
    {
        $moduleName || $moduleName = $this->getCurrent();

        if ($module = $this->get($moduleName)) {
            return $module->path . 'mod/';
        }

        return false;
    }

    public function add(Module\Loader $loader, $moduleName = '', $view_cascade = true)
    {
        if ($moduleName == '') {
            $moduleName = $this->getCurrent();
        }

        if ($modulePath = $this->getModPath($moduleName)) {
            // Mark module as loaded
            array_unshift($this->loaded, $moduleName);
            // Add package path
            $loader->add_package_path($modulePath, $view_cascade);
        }

        return $this;
    }

    public function remove(Module\Loader $loader, $moduleName = '')
    {
        if ($moduleName = '') {
            // Mark module as not loaded
            array_shift($this->loaded);
            // Remove package path
            $loader->remove_package_path();
        } elseif (($key = array_search($moduleName, $this->loaded)) !== false) {
            if ($modulePath = $this->getModPath($moduleName)) {
                // Mark module as not loaded
                unset($this->loaded[$key]);
                // Remove package path
                $loader->remove_package_path($modulePath . 'mod/');
            }
        }
    }

    public function loaded($moduleName)
    {
        return in_array($moduleName, $this->loaded);
    }

    public function find($module)
    {
        foreach ($this->config->item('modules_locations') as $location) {
            $path = $location . rtrim($module, '/') . '/';
            if (is_dir($path)) {
                return $path;
            }
        }

        return false;
    }

    public function detect($class)
    {
        $class = str_replace('.php', '', trim($class, '/'));
        if (($first_slash = strpos($class, '/')) !== false) {
            $module = substr($class, 0, $first_slash);
            $class  = substr($class, $first_slash + 1);

            // Check if module exists
            if ($this->get($module) === null) {
                $module = $this->getCurrent();
            }

            return [$module, $class];
        }

        return false;
    }
}
