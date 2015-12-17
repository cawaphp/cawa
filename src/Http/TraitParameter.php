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

namespace Cawa\Http;

trait TraitParameter
{
    /**
     * @param mixed $variable
     * @param string $type
     * @param mixed $default
     *
     * @return mixed
     */
    private function validateType($variable, string $type = 'string', $default = null)
    {
        $options = ['flags' => FILTER_NULL_ON_FAILURE];

        if ($default) {
            $options['options']['default'] = $default;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                $variable = filter_var($variable, FILTER_VALIDATE_INT, $options);
                break;

            case 'float':
                $variable = filter_var($variable, FILTER_VALIDATE_FLOAT, $options);
                break;

            case 'bool':
            case 'boolean':
                $variable = filter_var($variable, FILTER_VALIDATE_BOOLEAN, $options);
                break;

            case 'string':
                $variable = trim(filter_var($variable, FILTER_SANITIZE_STRING, $options));
                break;

            default:
                throw new \LogicException(sprintf("Invalid filter type '%s'", $type));

        }

        return $variable;
    }
}
