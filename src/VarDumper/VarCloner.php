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

class VarCloner extends \Symfony\Component\VarDumper\Cloner\VarCloner
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $casters = null)
    {
        parent::__construct($casters);

        $this->addCasters([
            'DateTime' => 'Cawa\VarDumper\Caster\DateTime::cast',
            'DateInterval' => 'Cawa\VarDumper\Caster\DateInterval::cast',
            'Cawa\Orm\Collection' => 'Cawa\VarDumper\Caster\Collection::cast',
        ]);

        if ($casters = DI::config()->getIfExists('varDumper/casters')) {
            $this->addCasters($casters);
        }
    }
}
