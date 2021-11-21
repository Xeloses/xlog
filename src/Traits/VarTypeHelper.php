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
 * VarTypeHelper trait.
 *
 * Helper functions working with variable type.
 *
 * @package    XLog
 * @subpackage Traits
 *
 * @method string getType($var)
 */
trait VarTypeHelper
{
    /**
     * Get type of variable.
     *
     * @internal
     *
     * @param mixed $var
     *
     * @return string
     */
    protected function getType($var): string
    {
        if (is_object($var))                return 'Object of class: '.ltrim(get_class($var),'\\');
        if (is_callable($var,false,$fname)) return '(callable) '.(strpos($fname,'::') ? 'Method' : 'Function').': '.$fname;
        if (is_array($var))                 return 'Array['.count($var).']';
        if (is_resource($var))              return 'Resource: '.get_resource_type($var);
        if (is_null($var))                  return 'NULL';
        if (is_bool($var))                  return 'Boolean';
        if (is_float($var))                 return 'Float';
        if (is_int($var))                   return 'Integer';
        if (is_numeric($var))               return 'Numeric';
        if (is_string($var))                return 'string';

        return '(unknown type)';
    }
}
?>
