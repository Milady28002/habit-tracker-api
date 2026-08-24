<?php

namespace App\Service;

use MongoDB\Client;

class MongoStatsService
{
    private $collection;

    public function __construct()
    {
        $client = new Client($_ENV['MONGODB_URL']);
        $database = $client->selectDatabase($_ENV['MONGODB_DB']);

        $this->collection = $database->stats;
    }

    public function saveHabitStat(array $data): void
    {
        $this->collection->updateOne(
            [
                'userId' => $data['userId'],
                'habitId' => $data['habitId'],
                'date' => $data['date'],
            ],
            [
                '$set' => [
                    'userId' => $data['userId'],
                    'habitId' => $data['habitId'],
                    'habit' => $data['habit'],
                    'completed' => $data['completed'],
                    'date' => $data['date'],
                    'updatedAt' => new \MongoDB\BSON\UTCDateTime(),
                ],
            ],
            ['upsert' => true]
        );
    }

    public function getStats(int $userId): array
    {
        $today = (new \DateTime())->format('Y-m-d');

        $totalToday = $this->collection->countDocuments([
            'userId' => $userId,
            'date' => $today,
        ]);

        $completedToday = $this->collection->countDocuments([
            'userId' => $userId,
            'date' => $today,
            'completed' => true,
        ]);

        $completionRate = $totalToday > 0
            ? round(($completedToday / $totalToday) * 100, 2)
            : 0;

        return [
            'totalHabitsTracked' => $totalToday,
            'completedHabits' => $completedToday,
            'completionRate' => $completionRate,
        ];
    }

    public function getHistory(
        int $userId,
        string $startDate,
        string $endDate,
        array $habits
    ): array {

        $documents = $this->collection->find([
            'userId' => $userId,
            'date' => [
                '$gte' => $startDate,
                '$lte' => $endDate,
            ],
        ]);

        $habitStats = [];

        foreach ($documents as $document) {

            $habitId = $document['habitId'];

            if (!isset($habitStats[$habitId])) {

                $plannedDays = 0;

                foreach ($habits as $habitEntity) {

                    if ($habitEntity->getId() !== $habitId) {
                        continue;
                    }

                    $currentDate = new \DateTime($startDate);
                    $lastDate = new \DateTime($endDate);

                    while ($currentDate <= $lastDate) {

                        $translations = [
                            'monday' => 'lundi',
                            'tuesday' => 'mardi',
                            'wednesday' => 'mercredi',
                            'thursday' => 'jeudi',
                            'friday' => 'vendredi',
                            'saturday' => 'samedi',
                            'sunday' => 'dimanche',
                        ];

                        $dayName = strtolower(
                            $currentDate->format('l')
                        );

                        $dayName = $translations[$dayName];

                        if (
                            empty($habitEntity->getDays()) ||
                            in_array($dayName, $habitEntity->getDays())
                        ) {
                            $plannedDays++;
                        }

                        $currentDate->modify('+1 day');
                    }
                }

                $habitStats[$habitId] = [
                    'habit' => $document['habit'],
                    'plannedDays' => $plannedDays,
                    'completed' => 0,
                ];
            }

            if ($document['completed']) {
                $habitStats[$habitId]['completed']++;
            }
        }

        $result = [];

        foreach ($habitStats as $habit) {

            $completionRate = $habit['plannedDays'] > 0
                ? round(($habit['completed'] / $habit['plannedDays']) * 100)
                : 0;

            $result[] = [
                'habit' => $habit['habit'],
                'plannedDays' => $habit['plannedDays'],
                'completed' => $habit['completed'],
                'completionRate' => $completionRate,
            ];
        }

        return $result;
    }
    public function isHabitCompletedForDate(
        int $userId,
        int $habitId,
        string $date
    ): bool {
        $document = $this->collection->findOne([
            'userId' => $userId,
            'habitId' => $habitId,
            'date' => $date,
            'completed' => true,
        ]);

        return $document !== null;
    }

    public function getCompletedHabitsForDate(
        int $userId,
        string $date
    ): array {

        $documents = $this->collection->find([
            'userId' => $userId,
            'date' => $date,
            'completed' => true,
        ]);

        $completedHabitIds = [];

        foreach ($documents as $document) {
            $completedHabitIds[] = $document['habitId'];
        }

        return $completedHabitIds;
    }
}