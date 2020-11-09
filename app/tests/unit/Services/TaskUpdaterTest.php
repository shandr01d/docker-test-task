<?php

namespace App\Tests\Services;

use App\Dto\TaskDto;
use App\Dto\TaskUpdateDto;
use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Repository\TaskListRepository;
use App\Services\TaskBuilder;
use App\Services\TaskUpdater;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Security;

class TaskUpdaterTest extends Unit
{
    const DEFAULT_OLD_TITLE = 'old title';
    const DEFAULT_NEW_TITLE = 'new title';
    const DEFAULT_OLD_DATE_STR = '2020-01-01';
    const DEFAULT_NEW_DATE_STR = '2020-01-02';

    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /**
     * @test
     * @dataProvider successUpdateStatusProvider
     */
    public function testTaskStatusIsUpdated($oldStatus, $newStatus)
    {
        $dueDate = new \DateTime(self::DEFAULT_OLD_DATE_STR);

        $task = $this->createTask(self::DEFAULT_OLD_TITLE, $oldStatus, $dueDate);
        $dto = $this->createTaskUpdateDto(self::DEFAULT_OLD_TITLE, $newStatus, $dueDate);

        $updatedTask = $this->getTaskUpdaterService()->update($task, $dto);
        $this->tester->assertEquals($updatedTask->getStatus(), $newStatus);
        $this->tester->assertEquals($updatedTask->getTitle(), self::DEFAULT_OLD_TITLE);
        $this->tester->assertEquals($updatedTask->getList()->getDueDate(), $dueDate);
        $this->tester->amConnectedToDatabase('test_db');
        $this->tester->seeNumRecords(1, 'task_list', [
            'owner_id' => 1,
            'due_date' => self::DEFAULT_OLD_DATE_STR,
        ]);
    }


    public function successUpdateStatusProvider(): array
    {
        return [
            [ TaskStatus::CREATED, TaskStatus::COMPLETED ],
            [ TaskStatus::CREATED, TaskStatus::NOT_COMPLETED ],
            [ TaskStatus::CREATED, TaskStatus::CANCELLED ],
            [ TaskStatus::COMPLETED, TaskStatus::CREATED ],
            [ TaskStatus::COMPLETED, TaskStatus::NOT_COMPLETED ],
            [ TaskStatus::COMPLETED, TaskStatus::CANCELLED ],
            [ TaskStatus::NOT_COMPLETED, TaskStatus::CREATED ],
            [ TaskStatus::NOT_COMPLETED, TaskStatus::COMPLETED ],
            [ TaskStatus::NOT_COMPLETED, TaskStatus::CANCELLED ],
            [ TaskStatus::CANCELLED, TaskStatus::CREATED ],
            [ TaskStatus::CANCELLED, TaskStatus::COMPLETED ],
            [ TaskStatus::CANCELLED, TaskStatus::NOT_COMPLETED ],
        ];
    }

    /**
     * @test
     * @dataProvider successUpdateTitleProvider
     */
    public function testTaskTitleIsUpdated($oldTitle, $newTitle)
    {
        $status = TaskStatus::CREATED;
        $dueDate = new \DateTime(self::DEFAULT_OLD_DATE_STR);

        $task = $this->createTask($oldTitle, $status, $dueDate);
        $dto = $this->createTaskUpdateDto($newTitle, $status, $dueDate);

        $updatedTask = $this->getTaskUpdaterService()->update($task, $dto);
        $this->tester->assertEquals($updatedTask->getStatus(), $status);
        $this->tester->assertEquals($updatedTask->getTitle(), $newTitle);
        $this->tester->assertEquals($updatedTask->getList()->getDueDate(), $dueDate);
        $this->tester->amConnectedToDatabase('test_db');
        $this->tester->seeNumRecords(1, 'task_list', [
            'owner_id' => 1,
            'due_date' => self::DEFAULT_OLD_DATE_STR,
        ]);
    }

    public function successUpdateTitleProvider(): array
    {
        return [
            [ 'old title', 'new title' ],
        ];
    }

