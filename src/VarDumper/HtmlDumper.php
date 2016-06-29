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

        $header = parent::getDumpHeader();

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
    protected function dumpLine($depth, $endOfValue = false)
    {
        if (-1 === $this->lastDepth && isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $this->dumpPrefix = $this->originalDumpPrefix  . $this->prefix() . '<br />';
        }

        parent::dumpLine($depth, $endOfValue);
    }
}
