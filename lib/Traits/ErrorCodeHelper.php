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

namespace Xeloses\XLog\Traits;

/**
 * ErrorCodeHelper trait.
 *
 * Helper functions working with Error Code.
 *
 * @package    XLog
 * @subpackage Traits
 *
 * @method string getErrorCategory(int $error_code)
 * @method string getErrorType(int $error_code)
 */
trait ErrorCodeHelper
{
    /**
     * Get error category.
     *
     * @internal
     *
     * @param int $error_code
     *
     * @return string
     */
    protected function getErrorCategory(int $error_code): string
    {
        switch($error_code)
        {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return 'ERROR';
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                return 'WARNING';
            case E_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_NOTICE:
            case E_USER_DEPRECATED:
                return 'NOTICE';
        }
        return 'UNKNOWN';
    }

    /**
     * Get error type.
     *
     * @internal
     *
     * @param int $error_code
     *
     * @return string
     */
    protected function getErrorType(int $error_code): string
    {
        switch($error_code)
        {
            case E_PARSE:             return 'Parse error';
            case E_ERROR:             return 'Error';
            case E_CORE_ERROR:        return 'Core error';
            case E_COMPILE_ERROR:     return 'Compile error';
            case E_RECOVERABLE_ERROR: return 'Recoverable error';
            case E_USER_ERROR:        return 'User generated error';
            case E_WARNING:           return 'Warning';
            case E_COMPILE_WARNING:   return 'Compile warning';
            case E_USER_WARNING:      return 'User generated warning';
            case E_NOTICE:            return 'Notice';
            case E_STRICT:            return 'Strict notice';
            case E_DEPRECATED:        return 'Deprecated!';
            case E_USER_NOTICE:       return 'User generated notice';
            case E_USER_DEPRECATED:   return 'User marked deprecated';
        }
        return 'Unknown error ['.$error_code.']';
    }
}
?>
