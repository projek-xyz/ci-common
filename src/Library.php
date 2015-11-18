<?php
namespace Projek\CI\Common;

class Library
{
    /**
     * Make use of common traits
     */
    use Utils\ErrorHandlerTrait;

    /**
     * Just to make thing shorter
     *
     * @param  string $var Codeigniter Object
     * @return mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }
}
