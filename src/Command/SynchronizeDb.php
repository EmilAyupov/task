<?php

namespace App\Command;

use App\Service\DataBaseService;
use App\Service\MigrateDbService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

#[AsCommand(
    name: 'db:synchronize',
    description: 'Database synchronization'
)]
class SynchronizeDb extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dotenv = new Dotenv();
        $dotenv->load('.env');

        $sourceDb = new DataBaseService($_ENV['DATABASE_URL_DEV']);
        $targetDb = new DataBaseService($_ENV['DATABASE_URL_PROD']);

        $migrateService = new MigrateDbService($sourceDb, $targetDb);
        $migrateService->migrateTables();
        $migrateService->migrateData();

        return 0;
    }
}
