<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\VarDumper;

use Cawa\Core\DI;

class HtmlDumper extends \Symfony\Component\VarDumper\Dumper\HtmlDumper
{
    use DumperTrait;

    /**
     * @var string
     */
    protected $originalDumpPrefix;

    /**
     * {@inheritdoc}
     */
    public function __construct($output = null, $charset = null)
    {
        $this->styles['default'] = str_replace(
            'font:12px Menlo',
            "font:11px 'Source Code Pro', Menlo",
            $this->styles['default']
        );

        $this->styles['ctrl'] = 'color:#999';

        $this->dumpPrefix .= '<a class="sf-close sf-dump-index">×</a>';

        $header = $this->getDumpHeader();

        // max depth function
        $header = str_replace(
            'function toggle(a, recursive) {',
            "function getLevel(elt) {\n" .
            "    var exit = false, level = 0, element = elt;\n" .
            "\n" .
            "    if (element.parentNode.className == 'sf-dump') {\n" .
            "        return 0;\n" .
            "    }\n" .
            "\n" .
            "    while(exit == false) {\n" .
            "        element = element.parentNode;\n" .
            "        level++;\n" .
            "        exit = !element.parentNode || element.parentNode.className == 'sf-dump';\n" .
            "    }\n" .

            "    return level;\n" .
            "}\n" .
            "\n" .
            'function toggle(a, recursive) {',
            $header
        );

        // close button function & copy paste enhancement
        $header = str_replace(
            "addEventListener(root, 'mouseover', function (e) {",
            "var container = root;\n" .
            "addEventListener(container.querySelectorAll('.sf-close')[0], 'click', function(e) {\n" .
            "    e.target.parentNode.parentNode.removeChild(e.target.parentNode);\n" .
            "});\n" .
            "\n" .
            "addEventListener(document, 'selectionchange', function(e) {\n" .
            "    if (\n" .
            "        window.getSelection &&\n" .
            "        window.getSelection().toString() &&\n" .
            "        window.getSelection().anchorNode.parentNode.className.indexOf('sf-dump') >= 0\n" .
            "    ) {\n" .
            "        var ctrl = container.querySelectorAll('.sf-dump-ctrl');\n" .
            "        ctrl.forEach(function(node)\n" .
            "        {\n" .
            "           node.style.display = 'none';\n" .
            "        });\n" .
            "    }\n" .
            "}, false);\n" .
            "\n" .
            "addEventListener(root, 'mouseover', function (e) {",
            $header
        );

        // close button style
        $header = str_replace(
            'pre.sf-dump span {',
            "pre.sf-dump a.sf-close {\n" .
            "    font-size:22px;\n" .
            "    position:absolute;\n" .
            "    right: 10px;\n" .
            "};\n" .
            "\n" .
            'pre.sf-dump span {',
            $header
        );

        $expandLevel = DI::config()->getIfExists('varDumper/expandLevel');
        if (is_null($expandLevel)) {
            $expandLevel = 4;
        }

        $header = preg_replace(
            "/if \\('sf-dump' != elt.parentNode.className\\) \\{.*\\}/mU",
            $expandLevel == false ? '' : 'if (getLevel(elt) > ' . $expandLevel . ') {toggle(a);}',
            $header,
            -1,
            $count
        );
        $this->setDumpHeader($header);

        $this->originalDumpPrefix = $this->dumpPrefix;

        parent::__construct($output, $charset);
    }
    /**
     * {@inheritdoc}
     */
    protected function style($style, $value, $attr = [])
    {
        $return = parent::style($style, $value, $attr);

        if ($style == 'str') {
            $map = array_values(static::$controlCharsMap);
            foreach ($map as $current) {
                $return = str_replace($current, '<span class="sf-dump-ctrl">' . $current . '</span>', $return);
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    protected function dumpLine($depth, $endOfValue = false)
    {
        if (-1 === $this->lastDepth && isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $this->dumpPrefix = $this->originalDumpPrefix . $this->prefix() . '<br />';
        }

        parent::dumpLine($depth, $endOfValue);
    }
}
