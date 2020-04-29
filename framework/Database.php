<?php


namespace MB;


use PDO;

class Database
{
    private $pdo;

    public function __construct()
    {
        $infrastructure = \json_decode(\file_get_contents(ROOT_PATH . '/config/infrastructure.json'), true)['database'];

        $this->pdo = new PDO
        ($infrastructure['dsn'], $infrastructure['user'], $infrastructure['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        $this->pdo->exec('SET NAMES UTF8mb4;');
    }

    public function execute(string $sql, array $values = []): string
    {
        $query = $this->pdo->prepare($sql);
        $query->execute($values);

        return $this->pdo->lastInsertId();
    }

    public function queryMultiple($sql, array $values = []): array
    {
        $query = $this->pdo->prepare($sql);
        $query->execute($values);

        return $query->fetchAll();
    }

    public function queryOne($sql, array $values = [])
    {
        $query = $this->pdo->prepare($sql);
        $query->execute($values);

        return $query->fetch();
    }

    public function executeGetRowCount(string $sql, array $values = []): int
    {
        $query = $this->pdo->prepare($sql);
        $query->execute($values);

        return $query->rowCount();
    }
}
