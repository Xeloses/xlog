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
 * PhpDocLinkHelper trait.
 *
 * Helper functions working with PHP documentation links.
 *
 * @package    XLog
 * @subpackage Traits
 *
 * @method string formatLinks(string $text, string $template)
 */
trait PhpDocLinkHelper
{
    /**
     * Default template for links
     *
     * @var string
     */
    protected string $__html_link_template = '<a href="{url}" title="Open PHP documentation in new tab" target="_blank" rel="noopener noreferer">{text}</a>'.PHP_EOL;

    /**
     * PHP documentation
     *
     * @var string
     */
    protected string $__php_docs_url = 'https://www.php.net/manual/';

    /**
     * Apply format to all PHP documentation links.
     *
     * @internal
     *
     * @param string $text
     * @param string $template
     *
     * @return string
     */
    protected function formatLinks(string $text, string $template): string
    {
        $result = trim($text);

        if(!strlen($result) || ini_get('html_errors') != '1' || !ini_get('docref_root')) return $result;

        if(!$template) $template = $this->__html_link_template;

        if(preg_match_all('/\<a[\s]+href\=[\'\"]{1}(?<url>[\w\-\.\:\/]+)[\'\"]{1}\>(?<text>[^\<]+)\<\/a\>/i', $result, $links, PREG_SET_ORDER|PREG_UNMATCHED_AS_NULL))
        {
            $php_docref_root = ini_get('docref_root');

            foreach($links as $link)
            {
                //if(str_starts_with($link['url'], $php_docref_root))
                if(substr($link['url'], 0, strlen($php_docref_root)) == $php_docref_root)
                {
                    $url = str_replace($php_docref_root, $this->__php_docs_url, $link['url']);
                    $lnk = str_replace(
                        [
                            '{text}',
                            '{url}'
                        ],
                        [
                            $link['text'],
                            $this->safeStr($url)
                        ],
                        $template
                    );

                    $result = str_replace($link[0], $lnk, $result);
                }
            }

            $result = preg_replace('/[\s]+\]\: /', ']: ', $result);
        }

        return $result;
    }
}
?>
