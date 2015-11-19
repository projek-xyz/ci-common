<?php
namespace Projek\CI\Common;

class Loader extends Module\Loader
{
    public function __construct()
    {
        parent::__construct();

        $CI =& get_instance();
        $CI->views = new Views($this->module);
    }

    /**
     * {inheritdoc}
     */
    public function view($view, $vars = [], $return = false)
    {
        $CI =& get_instance();

        $CI->views->add_data($this->_ci_cached_vars);
        $contents = $CI->views->render($view, $vars = []);
        $CI->output->append_output($contents);

        return $this;
    }
}
