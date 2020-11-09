<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Enum\TaskStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    const USER_COUNT = 5;
    const TASK_LIST_COUNT = 10;
    const TASK_COUNT = 50;
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $users = [];
        $lists = [];
        for($i = 1; $i <= self::USER_COUNT; $i++){
            $user = new User();
            $user->setEmail('test_user' . $i . '@example.com');

            $password = $this->encoder->encodePassword($user, 'pass_' . $i);
            $user->setPassword($password);
            $user->setRoles([]);

            $manager->persist($user);
            $users[] = $user;
        }

        for($i = 1; $i <= self::TASK_LIST_COUNT; $i++){
            $list = new TaskList();
            $list->setOwner($users[rand(0, self::USER_COUNT - 1)]);
            $date = $date = new \DateTime();
            $date->modify('+'.$i.' day');
            $list->setDueDate($date);
            $manager->persist($list);
            $lists[] = $list;
        }

        for($i = 1; $i <= self::TASK_COUNT; $i++){
            $task = new Task();
            $task->setStatus($this->getRandomStatusType());
            $task->setTitle('test title '.$i);
            $task->setList($lists[rand(0, self::TASK_LIST_COUNT - 1)]);
            $manager->persist($task);
        }

        // concrete 'test_user1@example.com' tasks and task list data
        for($i = 1; $i <= self::TASK_LIST_COUNT; $i++){
            $list = new TaskList();
            $date = new \DateTime();
            $date->modify('-5 day');
            $date->add(new \DateInterval('P'.$i.'D'));
            $list->setOwner($users[0]);
            $list->setDueDate($date);

            for($j = 1; $j < 5; $j++){
                $task = new Task();
                $task->setStatus($this->getRandomStatusType());
                $task->setTitle('test title '.$i);
                $task->setList($list);
                $manager->persist($task);
            }
            $manager->persist($list);
        }

        $manager->flush();
    }

    private function getRandomStatusType(): int
    {
        $min = min(TaskStatus::getAvailableStatuses());
        $max = max(TaskStatus::getAvailableStatuses());
        return rand($min, $max);
    }
}
