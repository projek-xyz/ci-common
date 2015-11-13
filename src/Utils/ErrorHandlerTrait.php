<?php
namespace Projek\CI\Common\Utils;

trait ErrorHandlerTrait
{
    /**
     * Error wrapper
     *
     * @var  array
     */
    private $_errors = [];

    /**
     * Setup error message
     *
     * @param  string  $message  Error message
     */
    public function set_error($message, $halt = false)
    {
        if ($message instanceof Exception) {
            $message = $message->getMessage();
        }

        $this->_errors[] = $message;
        log_message('error', $message);

        if ($halt) {
            show_error($message);
        }
    }

    /**
     * Get all errors
     *
     * @return  array
     */
    public function get_errors()
    {
        return $this->_errors;
    }

    /**
     * Get latest error
     *
     * @return  string
     */
    public function last_error()
    {
        if ($this->has_errors()) {
            return array_pop($this->_errors);
        }
        return null;
    }

    /**
     * Grab all love message
     *
     * @return  array
     */
    public function has_errors()
    {
        return (bool) count($this->_errors) > 0;
    }

    /**
     * Clean up all error messages
     */
    public function clear_errors()
    {
        $this->_errors = [];
    }
}
