<?php

namespace App\Service;

use MongoDB\Client;

class HabitStatsService
{
    private $collection;

    public function __construct()
    {
        $client = new Client($_ENV['MONGODB_URL']);
        $database = $client->selectDatabase($_ENV['MONGODB_DB']);
        $this->collection = $database->stats;
    }

    public function saveStat(array $data): void
    {
        $this->collection->insertOne($data);
    }

    public function getStats(): array
    {
        return $this->collection->find()->toArray();
    }

    public function getStatsByUser(int $userId): array
{
    return $this->collection->find([
        'userId' => $userId
    ])->toArray();
}
}