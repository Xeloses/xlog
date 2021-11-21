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

namespace Xeloses\XLog\Providers;

use Xeloses\XLog\Interfaces\ILogOutputProvider;
use Xeloses\XLog\Exceptions\LogFileException;

/**
 * LogFile class.
 *
 * @package    XLog
 * @subpackage Providers
 *
 * @WARNING Directories for log files are created with 0777 access mode.
 *          Log files are created with 0775 access mode.
 *          Path and/or filename should be added to .htaccess to close it from public access!
 *
 * @see ILogOutputProvider for list of methods
 */

class LogFile implements ILogOutputProvider
{
    use \Xeloses\XLog\Traits\PhpDocLinkHelper;
    use \Xeloses\XLog\Traits\ErrorCodeHelper;
    use \Xeloses\XLog\Traits\VarTypeHelper;

    /**
     * Templates.
     *
     * @var array
     */
    protected $templates = [
        'error'   => '[{timestamp}] <{error}> {description} (in {file}:{line})',
        'message' => '[{timestamp}] {message}',
        'dump'    => '[{timestamp}] DUMP <{type}>: {comment}'.PHP_EOL.'{value}',

        'link'    => '{text}', // remove links; to print links use template like '{text} ({url})'
    ];

    /**
     * Options.
     *
     * @var array
     */
    protected $options = [
        'timestamp_format' => 'c',
        'filename' => './logs/{date}.log',
        'filesize' => 1024*1024, // 1 Mb
        'overwrite' => true,
    ];

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if(count($options)){
            foreach($options as $option => $value)
            {
                if(array_key_exists($option,$this->options))
                {
                    $this->options[$option] = $value;
                }
            }
        }

