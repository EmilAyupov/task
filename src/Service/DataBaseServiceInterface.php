<?php

namespace App\Service;

interface DataBaseServiceInterface
{
    public function getTablesName(): array;

    public function getTableSchema(string $table): string;

    public function getTableColumns(string $table): array;

    public function createTable(string $sql): void;

    public function addColumns(string $table, $column): void;

    public function updateTable(string $table, $columns): void;

    public function getRowFromTable(string $table): array;

    public function getRowById(string $table, int $id): ?array;

    public function addRow(string $table, array $row): void;

    public function updateRow(string $table, array $row): void;
}