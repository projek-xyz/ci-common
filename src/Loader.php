<?php
namespace Projek\CI\Common;

class Loader extends Module\Loader
{
    public function __construct()
    {
        parent::__construct();

        $this->views = new Views($this->module);
    }

    public function view($view, $vars = [], $return = false)
    {
        $this->views->addData($this->_ci_cached_vars);

        $CI =& get_instance();
        $contents = $this->views->render($view, $vars = []);
        $CI->output->append_output($contents);

        return $this;
    }
}