        $this->createLogFile();
    }

    /**
     * Log errors/warnings/notices.
     *
     * @param int    $code
     * @param string $description
     * @param string $file
     * @param int    $line
     *
     * @return bool
     */
    public function logError(int $error_code, string $error_description, string $file, int $line): bool
    {
        $error_category = $this->getErrorCategory($error_code);
        $error_type = $this->getErrorType($error_code);

        $type = strtolower($error_category);
        $error_description = $this->formatLinks($error_description, $this->templates['link']);

        $log = str_replace(
            [
                '{error}',
                '{description}',
                '{file}',
                '{line}',
                '{timestamp}'
            ],
            [
                $error_category.': '.$error_type,
                $error_description,
                $file,
                $line,
                date($this->options['timestamp_format'])
            ],
            $this->templates['error']
        );

        $this->writeLog($log);

        return true;
    }

    /**
     * Log exceptions.
     *
     * @param Throwable $e
     *
     * @return void
     */
    public function logException(\Throwable $e): void
    {
        $log = str_replace(
            [
                '{error}',
                '{description}',
                '{file}',
                '{line}',
                '{timestamp}',
            ],
            [
                'EXCEPTION: '.ltrim(get_class($e),'\\'),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                date($this->options['timestamp_format']),
            ],
            $this->templates['error']
        );

        $this->writeLog($log);
    }

    /**
     * Add custom message to log.
     *
     * @param string $message
     *
     * @return void
     */
    public function logMessage(string $message): void
    {
        $log = str_replace(
            [
                '{message}',
                '{timestamp}',
            ],
            [
                $message,
                date($this->options['timestamp_format']),
            ],
            $this->templates['message']
        );

        $this->writeLog($log);
    }

    /**
     * Dump variable to log.
     *
     * @param mixed  $value
     * @param string $comment (optional)
     *
     * @return void
     */
    public function logValue($var, string $comment = ''): void
    {
        $log = str_replace(
            [
                '{type}',
                '{value}',
                '{comment}',
                '{timestamp}'
            ],
            [
                $this->getType($var),
                print_r($var,true),
                $comment,
                date($this->options['timestamp_format'])
            ],
            $this->templates['dump']
        );

        $this->writeLog($log);
    }

    /**
     * Add entry to log file.
     *
     * @internal
     *
     * @param string $log
     *
     * @return void
     *
     * @throws LogFileException
     */
    protected function writeLog(string $log)
    {
        if(filesize($this->options['filename']) >= $this->options['filesize'])
        {
            $this->createLogFile();
        }

        if(!file_put_contents($this->options['filename'],$log.PHP_EOL,FILE_APPEND))
        {
            throw new LogFileException('Error attempt to write to log file "'.$fname.'".');
        }
    }

    /**
     * Create new log file.
     *
     * @internal
     *
     * @return void
     *
     * @throws LogFileException
     */
    protected function createLogFile(): void
    {
        $this->parseFilename();

        if(is_file($this->options['filename']))
        {
            // log file exists
            if(filesize($this->options['filename']) >= $this->options['filesize'])
            {
                // log file exceed max size
                if($this->options['overwrite'])
                {
                    // remove old log file:
                    if(!unlink($this->options['filename']))
                    {
                        throw new LogFileException('Could not delete old log file "'.$fname.'".');
                    }
                }
                else
                {
                    // generate new file name:
                    $f = pathinfo($this->options['filename']);
                    $this->options['filename'] = $f['dirname'].$f['filename'].'.'.date('Y-m-d_H-i-s').'.'.$f['extension'];
                }
            }
            else
            {
                if(!is_writable($this->options['filename']))
                {
                    throw new LogFileException('Log file "'.$this->options['filename'].'" is not available for write.');
                }
                return;
            }
        }

        if(is_dir($this->options['filename']))
        {
            // "filename" is directory
            throw new \InvalidArgumentException('Bad file name "'.$this->options['filename'].'".');
        }

        $path = rtrim(pathinfo($this->options['filename'],PATHINFO_DIRNAME),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if(!is_dir($path))
        {
            // create directory for log files:
            if(!mkdir($path,0777,true))
            {
                throw new LogFileException('Could not create directory "'.$path.'".');
            }
        }

        // create new empty file:
        $f = fopen($this->options['filename'],'a+',false);
        if($f === false)
        {
            throw new LogFileException('Could not create file "'.$this->options['filename'].'".');
        }
        fclose($f);

        // set access mode:
        if(!chmod($this->options['filename'],0775))
        {
            throw new LogFileException('Could not change access mode on file "'.$this->options['filename'].'".');
        }
    }

    /**
     * Parse file name for placeholders and return processed file name.
     *
     * @internal
     *
     * @return void
     */
    protected function parseFilename(): void
    {
        $filename = $this->options['filename'];

        // Date placeholders:
        if(strpos($filename,'{y') !== false || strpos($filename,'{m') !== false || strpos($filename,'{d') !== false)
        {
            $date = new \DateTime();
            $filename = str_replace(
                [
                    '{date}',  // Date, format: yyyy-mm-dd
                    '{year}',  // Year, 4 digits
                    '{Y}',     // Year, 4 digits
                    '{yyyy}',  // Year, 4 digits
                    '{yy}',    // Year, 2 digits
                    '{Month}', // Month name, full  (January, Febrary, ...)
                    '{month}', // Month name, short (Jan, Feb, ...)
                    '{M}',     // Month name, short (Jan, Feb, ...)
                    '{mm}',    // Month, 2 digits
                    '{m}',     // Month, 1 or 2 digits
                    '{Day}',   // Day name, full (Sunday, Monday, ...)
                    '{day}',   // Day name, short (Sun, Mon, ...)
                    '{D}',     // Day name, short (Sun, Mon, ...)
                    '{dd}',    // Day, 2 digits
                    '{d}',     // Day, 1 or 2 digits
                ],
                [
                    $date->format('Y-m-d'), // <- '{date}'
                    $date->format('Y'),     // <- '{year}'
                    $date->format('Y'),     // <- '{Y}'
                    $date->format('Y'),     // <- '{yyyy}'
                    $date->format('y'),     // <- '{yy}'
                    $date->format('F'),     // <- '{Month}'
                    $date->format('M'),     // <- '{month}'
                    $date->format('M'),     // <- '{M}'
                    $date->format('m'),     // <- '{mm}'
                    $date->format('n'),     // <- '{m}'
                    $date->format('l'),     // <- '{Day}'
                    $date->format('D'),     // <- '{day}'
                    $date->format('D'),     // <- '{D}'
                    $date->format('d'),     // <- '{dd}'
                    $date->format('j'),     // <- '{d}'
                ],
                $filename
            );
        }

        // Counter placeholder:
        if(strpos($filename,'{n}'))
        {
            if($this->options['overwrite'])
            {
                $filename = str_replace('..','.',str_replace('{n}','',$filename));
            }
            else
            {
                $i = 0;
                $fname = '';
                do{
                    $i++;
                    $fname = str_replace('{n}',$i,$filename);
                }while(is_file($fname) && filesize($fname) >= $this->options['filesize']);

                $filename = $fname;
            }
        }

        $this->options['filename'] = $filename;
    }
}
?>
