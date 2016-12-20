<?php

namespace Boerl\Zf1LogPsr3;

/**
 * Collects log messages in memory. The messages are filtered and formatted as usual.
 *
 * Useful e.g. for testing purposes.
 */
class MemoryWriter extends \Zend_Log_Writer_Abstract
{
    /**
     * @var string[] {@see getMessages()}
     */
    protected $messages = [];

    /**
     * @return string[] The collected, formatted and filtered, log messages.
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return \Zend_Log_Formatter_Interface {@see setFormatter()}
     */
    public function getFormatter()
    {
        // Lazy load a default formatter.
        if (!isset($this->_formatter)) {
            $this->setFormatter(new \Zend_Log_Formatter_Simple('%message%'));
        }

        return $this->_formatter;
    }

    /**
     * @inheritdoc
     */
    protected function _write($event)
    {
        $message = $this->getFormatter()->format($event);
        $this->messages[] = $message;
    }

    /**
     * {@inheritdoc}
     *
     * No config is necessary.
     */
    static public function factory($config = [])
    {
        return new static();
    }
}