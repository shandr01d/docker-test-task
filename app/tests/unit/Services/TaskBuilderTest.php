<?php

namespace App\Tests\Services;

use App\Dto\TaskDto;
use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Repository\TaskListRepository;
use App\Services\TaskBuilder;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Security;

class TaskBuilderTest extends Unit
{
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

    /**
     * @test
     * @dataProvider successTasksProvider
     */
    public function testTaskIsCreated($title, $dueDate, $status, $message)
    {
        $dto = new TaskDto();
        $dto->title = $title;
        $dto->dueDate = $dueDate;
        $dto->status = $status;

        $task = $this->getTaskBuilderService()->build($dto);
        $this->tester->assertInstanceOf(Task::class, $task, $message);
        $this->tester->assertEquals(TaskStatus::CREATED, $task->getStatus(), $message);
        $this->tester->assertEquals($dto->title, $task->getTitle(), $message);
        $this->tester->assertEquals($dto->dueDate, $task->getList()->getDueDate(), $message);

    }

    /**
     * @test
     * @dataProvider failedTasksProvider
     */
    public function testFailToCreate($title, $dueDate, $status)
    {
        $dto = new TaskDto();
        $dto->title = $title;
        $dto->dueDate = $dueDate;
        $dto->status = $status;

        $this->tester->expectThrowable(\Throwable::class, function() use ($dto) {
            $this->getTaskBuilderService()->build($dto);
        });
    }

    /**
     * @test
     */
    public function testWrongUser()
    {
        $dto = new TaskDto();
        $dto->title = 'normal title';
        $dto->dueDate = new \DateTime('2020-01-01');
        $dto->status = TaskStatus::CREATED;

        $this->tester->expectThrowable(\Throwable::class, function() use ($dto) {
            $this->getTaskBuilderService(0)->build($dto);
        });
    }

    public function successTasksProvider(): array
    {
        return [
            ['normal title', new \DateTime('2020-01-01'), TaskStatus::CREATED, 'Task is returned after build'],
            ['normal title', new \DateTime('2020-01-01'), TaskStatus::COMPLETED, 'Task is returned after build'],
            ['normal title', new \DateTime('2020-01-01'), TaskStatus::NOT_COMPLETED, 'Task is returned after build'],
            ['normal title', new \DateTime('2020-01-01'), TaskStatus::CANCELLED, 'Task is returned after build'],
            ['normal title', new \DateTime('2020-01-01'), null, 'Task is returned after build'],
            ['normal title', new \DateTime('2020-01-01'), false, 'Task is returned after build'],
        ];
    }

    public function failedTasksProvider(): array
    {
        return [
            ['', '', ''],
            ['normal title', '', ''],
            ['normal title', 'wrong date', ''],
            [null, new \DateTime('2020-01-01'), ''],
        ];
    }
}