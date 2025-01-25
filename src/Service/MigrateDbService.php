<?php

namespace App\Service;

use Exception;

class MigrateDbService
{
    public function __construct(
        private readonly DataBaseServiceInterface $sourceDb,
        private readonly DataBaseServiceInterface $targetDb,
    )
    {

    }

    public function migrateTables(): void
    {
        try {
            $sourceTables = $this->sourceDb->getTablesName();
            $targetTables = $this->targetDb->getTablesName();

            foreach ($sourceTables as $table) {
                $schema = $this->sourceDb->getTableSchema($table);
                if (in_array($table, $targetTables)) {
                    $columns = $this->targetDb->getTableColumns($table);
                    $this->targetDb->updateTable($table, $columns);
                } else {
                    $this->targetDb->createTable($schema);
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function migrateData(): void
    {
        try {
            $sourceTables = $this->sourceDb->getTablesName();

            foreach ($sourceTables as $table) {
                $sourceRows = $this->sourceDb->getRowFromTable($table);
                foreach ($sourceRows as $row) {
                    if ($this->targetDb->getRowById($table, $row['id'])) {
                        $this->targetDb->updateRowById($table, $row);
                    } else {
                        $this->targetDb->addRowById($table, $row);
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}