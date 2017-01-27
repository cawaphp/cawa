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

namespace Cawa\Date;

use Carbon\Carbon;
use Cawa\Intl\TranslatorFactory;

class Translator extends \Cake\Chronos\Translator
{
    use TranslatorFactory;

    public function __construct()
    {
        $reflection = new \ReflectionClass(Carbon::class);

        $path = dirname($reflection->getFileName()) . '/Lang/';
        self::translator()->addFile($path . '/' . self::translator()->getLocale(), 'carbon', false);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return !is_null(self::translator()->trans(
            'carbon.' .
            $this->transformKey($key),
            [],
            false
        ));
    }
    /**
     * @param string $key
     *
     * @return string
     */
    private function transformKey(string $key) : string
    {
        return substr($key, -7) == '_plural' ? substr($key, 0, -7) : $key;
    }

    /**
     * @param array $vars
     *
     * @return array
     */
    private function transformVars(array $vars) : array
    {
        $return = [];
        foreach ($vars as $key => $value) {
            $return[':' . $key] = $value;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function singular($key, array $vars = [])
    {
        return self::translator()->transChoice(
            'carbon.' . $key,
            1,
            $this->transformVars($vars),
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function plural($key, $count, array $vars = [])
    {
        return self::translator()->transChoice(
            'carbon.' . $key,
            (int) $count,
            $this->transformVars($vars),
            false
        );
    }
}
