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

namespace Xeloses\XLog\Interfaces;

/**
 * ILogOutputProvider interface.
 *
 * @package    XLog
 * @subpackage Interfaces
 */

interface ILogOutputProvider
{
    public function __construct(array $options = []);

    public function logError(int $error_code, string $error_description, string $file, int $line): bool; // return true if log entry was successfull added
    public function logException(\Throwable $e): void;
    public function logMessage(string $message): void; // add custom message to log
    public function logValue($name, string $comment = ''): void; // dump value to log (works only in debug mode)
}
?>
