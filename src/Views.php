<?php
namespace Projek\CI\Common;

use League\Plates\Engine;
use Projek\CI\Common\Module;

class Views
{
    /**
     * Plates Instance
     *
     * @var League\Plates\Engine
     */
    protected $engine;

    /**
     * View paths
     *
     * @var array
     */
    protected $paths = [];

    public function __construct(Module $module, array $data = [])
    {
        $this->engine = new Engine(VIEWPATH, 'php');

        foreach ($module->getList('module') as $mod) {
            $this->paths[$mod->name] = $mod->path . 'mod/views/';
            $this->engine->addFolder($mod->name, $this->paths[$mod->name]);
        }

        if ($currentModule = $module->getCurrent()) {
            $this->engine->setDirectory($this->paths[$currentModule]);
        }
    }

    /**
     * Get the Plates Engine Instance
     *
     * @return League\Plates\Engine
     */
    public function engine()
    {
        return $this->engine;
    }

    /**
     * Add data to render.
     *
     * @param  array  $data      Data
     * @param  string $templates Template
     * @return League\Plates\Engine
     */
    public function addData(array $data, $templates = null)
    {
        $this->engine->addData($data, $templates);

        return $this->engine;
    }

    /**
     * Add a new template folder for grouping templates under different namespaces.
     *
     * @param string $name      Folder name
     * @param string $directory Folder path
     * @param bool   $fallback  Folder falback
     */
    public function addFolder($name, $directory, $fallback = false)
    {
        $this->engine->addFolder($name, $directory, $fallback);

        return $this->engine;
    }

    /**
     * Remove a template folder.
     *
     * @param  string $name Folder name
     * @return League\Plates\Engine
     */
    public function removeFolder($name)
    {
        $this->engine->removeFolder($name);

        return $this;
    }

    /**
     * Create a new template and render it.
     *
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    public function render($name, array $data = [])
    {
        return $this->engine->render($name, $data);
    }
}
