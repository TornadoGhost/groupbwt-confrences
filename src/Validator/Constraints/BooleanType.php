<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class BooleanType extends Constraint
{
    public string $message = 'This value is not a valid boolean.';
}
