<?php
/**
 * Created by PhpStorm.
 * User: datlt
 * Date: 05/07/2018
 * Time: 09:51
 */

namespace App\Http\Helper;


use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class LogToChannels
{
    /**
     * The LogToChannels channels.
     *
     * @var Logger[]
     */
    protected $channels = [];

    /**
     * LogToChannels constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $channel The channel to log the record in
     * @param$level The error level
     * @param $message The error message
     * @param array $context Optional context arguments
     *
     * @return bool Whether the record has been processed
     */
    public function log($channel, $level, $message, array $context = [])
    {
        // Add the logger if it doesn't exist
        if (!isset($this->channels[$channel])) {
            $handler = new StreamHandler(
                storage_path() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $channel . '.log'
            );

            $handler->setFormatter(new LineFormatter(null, null, true, true));

            $this->addChannel($channel, $handler);
        }

        // LogToChannels the record
        return $this->channels[$channel]->{Logger::getLevelName($level)}($message, $context);
    }

    /**
     * Add a channel to log in
     *
     * @param $channelName The channel name
     * @param HandlerInterface $handler The channel handler
     * @param string|null $path The path of the channel file, DEFAULT storage_path()/logs
     *
     * @throws \Exception When the channel already exists
     */
    public function addChannel($channelName, HandlerInterface $handler, $path = null)
    {
        if (isset($this->channels[$channelName])) {
            throw new \Exception('This channel already exists');
        }

        $this->channels[$channelName] = new Logger($channelName);
        $this->channels[$channelName]->pushHandler(
            new $handler(
                $path === null ?
                    storage_path() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $channelName . '.log' :
                    $path . DIRECTORY_SEPARATOR . $channelName . '.log'
            )
        );
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function debug($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::DEBUG, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function info($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function notice($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function warn($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function warning($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function err($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function error($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function crit($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return Boolean Whether the record has been processed
     */
    public function critical($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function alert($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function emerg($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::EMERGENCY, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * @param  $channel The channel name
     * @param  $message The log message
     * @param  array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function emergency($channel, $message, array $context = [])
    {
        return $this->log($channel, Logger::EMERGENCY, $message, $context);
    }
}