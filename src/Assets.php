<?php
namespace Projek\CI\Common;

use MiniAsset\AssetConfig;
use MiniAsset\Factory;

class Assets
{
    protected $CI;

    protected $configs = [
        'js' => [
            'paths' => ['FCPATH/asset/scripts/**']
        ],
        'css' => [
            'paths' => ['FCPATH/asset/styles/**']
        ],
    ];

    protected $factory;

    public function __construct(Module $modules, Module\Loader $load)
    {
        $configs = new AssetConfig($this->configs);

        if (! $cache_path = config_item('cache_path')) {
            $cache_path = FCPATH . 'asset/cache/';
            config_item('cache_path', $cache_path);
        }

        $configs->cachePath('js', $cache_path . 'js');
        $configs->cachePath('css', $cache_path . 'css');

        foreach ($modules->getList('module') as $module) {
            $configs->set('js.paths', $module->path . 'asset/scripts/**');
            $configs->set('css.paths', $module->path . 'asset/styles/**');
        }

        $this->factory = new Factory($configs);
    }
}
