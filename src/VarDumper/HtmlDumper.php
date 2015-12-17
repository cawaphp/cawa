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

class HtmlDumper extends \Symfony\Component\VarDumper\Dumper\HtmlDumper
{
    use Dumper;

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
        $header = preg_replace(
            "/if \\('sf-dump' != elt.parentNode.className\\) \\{.*\\}/mU",
            '',
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
