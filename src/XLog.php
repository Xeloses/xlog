<?php

/*
 * Intercept and log PHP exceptions/errors/warnings.
 *
 * @author     Xeloses (https://github.com/Xeloses)
 * @package    XLog (https://github.com/Xeloses/xlog)
 * @version    1.0
 * @copyright  Xeloses 2018-2020
 * @license    GNU GPL v3 (https://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace Xeloses\XLog;

use Xeloses\XLog\Interfaces\ILogOutputProvider;
use Xeloses\XLog\Providers\LogScreen;

/**
 * XLog class
 *
 * @package XLog
 *
 * @method void register(ILogOutputProvider $provider, ?int $log_level, ?bool $terminate_on_error, ?bool $debug)
 * @method void log(string $message)
 * @method void dump($value, ?string $comment)
 * @method bool isDebug()
 */

class XLog
{
    /**
     * Error levels.
     *
     * @see https://www.php.net/manual/ru/errorfunc.constants.php
     *
     * @const int
     */
    const ALL      = -1;
    const NONE     = 0;
    const ERRORS   = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_USER_ERROR;
    const WARNINGS = E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING;
    const NOTICES  = E_NOTICE | E_STRICT | E_DEPRECATED | E_USER_NOTICE | E_USER_DEPRECATED;

    const DEFAULT  = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED;
    const DEBUG    = -1;

    /**
     * Log output provider.
     *
     * @var ILogOutputProvider
     */
    protected static $output = null;

    /**
     * Debug mode.
     *
     * @var bool
     */
    protected static $debug = false;

    /**
     * Log level.
     *
     * @var int
     */
    protected static $log_level;

    /**
     * Register logger.
     *
     * @param ILogOutputProvider|null $provider  - can be NULL only when $debug is TRUE
     * @param int                     $log_level (optional) default: self::DEFAULT constant
     * @param bool                    $debug     (optional) default: FALSE
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public static function register(?ILogOutputProvider $provider = null, int $log_level = self::DEFAULT, bool $debug = false): void
    {
        self::$debug = $debug || (defined('DEBUG') && DEBUG);

        if(!self::$debug && is_null($provider))
        {
            throw new \InvalidArgumentException('Log output provider required.');
        }

        self::$log_level = self::$debug ? self::DEBUG : $log_level;
        self::$output    = (self::$debug && is_null($provider)) ? new LogScreen() : $provider;

        // After this any changes to class attributes will be ignored!

        register_shutdown_function(\Closure::fromCallable([__CLASS__,'handleShutdown']));
        set_exception_handler(\Closure::fromCallable([__CLASS__,'handleException']));
        set_error_handler(\Closure::fromCallable([__CLASS__,'handleError']),self::$log_level);

        ini_set('display_errors',0);
        ini_set('ignore_repeated_errors',1);
        ini_set('ignore_repeated_source',0);
        error_reporting(0);
    }

    /**
     * Add custom message to log.
     *
     * @param string $message
     *
     * @return void
     */
    public static function log(string $message): void
    {
        if(self::$output)
        {
            self::$output->logMessage($message);
        }
    }

    /**
     * Dump variable to log (only in Debug mode).
     *
     * @param mixed  $value
     * @param string $comment (optional)
     *
     * @return void
     */
    public static function dump($value, string $comment = ''): void
    {
        if(self::isDebug() && self::$output)
        {
            self::$output->logValue($value,$comment);
        }
    }

    /**
     * Get status of Debug mode.
     *
     * @return bool
     */
    public static function isDebug(): bool
    {
        return self::$debug;
    }

    /**
     * Handle errors/warnings/notices.
     *
     * @param int    $code
     * @param string $description
     * @param string $file
     * @param int    $line
     *
     * @return bool
     */
    protected static function handleError(int $code, string $description, string $file, int $line): bool
    {
        // check this error is in current log level:
        if(!(self::$log_level & $code))
        {
            // let PHP process this error:
            return false;
        }

        $processed = self::$output->logError($code,$description,$file,$line);

        if(self::ERRORS & $code)
        {
            exit(1);
        }

        return $processed;
    }

    /**
     * Handle exceptions.
     *
     * @param Throwable $e
     *
     * @return void
     */
    protected static function handleException(\Throwable $e): void
    {
        try
        {
            self::$output->logException($e);
        }
        catch(Exception $ex)
        {
            print 'Exception ['.ltrim(get_class($ex),'\\').']: "'.$ex->getMessage().'"'.PHP_EOL.PHP_EOL.'Ecxeption was thrown when tried to handle exception:'.PHP_EOL;
            print_r($e);
        }
        finally
        {
            exit(1);
        }
    }

    /**
     * Handle fatal errors.
     *
     * @return void
     */
    protected static function handleShutdown(): void
    {
        $error = error_get_last();
        if($error && $error["type"])
        {
            self::handleError($error["type"],$error["message"],$error["file"],$error["line"]);
        }
    }
}
?>
