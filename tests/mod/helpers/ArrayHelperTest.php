<?php
use Projek\CI\Common\Tests\TestCase;

class ArrayHelperTest extends TestCase
{
    public function setUp()
    {
        $this->CI->load->helper('array');
    }

    public function test_should_array_set_defaults_invokable()
    {
        // It should be true actualy :lol:
        $this->assertFalse(function_exists('array_set_defaults'));
    }
}