    /**
     * @test
     * @dataProvider successDueDateProvider
     */
    public function testTaskDueDateIsUpdated($oldDueDate, $newDueDate)
    {
        $title = 'normal title';
        $status = TaskStatus::CREATED;

        $task = $this->createTask($title, $status, $oldDueDate);
        $dto = $this->createTaskUpdateDto($title, $status, $newDueDate);

        $updatedTask = $this->getTaskUpdaterService()->update($task, $dto);
        $this->tester->assertEquals($updatedTask->getStatus(), $status);
        $this->tester->assertEquals($updatedTask->getTitle(), $title);
        $this->tester->assertEquals($updatedTask->getList()->getDueDate(), $newDueDate);

        $this->tester->amConnectedToDatabase('test_db');
        $this->tester->seeNumRecords(1, 'task_list', [
            'owner_id' => 1,
            'due_date' => $oldDueDate->format('Y-m-d'),
        ]);
    }

    public function successDueDateProvider(): array
    {
        return [
            [ new \DateTime('2020-01-01'), new \DateTime('2020-02-01') ],
            [ new \DateTime('2020-02-01'), new \DateTime('2020-01-01') ],
        ];
    }

    /**
     * @test
     * @dataProvider ifTaskFieldProvider
     */
    public function testIfTaskFieldUpdate(
        $oldTitle, $oldStatus, $oldDueDate,
        $newTitle, $newStatus, $newDueDate,
        $compareTitle, $compareStatus, $compareDueDate
    ) {
        $data = $this->createTaskAndDto(
            $oldTitle, $newTitle,
            $oldStatus, $newStatus,
            $oldDueDate, $newDueDate
        );

        $updatedTask = $this->getTaskUpdaterService()->update($data['task'], $data['dto']);
        $this->tester->assertEquals($updatedTask->getStatus(), $compareStatus);
        $this->tester->assertEquals($updatedTask->getTitle(), $compareTitle);
        $this->tester->assertEquals($updatedTask->getList()->getDueDate(), $compareDueDate);
        $this->tester->amConnectedToDatabase('test_db');
        $this->tester->seeNumRecords(1, 'task_list', [
            'owner_id' => 1,
            'due_date' => $oldDueDate->format('Y-m-d') ?? $newDueDate->format('Y-m-d'),
        ]);
    }

