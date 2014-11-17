<?php
/**
 * Obereg Bundle
 *
 * @copyright 2014, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @author    Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license   http://opensource.org/licenses/MIT MIT
 */
namespace Mekras\OberegBundle\Tests\Debug\Formatter;

use Mekras\OberegBundle\Debug\Formatter\StackTraceFormatter;

/**
 * Tests for Mekras\OberegBundle\Debug\Formatter\StackTraceFormatter
 *
 * @covers Mekras\OberegBundle\Debug\Formatter\StackTraceFormatter
 */
class StackTraceFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check representation of log string args
     */
    public function testLongStringArg()
    {
        $shortString = str_repeat('*', 1024);
        $longString = $shortString . $shortString;

        $stack = [
            [
                'file' => 'foo.php',
                'line' => 10,
                'function' => 'bar',
                'args' => [$longString]
            ]
        ];

        $formatter = new StackTraceFormatter();
        $result = $formatter->format($stack);

        $this->assertNotContains($longString, $result);
        $this->assertContains($shortString, $result);
    }
}
