<?php
/**
 * Obereg Bundle
 *
 * @copyright 2014, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @author    Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license   http://opensource.org/licenses/MIT MIT
 */
namespace Mekras\OberegBundle\Tests\Monolog\Processor;

use Mekras\OberegBundle\Monolog\Processor\BacktraceProcessor;
use Monolog\Logger;

/**
 * Tests for Mekras\OberegBundle\Monolog\Processor\BacktraceProcessor
 *
 * @covers Mekras\OberegBundle\Monolog\Processor\BacktraceProcessor
 */
class BacktraceProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basics
     */
    public function testBasics()
    {
        $processor = new BacktraceProcessor();
        $record = ['level' => Logger::WARNING];
        $record = $processor->__invoke($record);
        static::assertArrayHasKey('backtrace', $record['extra']);
    }

    /**
     * Check representation of log string args
     */
    public function testLongStringArg()
    {
        $foo = function () {
            return $this->log(Logger::WARNING);
        };

        $shortString = str_repeat('*', 1024);
        $longString = $shortString . $shortString;
        $record = $foo($longString);

        static::assertNotContains($longString, $record['extra']['backtrace']);
        static::assertContains($shortString, $record['extra']['backtrace']);
    }

    /**
     * Check usage of exception from context
     */
    public function testExceptionFromContext()
    {
        $processor = new BacktraceProcessor();
        $record = ['level' => Logger::ERROR, 'context' => ['exception' => new \Exception('foo')]];
        $record = $processor->__invoke($record);
        static::assertArrayHasKey('backtrace', $record['extra']);
        static::assertContains('testExceptionFromContext', $record['extra']['backtrace']);
    }

    /**
     * Emulating processor call from Monolog code
     *
     * @param int $level message severity
     * @param int $counter
     *
     * @return array
     */
    private function log($level, $counter = 1)
    {
        if ($counter > 0) {
            return $this->log($level, --$counter);
        } else {
            $processor = new BacktraceProcessor();
            $record = ['level' => $level];
            return $processor->__invoke($record);
        }
    }
}
