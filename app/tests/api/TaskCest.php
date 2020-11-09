<?php namespace App\Tests;
use App\Entity\Task;
use App\Entity\TaskList;
use App\Tests\ApiTester;
use Codeception\Util\HttpCode;

class TaskCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Accept', '*/*');
    }

    public function testGetCollection(ApiTester $I): void
    {
        $I->login();
        $I->sendGet('api/tasks/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            "@context" => "string",
            "@id" => "string",
            "@type" => "string",
            "hydra:member" => "array",
            "hydra:totalItems" => "integer",
            "hydra:search" => "array"
        ]);
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].[\'@id\']');
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].[\'@type\']');
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].id');
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].title');
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].status');
    }

    public function testUserCannotGetAnotherUserCollection(ApiTester $I): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskList = $user->getTaskLists()->first();
        $tasks = $taskList->getTasks();

        $I->login($I::SECOND_USER_EMAIL, $I::SECOND_USER_PASS);
        $I->sendGet('api/tasks');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            "@context" => "string",
            "@id" => "string",
            "@type" => "string",
            "hydra:member" => "array",
            "hydra:totalItems" => "integer",
            "hydra:search" => "array"
        ]);
        foreach ($tasks as $task) {
            $I->dontSeeResponseContainsJson(["@id" => "/api/tasks/".$task->getId()]);
        }
    }

    public function testGetItem(ApiTester $I): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskList = $user->getTaskLists()->first();
        $task = $taskList->getTasks()->first();

        $I->login($I::FIRST_USER_EMAIL, $I::FIRST_USER_PASS);
        $I->sendGet('api/tasks/'.$task->getId());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            "@context" => "string",
            "@id" => "string",
            "@type" => "string",
            "id" => "integer",
            "title" => "string",
            "status" => "integer",
            "list" => "array"
        ]);
        $I->seeResponseJsonMatchesJsonPath('$.list.[\'@id\']');
        $I->seeResponseJsonMatchesJsonPath('$.list.[\'@type\']');
        $I->seeResponseJsonMatchesJsonPath('$.list.dueDate');
    }

    public function testUserCannotGetAnotherUserItem(ApiTester $I): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskList = $user->getTaskLists()->first();
        $task = $taskList->getTasks()->first();

        $I->login($I::SECOND_USER_EMAIL, $I::SECOND_USER_PASS);
        $I->sendGet('api/tasks/'.$task->getId());
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
    }

    public function testCreateTaskItem(ApiTester $I): void
    {
        $I->login();
        $I->sendPost('api/tasks', [
            "title" => "task title",
            "dueDate" => "2020-11-05"
        ]);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            "@context" => "string",
            "@id" => "string",
            "@type" => "string",
            "id" => "integer",
            "title" => "string",
            "status" => "integer"
        ]);
        $I->seeResponseContainsJson([
            "title" => "task title",
            "status" => 1
        ]);
        $id = $I->grabDataFromResponseByJsonPath('$.id');
        $I->seeInRepository(Task::class, ['id' => $id[0]]);
    }

    /**
     * @dataProvider failCreateTaskProvider
     */
    public function testFailCreateTaskItem(ApiTester $I, \Codeception\Example $example): void
    {
        $I->login();
        $I->sendPost('api/tasks', $example['data']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
    }

    /**
     * @return array
     */
    protected function failCreateTaskProvider()
    {
        return [
            [ 'data' => ["title" => "task title"]],
            [ 'data' => ["title" => null]],
            [ 'data' => ["title" => 1111]],
            [ 'data' => ["dueDate" => "2020-11-05"]],
            [ 'data' => ["dueDate" => "2020-25-15"]],
            [ 'data' => ["dueDate" => null]],
            [ 'data' => ["title" => "task title", "dueDate" => null]],
            [ 'data' => ["title" => "task title", "dueDate" => "2020-25-05"]],
            [ 'data' => ["title" => null, "dueDate" => "2020-11-05"]],
            [ 'data' => ["title" => 1111, "dueDate" => "2020-11-05"]],
            [ 'data' => []],
        ];
    }

    /**
     * @dataProvider taskUpdateItemDataProvider
     */
    public function testUpdateTaskItem(ApiTester $I, \Codeception\Example $example): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskList = $user->getTaskLists()->first();
        $task = $taskList->getTasks()->first();

        $I->login();
        $I->haveHttpHeader('Content-Type', 'application/merge-patch+json');
        $I->sendPatch('api/tasks/'.$task->getId(), $example['data']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            "@context" => "string",
            "@id" => "string",
            "@type" => "string",
            "id" => "integer",
            "title" => "string",
            "status" => "integer",
            "list" => "array"
        ]);
        $I->seeResponseJsonMatchesJsonPath('$.list.[\'@id\']');
        $I->seeResponseJsonMatchesJsonPath('$.list.[\'@type\']');
        $I->seeResponseJsonMatchesJsonPath('$.list.dueDate');
        $I->seeResponseContainsJson($example['result']);
    }

    /**
     * @return array
     */
    protected function taskUpdateItemDataProvider()
    {
        return [
            [
                'data' => ['status' => 1],
                'result' => ['status' => 1]
            ],
            [
                'data' => ['status' => 2],
                'result' => ['status' => 2]
            ],
            [
                'data' => ['status' => 3],
                'result' => ['status' => 3]
            ],
            [
                'data' => ['status' => 4],
                'result' => ['status' => 4]
            ],
            [
                'data' => ['title' => 'new title'],
                'result' => ['title' => 'new title']
            ],
            [
                'data' => ['dueDate' => '2020-11-06'],
                'result' => [ 'list' => [ 'dueDate' => '2020-11-06' ] ]
            ],
            [
                'data' => ['title' => 'new title', 'dueDate' => '2020-11-06'],
                'result' => ['title' => 'new title', 'list' => [ 'dueDate' => '2020-11-06' ]]
            ],
            [
                'data' => ['title' => 'new title', 'status' => 1],
                'result' => ['title' => 'new title', 'status' => 1]
            ],
            [
                'data' => ['title' => 'new title', 'status' => 2],
                'result' => ['title' => 'new title', 'status' => 2]
            ],
            [
                'data' => ['title' => 'new title', 'status' => 3],
                'result' => ['title' => 'new title', 'status' => 3]
            ],
            [
                'data' => ['title' => 'new title', 'status' => 4],
                'result' => ['title' => 'new title', 'status' => 4]
            ],
            [
                'data' => ['title' => 'new title', 'status' => 1, 'dueDate' => '2020-11-06'],
                'result' => ['title' => 'new title', 'status' => 1, 'list' => [ 'dueDate' => '2020-11-06' ] ]
            ],
            [
                'data' => ['title' => 'new title', 'status' => 2, 'dueDate' => '2020-11-06'],
                'result' => ['title' => 'new title', 'status' => 2, 'list' => [ 'dueDate' => '2020-11-06'] ]
            ],
            [
                'data' => ['title' => 'new title', 'status' => 3, 'dueDate' => '2020-11-06'],
                'result' => ['title' => 'new title', 'status' => 3, 'list' => [ 'dueDate' => '2020-11-06'] ]
            ],
            [
                'data' => ['title' => 'new title', 'status' => 4, 'dueDate' => '2020-11-06'],
                'result' => ['title' => 'new title', 'status' => 4, 'list' => [ 'dueDate' => '2020-11-06'] ]
            ],
        ];
    }

    public function testDeleteTaskItem(ApiTester $I): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskList = $user->getTaskLists()->first();
        $task = $taskList->getTasks()->first();

        $I->login();
        $I->sendDelete('api/tasks/'.$task->getId());
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->dontSeeInRepository(Task::class, ['id' => $task->getId()]);
    }
}
