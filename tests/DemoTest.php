<?php

namespace Saritasa\LaravelUploads\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Check that unit tests configured and can be run.
 */
class DemoTest extends TestCase
{
    /**
     * Simple test to check that unit tests working.
     */
    public function testUnitTestAvailable()
    {
        $this->assertTrue(true);
    }

    /**
     * Check, that main project class can is accessible in tests, using autoload
     */
    public function testExample()
    {
        $example = new \Saritasa\LaravelUploads\Example();
        $this->assertEquals('test', $example->echoPhrase('test'));
    }
}
