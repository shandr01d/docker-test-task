<?php

namespace App\Dto;

use DateTime;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class TaskDto {

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $title;

    /**
     * @var DateTime
     * @ApiProperty(attributes={
     *     "openapi_context"={"format"="date"}
     * })
     * @Assert\NotBlank()
     */
    public $dueDate;

    /**
     * @var integer
     */
    public $status;
}