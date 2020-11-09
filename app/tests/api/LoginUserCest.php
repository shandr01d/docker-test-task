<?php namespace App\Tests;

use App\Tests\ApiTester;
use Codeception\Util\HttpCode;

class LoginUserCest
{
    public function _before(ApiTester $I)
    {
    }

    // tests
    public function successLoginTest(ApiTester $I)
    {
        $I->wantToTest('User success login');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['email' => 'test_user1@example.com', 'password' => 'pass_1']);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function wrongLoginAndPasswordTest(ApiTester $I)
    {
        $I->wantToTest('Wrong login and wrong password');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['email' => 'notexisted@example.com', 'password' => 'wrong password']);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function wrongPasswordTest(ApiTester $I)
    {
        $I->wantToTest('Wrong password');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['email' => 'test_user1@example.com', 'password' => 'wrong_password']);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function withoutLoginAndCorrectPasswordTest(ApiTester $I)
    {
        $I->wantToTest('User without email');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['password' => 'pass_1']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function withoutLoginAndIncorrectPasswordTest(ApiTester $I)
    {
        $I->wantToTest('User without email and wrong password');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['password' => 'wrong_password']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function withoutPasswordAndCorrectLoginTest(ApiTester $I)
    {
        $I->wantToTest('User without password and correct email');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['email' => 'test_user1@example.com']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function withoutPasswordAndIncorrectLoginTest(ApiTester $I)
    {
        $I->wantToTest('User without password and wrong email');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['email' => 'notexisted@example.com']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function customParametersTest(ApiTester $I)
    {
        $I->wantToTest('User custom parameters');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['login' => 'test_user1@example.com', 'pass' => 'pass_1']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function withoutParametersTest(ApiTester $I)
    {
        $I->wantToTest('User custom parameters');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', []);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }
}
