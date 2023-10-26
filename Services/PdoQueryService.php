<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDO;
use PDOStatement;

class PdoQueryService
{
    private PDO $pdo;
    private string $dbConnection;

	public function __construct()
	{
        $this->setDatabaseConnection();
        $this->pdo = DB::connection($this->dbConnection)->getPdo();
	}

    public function setDatabaseConnection(?string $connection = null): void
    {
        if (!empty($connection)) {
            $this->dbConnection = $connection;
        }

        $this->dbConnection = config('database.default');
    }

    public function executeQuery(string $query): PDOStatement
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function fetchAllResultsFromStatement(PDOStatement $stmt, int $fetchMode = PDO::FETCH_ASSOC): array
    {
        return $stmt->fetchAll($fetchMode);
    }
}
