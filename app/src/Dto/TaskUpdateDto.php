<?php

namespace App\Dto;

use DateTime;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class TaskUpdateDto {

    /**
     * @var string
     */
    public $title;

    /**
     * @var DateTime
     * @ApiProperty(attributes={
     *     "openapi_context"={"format"="date"}
     * })
     */
    public $dueDate;

    /**
     * @var integer
     */
    public $status;
}