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

namespace Cawa\App;

use Cawa\Console\Application;

class ConsoleApp extends AbstractApp
{
    /**
     * @var
     */
    private static $exitCode;

    /**
     * @var Application
     */
    private $application;

    /**
     * @return Application
     */
    public static function application() : Application
    {
        return self::$instance->application;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->application = new Application();
        $this->application->setAutoExit(false);
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        self::$exitCode = $this->application->run();
    }

    /**
     * {@inheritdoc}
     */
    public static function end()
    {
        parent::end();

        if (self::$exitCode > 255) {
            self::$exitCode = 255;
        }

        exit(self::$exitCode);
    }
}
