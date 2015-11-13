<?php
namespace Projek\CI\Common\Utils;

trait HooksHandlerTrait
{
    /**
     * Hook wrapper
     *
     * @var  array
     */
    private $_hooks = [];

    /**
     * Setup hook
     *
     * @param  string  $name        Hook name
     * @param  callable  $callback  Hook callable
     */
    protected function set_hook($name, $callback)
    {
        $this->_hooks[$name] = $callback;
    }

    /**
     * Invoke hook
     *
     * @return  mixed
     */
    protected function call_hook()
    {
        $args = func_get_args();
        $name = array_shift($args);

        if ($this->has_hook($name)) {
            $callback = $this->_hooks[$name];

            if ( ! is_callable($callback)) {
                return false;
            }

            if (is_php('5.4.0') and $callback instanceof Closure) {
                $callback->bindTo($this);
            }

            return call_user_func_array($callback, $args);
        }

        return false;
    }

    /**
     * Determine wheter this class has hook called $name
     *
     * @param   string  $name  Hook Name
     * @return  bool
     */
    protected function has_hook($name)
    {
        return (bool) isset($this->_hooks[$name]);
    }
}
