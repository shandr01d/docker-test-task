<?php

namespace App\Validator\Constraints;

use App\Enum\TaskStatus;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
final class CorrectTaskStatusValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if(!in_array($value, TaskStatus::getAvailableStatuses())){
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}