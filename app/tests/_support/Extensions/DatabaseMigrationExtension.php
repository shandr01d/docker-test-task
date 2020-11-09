<?php
namespace App\Tests\Extensions;

use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Codeception\Module\Cli;
use Codeception\Events;

class DatabaseMigrationExtension extends Extension
{
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
    ];

    public function _initialize()
    {
        if (!class_exists('Codeception\Module\Cli')) {
            throw new ExtensionException($this, 'codeception/module-cli package is required');
        }
    }

    public function beforeSuite()
    {
        try {
            /** @var Cli $cli */
            $cli = $this->getModule('Cli');

            $this->writeln('Running Doctrine Migrations...');
            $cli->runShellCommand('bin/console doctrine:migrations:migrate --no-interaction --env=test');
            $cli->seeResultCodeIs(0);

            $this->writeln('Running Fixtures...');
            $cli->runShellCommand('bin/console doctrine:fixtures:load --append --env=test');
            $cli->seeResultCodeIs(0);

            $this->writeln('Test database recreated');
        } catch (\Exception $e) {
            $this->writeln(
                sprintf(
                    'An error occurred whilst rebuilding the test database: %s',
                    $e->getMessage()
                )
            );
        }
    }
}