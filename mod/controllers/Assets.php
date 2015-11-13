<?php
use Projek\CI\Common\Controller\Base;

class Assets extends Base
{
    private $requires = [];

    public function __construct()
    {
        parent::__construct();

        if ($requires = $this->input->get('load')) {
            $this->requires = explode(',', $requires);
        }
    }

    public function index()
    {
        $this->output->set_content_type('', 'utf-8');
    }

    public function styles()
    {
        $this->set_output('body {}', 'css');
    }

    public function scripts()
    {
        $this->set_output('console.log(\'Hallo\')', 'js');
    }

    public function images($path = null)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $this->set_output('', $ext);
    }

    protected function set_output($contents, $type, $status_code = 200)
    {
        $charset = in_array($type, ['js', 'css']) ? 'utf-8' : null;
        $this->output->set_status_header($status_code)
                     ->set_content_type($type, $charset)
                     ->set_output($contents)
                     ->_display();
        exit();
    }
}
