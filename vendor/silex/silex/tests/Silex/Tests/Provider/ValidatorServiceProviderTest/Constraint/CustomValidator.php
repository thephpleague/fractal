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
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Alex Kalyvitis <alex.kalyvitis@gmail.com>
 */
class CustomValidator extends ConstraintValidator
{
    public function isValid($value, Constraint $constraint)
    {
        // Validate...
        return true;
    }

    public function validate($value, Constraint $constraint)
    {
        return $this->isValid($value, $constraint);
    }
}
