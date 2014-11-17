<?php
/**
 * Obereg Bundle
 *
 * @copyright 2014, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @author Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Mekras\OberegBundle\Debug\Formatter;

/**
 * Tool for formatting stack traces
 *
 * @since x.xx
 */
class StackTraceFormatter
{
    /**
     * Max length of string arguments
     *
     * @var int
     */
    private $stringMaxLength = 1024;

    /**
     * Formats stack trace as string
     *
     * @param array $stack stack trace
     *
     * @return string
     *
     * @since x.xx
     */
    public function format(array $stack)
    {
        $string = '';
        $level = 1;
        foreach ($stack as $call) {
            $string .= sprintf('#%d %s ', $level, $this->convertCallToString($call));
            $level++;
        }
        return $string;
    }

    /**
     * Returns max length of string arguments
     *
     * @return int
     *
     * @since x.xx
     */
    public function getStringMaxLength()
    {
        return $this->stringMaxLength;
    }

    /**
     * Sets max length of string arguments
     *
     * @param int $characters
     *
     * @return void
     *
     * @since x.xx
     */
    public function setStringMaxLength($characters)
    {
        $this->stringMaxLength = intval($characters);
        if ($this->stringMaxLength < 0) {
            $this->stringMaxLength = 0;
        }
    }

    /**
     * Represents function call info as string
     *
     * @param array $call call info from {@link debug_backtrace()}
     *
     * @return string
     */
    private function convertCallToString(array $call)
    {
        $result = '';
        if (array_key_exists('file', $call)) {
            $result .= $call['file'];
        }
        if (array_key_exists('line', $call)) {
            $result .= '(' . $call['line'] . ')';
        }
        if ($result) {
            $result .= ': ';
        }
        if (array_key_exists('class', $call)) {
            $result .= $call['class'] . $call['type'];
        }
        if (array_key_exists('function', $call)) {
            $result .= $call['function'];
        }
        if (array_key_exists('args', $call)) {
            $args = [];
            foreach ($call['args'] as $arg) {
                $args []= $this->convertArgToString($arg);
            }
            $result .= '(' . implode(', ', $args) . ')';
        }
        return $result;
    }

    /**
     * Represents function call argument as string
     *
     * @param mixed $arg argument info from {@link debug_backtrace()}
     *
     * @return string
     */
    private function convertArgToString($arg)
    {
        switch (true) {
            case is_object($arg):
                $arg = get_class($arg);
                break;

            case is_array($arg):
                $arg = 'Array';
                break;

            case is_string($arg):
                $arg = "'" . mb_substr($arg, 0, $this->getStringMaxLength(), 'utf-8') . "'";
                break;

            default:
                $arg = strval($arg);
        }

        return $arg;
    }
}