    public function ifTaskFieldProvider(): array
    {
        return [
            [
                self::DEFAULT_OLD_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_OLD_DATE_STR),
                null, TaskStatus::COMPLETED, new \DateTime(self::DEFAULT_NEW_DATE_STR),
                self::DEFAULT_OLD_TITLE, TaskStatus::COMPLETED, new \DateTime(self::DEFAULT_NEW_DATE_STR)
            ],
            [
                self::DEFAULT_OLD_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_OLD_DATE_STR),
                self::DEFAULT_NEW_TITLE, null, new \DateTime(self::DEFAULT_NEW_DATE_STR),
                self::DEFAULT_NEW_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_NEW_DATE_STR)
            ],
            [
                self::DEFAULT_OLD_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_OLD_DATE_STR),
                self::DEFAULT_NEW_TITLE, TaskStatus::COMPLETED, null,
                self::DEFAULT_NEW_TITLE, TaskStatus::COMPLETED, new \DateTime(self::DEFAULT_OLD_DATE_STR)
            ],
            [
                self::DEFAULT_OLD_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_OLD_DATE_STR),
                self::DEFAULT_NEW_TITLE, null, null,
                self::DEFAULT_NEW_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_OLD_DATE_STR)
            ],
            [
                self::DEFAULT_OLD_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_OLD_DATE_STR),
                null, TaskStatus::COMPLETED, null,
                self::DEFAULT_OLD_TITLE, TaskStatus::COMPLETED, new \DateTime(self::DEFAULT_OLD_DATE_STR)
            ],
            [
                self::DEFAULT_OLD_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_OLD_DATE_STR),
                self::DEFAULT_NEW_TITLE, TaskStatus::COMPLETED, new \DateTime(self::DEFAULT_NEW_DATE_STR),
                self::DEFAULT_NEW_TITLE, TaskStatus::COMPLETED, new \DateTime(self::DEFAULT_NEW_DATE_STR)
            ],
            [
                self::DEFAULT_OLD_TITLE, TaskStatus::COMPLETED, new \DateTime(self::DEFAULT_OLD_DATE_STR),
                self::DEFAULT_NEW_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_NEW_DATE_STR),
                self::DEFAULT_NEW_TITLE, TaskStatus::CREATED, new \DateTime(self::DEFAULT_NEW_DATE_STR)
            ],
            [
                self::DEFAULT_OLD_TITLE, TaskStatus::CREATED, new \DateTime('2020-02-01'),
                self::DEFAULT_NEW_TITLE, TaskStatus::CANCELLED, new \DateTime('2020-01-01'),
                self::DEFAULT_NEW_TITLE, TaskStatus::CANCELLED, new \DateTime('2020-01-01')
            ],
        ];
    }

    /**
     * @test
     * @dataProvider ifTaskDtoIsInvalidProvider
     */
    public function testIfTaskDtoIsInvalid($title, $dueDate, $status) {
        $task = $this->createTask(
            self::DEFAULT_OLD_TITLE,
            TaskStatus::CREATED,
            new \DateTime(self::DEFAULT_OLD_DATE_STR)
        );

        $dto = new TaskUpdateDto();
        $dto->title = $title;
        $dto->dueDate = $dueDate;
        $dto->status = $status;

        $this->tester->expectThrowable(\Throwable::class, function() use ($task, $dto) {
            $this->getTaskUpdaterService()->update($task, $dto);
        });
    }

    public function ifTaskDtoIsInvalidProvider(): array
    {
        return [
            ['', '', ''],
            ['normal title', '', ''],
            ['normal title', 'wrong date', ''],
            [null, new \DateTime('2020-01-01'), ''],
        ];
    }

    private function getTaskBuilderService(int $currentUserId = 1)
    {
        /** @var Container $container */
        $container = $this->getModule('Symfony')->_getContainer();
        $taskListRepository = $container->get(TaskListRepository::class);
        $security = $this->make(Security::class,  [
            'getUser' => function () use ($currentUserId) {
                return $this->tester->getUser($currentUserId);
            }
        ]);

        return new TaskBuilder($taskListRepository, $security);
    }

    private function getTaskUpdaterService(int $currentUserId = 1)
    {
        /** @var Container $container */
        $container = $this->getModule('Symfony')->_getContainer();
        $taskListRepository = $container->get(TaskListRepository::class);
        $security = $this->make(Security::class,  [
            'getUser' => function () use ($currentUserId) {
                return $this->tester->getUser($currentUserId);
            }
        ]);

        return new TaskUpdater($taskListRepository, $security);
    }

    private function createTask(string $title, int $status, \DateTime $dueDate): Task
    {
        /** @var EntityManager $em */
        $em = $this->getModule('Doctrine2')->em;

        $taskDto = $this->createTaskDto($title, $status, $dueDate);
        $task = $this->getTaskBuilderService()->build($taskDto);

        $em->persist($task);
        $em->flush();

        return $task;
    }

    private function createTaskUpdateDto(string $title = null, int $status = null, \DateTime $dueDate = null): TaskUpdateDto
    {
        $dto = new TaskUpdateDto();
        $dto->title = $title;
        $dto->dueDate = $dueDate;
        $dto->status = $status;
        return $dto;
    }

    private function createTaskDto(string $title = null, int $status = null, \DateTime $dueDate = null): TaskDto
    {
        $dto = new TaskDto();
        $dto->title = $title;
        $dto->dueDate = $dueDate;
        $dto->status = $status;
        return $dto;
    }

    private function createTaskAndDto(
        $oldTitle = self::DEFAULT_OLD_TITLE,
        $newTitle = self::DEFAULT_NEW_TITLE,
        $oldStatus = TaskStatus::CREATED,
        $newStatus = TaskStatus::COMPLETED,
        $oldDueDate = null,
        $newDueDate = null
    ){
        $task = $this->createTask($oldTitle, $oldStatus, $oldDueDate);
        $dto = $this->createTaskUpdateDto($newTitle, $newStatus, $newDueDate);
        return [
            'task' => $task,
            'dto' => $dto
        ];
    }
}