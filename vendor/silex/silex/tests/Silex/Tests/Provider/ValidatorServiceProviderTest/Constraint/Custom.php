<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @author Alex Kalyvitis <alex.kalyvitis@gmail.com>
 */
class Custom extends Constraint
{
    public $message = 'This field must be ...';
    public $table;
    public $field;

    public function validatedBy()
    {
        return 'test.custom.validator';
    }
}
