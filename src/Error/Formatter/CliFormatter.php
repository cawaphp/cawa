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

class CliFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     *
     * @see http://davidbu.ch/mann/blog/2014-05-06/open-php-stack-trace-links-phpstorm.html
     * @see https://github.com/bcremer/phpstorm-url-handler-PKGBUILD
     * @see https://github.com/aik099/PhpStormProtocol
     */
    public function render(\Throwable $exception, int $index) : string
    {
        $stacks = $this->exceptionStackTrace($exception);

        $out = '';

        $out .= "\033[1;37m" . // white foreground
            "\033[41m #$index >>>> " . // red background
            $exception->getMessage() .
            "\033[0m\n";

        $out .= "\033[41mType:\033[0;31m " . // light red foreground
            get_class($exception) .
            "\033[0m\n";

        $out .= "\033[41mCode:\033[0;33m " . // brown foreground
            $exception->getCode() .
            "\033[0m\n";

        $out .= "\033[41mFile:\033[0;32m " . // green foreground
            $stacks[0]['file'] .
            "\033[0m";

        if (isset($stacks[0]['line'])) {
            $out .= ':' .
                "\033[0;35m" . // red foreground
                $stacks[0]['line'] .
                "\033[0m";
        }
        $out .= "\n\n";

        $stackTxt = '';
        foreach ($stacks as $index => $stack) {
            $stackTxt .= '  at ';
            if ($type = $this->getType($stack)) {
                $stackTxt .= "\033[0;36m" . // light gray foreground
                    $type . ' ' .
                    "\033[0m";
            }

            if (isset($stack['args'])) {
                $stackTxt .= "\033[0;37m" . // light gray foreground
                    $stack['args'] .
                    "\033[0m";
            }

            $stackTxt .= "\033[0;32m" . // green foreground
                $stack['file'] .
                "\033[0m";

            if (isset($stack['line'])) {
                $stackTxt .= ':' .
                    "\033[0;35m" . // red foreground
                    $stack['line'] .
                    "\033[0m";
            }

            $stackTxt .= "\n";
        }

        $stackTxt .= "\n";

        $data = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $stacks[0]['file'],
            'stack' => $stackTxt,
        ];

        if (isset($stacks[0]['line'])) {
            $data['line'] = $stacks[0]['line'];
        }

        return $out . $stackTxt;
    }
}
