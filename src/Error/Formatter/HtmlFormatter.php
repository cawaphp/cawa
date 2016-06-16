<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Cawa\Error\Formatter;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class HtmlFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     *
     * /home/[-USER-]/.local/share/applications/pstorm.desktop
     * [Desktop Entry]
     * Version=1.0
     * Type=Application
     * Name=PhpStorm Url Handler
     * Icon=/opt/phpstorm/bin/webide.png
     * Exec=sh -c "echo %u | sed 's/phpstorm:\/\///' | xargs /usr/local/bin/pstorm"
     * Categories=Development;IDE;
     * Terminal=false
     *
     * /home/[-USER-]/.local/share/applications/mimeapps.list
     * [Default Applications]
     * x-scheme-handler/phpstorm=pstorm.desktop
     */
    public function render(\Throwable $exception) : string
    {
        $fileLinkFormat = ini_get('xdebug.file_link_format') ?: 'phpstorm://%f:%l' ;

        $stacks = $this->exceptionStackTrace($exception);
        $out = <<<EOF
            <script type="text/javascript">
                function showFullArgs(index)
                {
                    var args = document.getElementsByClassName("fullargs");
                    for (var key in args) {
                        if (args.hasOwnProperty(key)) {
                            if (args[key].className.indexOf("args-" + index + "-index") != -1) {
                                args[key].style.display = "block";
                            } else {
                                args[key].style.display = "none";
                            }
                        }
                    }

                }
            </script>
            <style type="text/css">
                .cawaException {
                    background-color:#fff;
                    z-index: 99999;
                    position: relative;
                    border-radius: 4px;
                    border:1px solid #ccc;
                    margin: 10px;
                    overflow: hidden;
                    word-wrap: break-word;
                    box-shadow: 0px 0px 10px grey;
                }

                .cawaException * {
                   font-family: 'Source Code Pro', Menlo, Monaco, Consolas, monospace;
                    color: #333;
                }

                .cawaException h1, .cawaException h2, .cawaException h3 {
                    background-color: FireBrick ;
                    color: white;
                    text-shadow: -1px 0 #333, 0 1px #333, 1px 0 #333, 0 -1px #333;
                    padding: 5 10px;
                    margin: 0;
                    font-size: 16px;
                }

                .cawaException h2 {
                    border-bottom:1px solid #ccc;
                    font-size: 13px;
                }

                .cawaException pre.extract {
                    background-color:#2b2b2b; 
                    color:#a1acb2;
                    font-size: 11px;
                    margin-top: 0;
                    padding: 5 10px;
                }

                .cawaException pre.extract span {
                    background-color:#323232;
                    color:#a1acb2;
                    display: block;
                }

                .cawaException ol {
                    margin: 10px;
                }

                .cawaException ol li {
                    padding: 0 5px;
                    font-size: 11px;
                }

                .cawaException .line, .cawaException .args {
                    color : DarkGrey;
                    text-decoration: none;
                }


                .cawaException abbr {
                    font-weight: bold;
                }

                .cawaException .fullargs {
                    display: none;
                    padding: 0 5px;
                }


            </style>

EOF;

        $out .= '<div class="cawaException">';
        $out .= '<h1>' . htmlspecialchars($exception->getMessage()) . "</h1>\n";

        $out .= '<h2>' . get_class($exception) . ' code(' . $exception->getCode() . ') in ' . $stacks[0]['file'];
        if (isset($stacks[0]['line'])) {
            $out .= ' line ' . $stacks[0]['line'];
        }
        $out .= "</h2>\n";

        if ($stacks[0]['file'] && isset($stacks[0]['line'])) {
            $files =  file($stacks[0]['file']);
            if (isset($files[$stacks[0]['line'] - 1 ])) {
                $begin = array_map('htmlspecialchars', array_slice($files, max(0, $stacks[0]['line'] - 3), 2));
                $current = htmlspecialchars(array_slice($files, $stacks[0]['line'] - 1, 1)[0]);
                $end = array_map('htmlspecialchars', array_slice($files, $stacks[0]['line'], 2));

                $extract = array_merge(
                    $begin,
                    ['<span class="current">' . $current . '</span>'],
                    $end
                );
                $out .= '<pre class="extract">' . implode('', $extract) .  "</pre>\n";
            }
        }

        $out .= "<ol>\n";

        foreach ($stacks as $index => $stack) {
            $out .= "  <li>\n";

            if ($type = $this->getType($stack)) {
                $out .= '    at <abbr>' . $type . '</abbr>' . "\n";
            }

            if (isset($stack['args'])) {
                $out .= '    <a href="javascript:showFullArgs(' .  $index . ')" class="args">' .
                    htmlspecialchars($stack['args']) . '</a>' . "\n";
            }

            $link = '';

            if ($stack['file'] != '[internal function]' && $stack['file'] != '{main}') {
                $link = ' href="' . str_replace(
                    ['%f', '%l'],
                    [$stack['file'], $stack['line'] ?? 1],
                    $fileLinkFormat
                ) . '"';
            }

            $out .= '    in <a class="file"' . $link . ' title="' . htmlentities($stack['file']) . '">' .
                basename($stack['file']) .
                '</a>' . "\n";

            if (isset($stack['line'])) {
                $out .= '    <span class="line"> line ' . $stack['line'] . '</span>';
            }

            // $out .= '    <span class="line" href="" title="' . htmlentities($stack["file"]) . '">\n' .
            $out .= "  </li>\n";
        }

        $out .= "</ol>\n";
        foreach ($stacks as $index => $stack) {
            if (isset($stack['fullargs'])) {
                foreach ($stack['fullargs'] as $argsIndex => $args) {
                    ob_start();

                    $cloner = new VarCloner();
                    $dumper = new HtmlDumper();
                    $dumper->dump($cloner->cloneVar($args));

                    $out .= '<div class="fullargs args-' . $index . '-index">' . ob_get_clean() . '</div>' .
                        "\n";
                }
            }
        }

        $out .= "</div>\n";

        return $out;
    }
}
