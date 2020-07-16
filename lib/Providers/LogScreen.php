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

/**
 * LogScreen class.
 *
 * @package    XLog
 * @subpackage Providers
 *
 * @see ILogOutputProvider for list of methods
 */

class LogScreen implements ILogOutputProvider
{
    use \Xeloses\XLog\Traits\ErrorCodeHelper;
    use \Xeloses\XLog\Traits\VarTypeHelper;

    /**
     * HTML template.
     *
     * @var array
     */
    protected $html = [
        'error'   => '<div style="display:block;margin:10px;padding:0;background:#333;color:#ccc;border:1px solid;border-color:{color};border-radius:10px;font-family:monospace;font-size:1.2em;">'.
                        '<div style="display:block;margin:0;padding:2px 10px;background:#222;color:{color-text};font-weight:bold;border-radius:10px 10px 0 0;border-bottom:1px solid;border-bottom-color:{color}">{error}</div>'.
                        '<span style="display:block;margin:5px 10px;padding:0;color:#999;font-size:.7em;">{timestamp}</span>'.
                        '<p style="display:block;margin:10px;padding:0;color:#fff;">{description}</p>'.
                        '<p style="display:block;margin:5px 10px;padding:0;">in <span style="display:inline-block;margin:0;padding:0 5px;border:1px solid #777;border-radius:5px;background: #222;">{file}:{line}</span></p>'.
                     '</div>'.PHP_EOL,
        'message' => '<div style="display:block;margin:10px;padding:0;background:#333;color:#ccc;border:1px solid {color};border-radius:10px;font-family:monospace;font-size:1.2em;">'.
                        '<div style="display:block;margin:0;padding:2px 10px;background:#222;color:{color-text};font-weight:bold;border-radius:10px 10px 0 0;border-bottom:1px solid;border-bottom-color:{color}">Custom output</div>'.
                        '<span style="display:block;margin:5px 10px;padding:0;color:#999;font-size:.7em;">{timestamp}</span>'.
                        '<p style="display:block;margin:10px;padding:0;color:#fff;">{message}</p>'.
                     '</div>'.PHP_EOL,
        'dump'    => '<div style="display:block;margin:10px;padding:0 0 5px 0;background:#333;color:#ccc;border:1px solid;border-color:{color};border-radius:10px;font-family:monospace;font-size:1.2em;">'.
                        '<div style="display:block;margin:0;padding:2px 10px;background:#222;color:{color-text};font-weight:bold;border-radius:10px 10px 0 0;border-bottom:1px solid;border-bottom-color:{color};cursor:pointer;" onclick="javascript:(this.nextSibling.style.display=(this.nextSibling.style.display==\'block\')?\'none\':\'block\')&&(this.firstElementChild.innerText=(this.firstElementChild.innerText==\'+\')?\'-\':\'+\');">'.
                            '<span style="display:inline-block;margin:0 7px 0 0;padding:0;width:11px;height:11px;line-height:10px;text-align:center;border:1px solid #f7f;border-radius:5px;background:#555;font-weight: normal;">-</span>Dump'.
                        '</div>'.
                        '<div class="log-dump" style="display:block;margin:0;padding:0;">'.
                            '<span style="display:block;margin:5px 10px;padding:0;color:#999;font-size:.7em;">{timestamp}</span>'.
                            '<p style="display:block;margin:10px;padding:0;color:#fff;">{type}</p>'.
                            '<p style="display:block;margin:5px 10px;padding:0;font-size:.8em;">{comment}</p>'.
                            '<pre style="display:block;margin:10px 10px 5px;padding:7px;border:1px inset #aaa;border-radius:10px;background:#222;color:#ddd;font-size:.85em;">{value}</pre>'.
                        '</div>'.
                     '</div>'.PHP_EOL,
    ];

    /**
     * Colors for output HTML/CSS.
     *
     * @var array
     */
    protected $colors = [
        // ERRORs:
        'error-color'     => '#f99',
        'error-text'      => '#f55',
        // WARNINGs:
        'warning-color'   => '#ffa',
        'warning-text'    => '#ff5',
        // NOTICEs:
        'notice-color'    => '#aaf',
        'notice-text'     => '#77f',
        // EXCEPTION:
        'exception-color' => '#f77',
        'exception-text'  => '#f55',
        // CUSTOM MESSAGEs:
        'message-color'   => '#555',
        'message-text'    => '#ddd',
        // DUMPs:
        'dump-color'      => '#faf',
        'dump-text'       => '#f7f',
    ];

    /**
     * Options.
     *
     * @var array
     */
    protected $options = [
        'timestamp_format' => 'c',
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
                if(strcasecmp($option,'colors') == 0 && is_array($value))
                {
                    $this->colors = array_merge($this->colors,$value);
                }
                elseif(array_key_exists($option,$this->options))
                {
                    $this->options[$option] = $value;
                }
            }
        }
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

        echo str_replace(
            [
                '{error}',
                '{description}',
                '{file}',
                '{line}',
                '{timestamp}',
                '{color}',
                '{color-text}',
            ],
            [
                '<span style="text-decoration:underline">'.$error_category.'</span>: '.$error_type,
                $this->safeStr($error_description),
                $file,
                $line,
                date($this->options['timestamp_format']),
                array_key_exists($type.'-color',$this->colors) ? $this->colors[$type.'-color'] : 'inherit',
                array_key_exists($type.'-text',$this->colors)  ? $this->colors[$type.'-text']  : 'inherit',
            ],
            $this->html['error']
        );

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
        echo str_replace(
            [
                '{error}',
                '{description}',
                '{file}',
                '{line}',
                '{timestamp}',
                '{color}',
                '{color-text}',
            ],
            [
                '<span style="text-decoration:underline">EXCEPTION</span>: '.ltrim(get_class($e),'\\'),
                $this->safeStr($e->getMessage()),
                $e->getFile(),
                $e->getLine(),
                date($this->options['timestamp_format']),
                array_key_exists('exception-color',$this->colors) ? $this->colors['exception-color'] : 'inherit',
                array_key_exists('exception-text',$this->colors)  ? $this->colors['exception-text']  : 'inherit',
            ],
            $this->html['error']
        );
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
        echo str_replace(
            [
                '{message}',
                '{timestamp}',
                '{color}',
                '{color-text}',
            ],
            [
                $this->safeStr($message),
                date($this->options['timestamp_format']),
                array_key_exists('message-color',$this->colors) ? $this->colors['message-color'] : 'inherit',
                array_key_exists('message-text',$this->colors)  ? $this->colors['message-text']  : 'inherit',
            ],
            $this->html['message']
        );
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
        echo str_replace(
            [
                '{type}',
                '{value}',
                '{comment}',
                '{timestamp}',
                '{color}',
                '{color-text}',
            ],
            [
                $this->getType($var),
                is_scalar($var) ? var_export($var,true) : print_r($var,true),
                $this->safeStr($comment),
                date($this->options['timestamp_format']),
                array_key_exists('dump-color',$this->colors) ? $this->colors['dump-color'] : 'inherit',
                array_key_exists('dump-text',$this->colors)  ? $this->colors['dump-text']  : 'inherit',
            ],
            $this->html['dump']
        );
    }

    /**
     * Sanitize string before output to browser.
     *
     * @internal
     *
     * @param mixed $str
     *
     * @return string
     */
    protected function safeStr($str): string
    {
        return nl2br(htmlentities($str,ENT_QUOTES|ENT_SUBSTITUTE|ENT_XHTML,'UTF-8'));
    }
}
?>
