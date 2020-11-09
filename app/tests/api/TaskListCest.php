<?php namespace App\Tests;
use App\Tests\ApiTester;
use Codeception\Util\HttpCode;

class TaskListCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Accept', '*/*');
    }

    public function testGetCollection(ApiTester $I): void
    {
        $I->login();
        $I->sendGet('api/task_lists/');
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
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].dueDate');
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].tasks');
    }

    public function testFilterByDueDateCollection(ApiTester $I): void
    {
        $I->login();
        $I->sendGet('api/task_lists/', ['dueDate' => (new \DateTime())->format('Y-m-d')]);
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
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].dueDate');
        $I->seeResponseJsonMatchesJsonPath('$.[\'hydra:member\'][*].tasks');
    }

    public function testUserCannotGetAnotherUserCollection(ApiTester $I): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskLists = $user->getTaskLists();

        $I->login($I::SECOND_USER_EMAIL, $I::SECOND_USER_PASS);
        $I->sendGet('api/task_lists');
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
        foreach ($taskLists as $taskList) {
            $I->dontSeeResponseContainsJson(["@id" => "/api/tasks/".$taskList->getId()]);
        }
    }

    public function testGetItem(ApiTester $I): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskList = $user->getTaskLists()->first();

        $I->login($I::FIRST_USER_EMAIL, $I::FIRST_USER_PASS);
        $I->sendGet('api/task_lists/'.$taskList->getId());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            "@context" => "string",
            "@id" => "string",
            "@type" => "string",
            "id" => "integer",
            "dueDate" => "string",
            "tasks" => "array"
        ]);
        $I->seeResponseJsonMatchesJsonPath('$.tasks[*].[\'@id\']');
        $I->seeResponseJsonMatchesJsonPath('$.tasks[*].[\'@type\']');
        $I->seeResponseJsonMatchesJsonPath('$.tasks[*].title');
        $I->seeResponseJsonMatchesJsonPath('$.tasks[*].status');
    }

    public function testCreateListItemNotAllowed(ApiTester $I): void
    {
        $I->login();
        $I->sendPost('api/task_lists', ['dueDate' => '2020-01-01']);
        $I->seeResponseCodeIs(HttpCode::METHOD_NOT_ALLOWED);
    }

    public function testUpdateListItemNotAllowed(ApiTester $I): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskList = $user->getTaskLists()->first();

        $I->login($I::FIRST_USER_EMAIL, $I::FIRST_USER_PASS);
        $I->sendPatch('api/task_lists/'.$taskList->getId());
        $I->seeResponseCodeIs(HttpCode::METHOD_NOT_ALLOWED);
    }

    public function testDeleteListItemNotAllowed(ApiTester $I): void
    {
        $user = $I->getUserDataFromDatabase($I::FIRST_USER_EMAIL);
        $taskList = $user->getTaskLists()->first();

        $I->login($I::FIRST_USER_EMAIL, $I::FIRST_USER_PASS);
        $I->sendDelete('api/task_lists/'.$taskList->getId());
        $I->seeResponseCodeIs(HttpCode::METHOD_NOT_ALLOWED);
    }
}
