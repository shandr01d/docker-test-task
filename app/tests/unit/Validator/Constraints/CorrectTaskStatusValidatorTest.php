<?php

namespace App\Tests\Validator\Constraints;

use App\Enum\TaskStatus;
use App\Validator\Constraints\CorrectTaskStatus;
use App\Validator\Constraints\CorrectTaskStatusValidator;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CorrectTaskStatusValidatorTest extends Unit
{
    /**
     * @var \App\Tests\UnitTester
     */
    protected $tester;

    private $correctTaskStatusValidator;

    protected function _before()
    {
        $this->correctTaskStatusValidator = new CorrectTaskStatusValidator();
    }

    protected function _after()
    {
    }

    /**
     * @test
     * @dataProvider successStatusProvider
     */
    public function testCorrectValue($value)
    {
        $correctTaskStatusMock = $this->make(CorrectTaskStatus::class);
        /** @var ExecutionContextInterface $executionContextMock */
        $executionContextMock = $this->makeEmpty(ExecutionContextInterface::class, [
            'buildViolation' => Expected::never()
        ]);

        $this->correctTaskStatusValidator->initialize($executionContextMock);
        $this->correctTaskStatusValidator->validate($value, $correctTaskStatusMock);
    }

    public function successStatusProvider(): array
    {
        return [
            [TaskStatus::CREATED],
            [TaskStatus::COMPLETED],
            [TaskStatus::NOT_COMPLETED],
            [TaskStatus::CANCELLED],
        ];
    }

    /**
     * @test
     * @dataProvider failStatusProvider
     */
    public function testIncorrectValue($value)
    {
        $correctTaskStatusMock = $this->make(CorrectTaskStatus::class, [
            'message' => 'Not valid task status'
        ]);

        /** @var ConstraintViolationBuilderInterface $executionContextMock */
        $constraintViolationBuilderMock = $this->makeEmpty(ConstraintViolationBuilderInterface::class, [
            'addViolation' => Expected::once()
        ]);

        /** @var ExecutionContextInterface $executionContextMock */
        $executionContextMock = $this->make(ExecutionContext::class, [
            'buildViolation' => function() use ($constraintViolationBuilderMock) {
                return $constraintViolationBuilderMock;
            }
        ]);

        $this->correctTaskStatusValidator->initialize($executionContextMock);
        $this->correctTaskStatusValidator->validate($value, $correctTaskStatusMock);
    }

    public function failStatusProvider(): array
    {
        return [
            [0],
            [5],
            [-1],
            [0.2],
            [[]],
            ['string'],
            [''],
            [null],
        ];
    }

}