<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CorrectTaskStatus extends Constraint
{
    public $message = 'Not valid task status';
}