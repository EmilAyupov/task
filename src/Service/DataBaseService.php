<?php

namespace App\Service;

use PDO;
use PDOException;

class DataBaseService implements DataBaseServiceInterface
{
    /**
     * @var PDO|null
     */
    private ?PDO $db = null;

    /**
     * @param string $databaseUrl
     */
    public function __construct(string $databaseUrl)
    {
        try {
            $this->db = new PDO($databaseUrl);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function __destruct()
    {
        $this->db = null;
    }

    /**
     * @return array
     */
    public function getTablesName(): array
    {
        $tables = [];
        $sql = 'SHOW TABLES';

        foreach ($this->db->query($sql) as $row) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    /**
     * @param string $table
     * @return string
     */
    public function getTableSchema(string $table): string
    {
        $sql = sprintf('SHOW CREATE TABLE %s', $table);
        return $this->db->query($sql, PDO::FETCH_ASSOC)->fetch()['Create Table'];
    }

    /**
     * @param string $table
     * @return array
     */
    public function getTableColumns(string $table): array
    {
        $sql = sprintf('SHOW COLUMNS FROM %s', $table);
        return $this->db->query($sql, PDO::FETCH_ASSOC)->fetchAll();
    }

    /**
     * @param string $sql
     * @return void
     */
    public function createTable(string $sql): void
    {
        $this->db->query($sql, PDO::FETCH_ASSOC);
    }

    /**
     * Здесь все варианты расписывать не буду
     */
    public function addColumns(string $table, $column): void
    {
        $sql = sprintf('ALTER TABLE %s ADD %s %s', $table, $column['Field'], $column['Type']);
        if ($column['Null'] === 'NO') {
            $sql .= ' NOT NULL';
        }
        $this->db->query($sql);
    }

    /**
     * @param string $table
     * @param $columns
     * @return void
     */
    public function updateTable(string $table, $columns): void
    {
        $currentColumns = $this->getTableColumns($table);
        foreach ($columns as $column) {
            if (!in_array($column['Field'], array_column($currentColumns, 'Field'))) {
                $this->addColumns($table, $column);
            }
        }
    }

    /**
     * @param string $table
     * @return array
     */
    public function getRowFromTable(string $table): array
    {
        $data = [];
        $sql = sprintf('SELECT * FROM %s', $table);
        foreach ($this->db->query($sql, PDO::FETCH_ASSOC) as $row) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * @param string $table
     * @param int $id
     * @return array|null
     */
    public function getRowById(string $table, int $id): ?array
    {
        $sql = sprintf('SELECT * FROM %s WHERE id=%d', $table, $id);
        return $this->db->query($sql, PDO::FETCH_ASSOC)->fetch() ?: null;
    }

    /**
     * @param string $table
     * @param array $row
     * @return void
     */
    public function addRowById(string $table, array $row): void
    {
        unset($row['id']);

        $sql = sprintf('INSERT INTO %s (%s) VALUES ("%s")',
            $table, join(', ', array_keys($row)), join('", "', array_values($row)));
        $this->db->query($sql);
    }

    /**
     * @param string $table
     * @param array $row
     * @return void
     */
    public function updateRowById(string $table, array $row): void
    {
        $strValues = '';
        foreach ($row as $column => $value) {
            $strValues .= sprintf('%s = %s,', $column, $value);
        }
        $sql = sprintf('UPDATE %s SET %s WHERE id=%d', $table, $strValues, $row['id']);
        $this->db->query($sql);
    }
}