<?php
namespace App\Tests;

use App\Entity\User;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    const FIRST_USER_EMAIL = 'test_user1@example.com';
    const FIRST_USER_PASS = 'pass_1';
    const SECOND_USER_EMAIL = 'test_user2@example.com';
    const SECOND_USER_PASS = 'pass_2';

    /**
     * Define custom actions here
     */
    public function login(string $email = self::FIRST_USER_EMAIL, string $password = 'pass_1')
    {
        $I = $this;
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('authentication_token', ['email' => $email, 'password' => $password]);
        $response = json_decode($I->grabResponse(), true);
        $token = $response['token'];
        $I->amBearerAuthenticated($token);
    }

    public function getUserDataFromDatabase(string $email = self::FIRST_USER_EMAIL): User
    {
        return $this->grabEntityFromRepository(User::class, ['email' => $email]);
    }

    public function getUserTaskFromDatabase(string $email = self::FIRST_USER_EMAIL): User
    {
        return $this->grabEntityFromRepository(User::class, ['email' => $email]);
    }
}
