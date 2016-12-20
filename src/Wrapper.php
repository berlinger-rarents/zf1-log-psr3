<?php

namespace Boerl\Zf1LogPsr3;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Zend_Log as Zf1Logger;

class Wrapper extends AbstractLogger
{
    /* @var Zf1Logger The original ZF1 Logger */
    protected $zf1Logger;

    /* @var array<string,mixed> Config. */
    protected $config;

    /**
     * @param Zf1Logger $zf1Logger The original ZF1 Logger to be wrapped.
     * @param array<string,mixed> $config {@see setConfig()}
     */
    public function __construct(Zf1Logger $zf1Logger, array $config = [])
    {
        $this->zf1Logger = $zf1Logger;
        $this->setConfig($config);
    }

    /**
     * @param array $config Will be merged with {@see getDefaultConfig()} into the configuration for this logger.
     */
    public function setConfig(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * @return array<string,mixed> Default configuration.
     *  - `add_log_level` : bool [=true] - Whether to include `logLevel` in the `extras` array.
     *  - `rewrite_placeholders` : bool [=true] - Whether to rewrite PSR-3 style placeholders into ZF1 style.
     */
    public function getDefaultConfig()
    {
        return [
            'add_log_level' => true,
            'rewrite_placeholders' => true,
        ];
    }

    /**
     * @inheritdoc
     */
    public function log($psrLogLevel, $message, array $context = [])
    {
        // Translate from PSR3 log level to ZF1 priority.
        $zf1LogPriority = $this->psrLogLevelToZf1($psrLogLevel);

        // Add `logLevel` to the context.
        if ($this->config['add_log_level']) {
            $context = array_merge(['logLevel' => $psrLogLevel], $context);
        }

        // Replace PSR placeholders with ZF1 placeholders.
        if ($this->config['rewrite_placeholders']) {
            $this->psrPlaceHoldersToZf1($message, array_keys($context));
        }

        try {
            $this->zf1Logger->log($message, $zf1LogPriority, $context);
        } catch (\Zend_Log_Exception $zf1LogEx) {
            throw new InvalidArgumentException($zf1LogEx->getMessage(), $zf1LogEx->getCode(), $zf1LogEx);
        }
    }

    /**
     * Translates a PSR-3 Log Level to a ZF1 Log Priority.
     *
     * @param int $psrLogLevel
     * @return int|null
     */
    public static function psrLogLevelToZf1($psrLogLevel)
    {
        switch ($psrLogLevel) {
            case LogLevel::EMERGENCY:
                return Zf1Logger::EMERG;
            case LogLevel::ALERT:
                return Zf1Logger::ALERT;
            case LogLevel::CRITICAL:
                return Zf1Logger::CRIT;
            case LogLevel::ERROR:
                return Zf1Logger::ERR;
            case LogLevel::WARNING:
                return Zf1Logger::WARN;
            case LogLevel::NOTICE:
                return Zf1Logger::NOTICE;
            case LogLevel::INFO:
                return Zf1Logger::INFO;
            case LogLevel::DEBUG:
                return Zf1Logger::DEBUG;
        }

        return null;
    }

    /**
     * Rewrites PSR-3 style placeholders to ZF1 style. E.g. `{example}` becomes `%example%`.
     *
     * @param string $message Will be rewritten.
     * @param string[] $names Only replace these names. (Optional. By default, all will be rewritten.)
     */
    public static function psrPlaceHoldersToZf1(&$message, array $names = null)
    {
        $message = preg_replace_callback('/{(?<name>[A-Za-z0-9_.]+)}/', function($match) use ($names) {
            $name = $match['name'];
            if ($names && !in_array($name, $names)) {
                return $match[0];
            } else {
                return "%{$name}%";
            }
        }, $message);
    }
}