<?php
/**
 * Obereg Bundle.
 *
 * @copyright 2014, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @author    Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license   http://opensource.org/licenses/MIT MIT
 */
namespace Mekras\OberegBundle\Monolog\Processor;

use Exception;
use Mekras\OberegBundle\Debug\Formatter\StackTraceFormatter;
use Monolog\Logger;

/**
 * Monolog processor which adds call stack to log messages.
 */
class BacktraceProcessor
{
    /**
     * Lowest severity needed to add stack trace.
     *
     * @var int
     */
    private $level;

    /**
     * Stack trace formatter.
     *
     * @var StackTraceFormatter|null
     */
    private $formatter = null;

    /**
     * Creates new processor.
     *
     * @param int|string $level Lowest severity needed to add stack trace.
     */
    public function __construct($level = Logger::WARNING)
    {
        $this->level = Logger::toMonologLevel($level);
    }

    /**
     * Adds stack trace to log record.
     *
     * @param array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        // Ignoring messages with severity below threshold
        if (!array_key_exists('level', $record) || $record['level'] < $this->level) {
            return $record;
        }

        if (!array_key_exists('extra', $record)) {
            $record['extra'] = [];
        }

        // Do nothing if stack already exists.
        if (array_key_exists('backtrace', $record['extra'])) {
            return $record;
        }

        if (!array_key_exists('context', $record)) {
            $record['context'] = [];
        }

        // Use backtrace from context if possible.
        if (array_key_exists('backtrace', $record['context'])) {
            $record['extra']['backtrace'] = $record['context']['backtrace'];

            return $record;
        }

        if (array_key_exists('exception', $record['context'])) {
            /* Symfony exception listener adds exception to context. We can use it. */

            $exception = $record['context']['exception'];
            if ($exception instanceof Exception) {
                $record['extra']['backtrace'] = $exception->getTraceAsString();
            }
        } else {
            $backtrace = debug_backtrace();

            /* Skipping Monolog part of stack */
            for ($i = 0; $i < 3; $i++) {
                array_shift($backtrace);
            }

            $formatter = $this->getFormatter();
            $record['extra']['backtrace'] = $formatter->format($backtrace);
        }

        return $record;
    }

    /**
     * Returns stack trace formatter.
     *
     * @return StackTraceFormatter
     */
    private function getFormatter()
    {
        if (null === $this->formatter) {
            $this->formatter = new StackTraceFormatter();
        }

        return $this->formatter;
    }
}
