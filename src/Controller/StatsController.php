<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\MongoStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\HabitRepository;

class StatsController extends AbstractController
{
    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        MongoStatsService $mongoStatsService
    ): JsonResponse {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if (!$token) {
            return $this->json(['message' => 'Token manquant'], 401);
        }

        $user = $userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return $this->json(['message' => 'Token invalide'], 401);
        }

        return $this->json($mongoStatsService->getStats($user->getId()));
    }

    #[Route('/api/stats/history', name: 'api_stats_history', methods: ['GET'])]
    public function history(
        Request $request,
        UserRepository $userRepository,
        MongoStatsService $mongoStatsService,
        HabitRepository $habitRepository,
    ): JsonResponse {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if (!$token) {
            return $this->json(['message' => 'Token manquant'], 401);
        }

        $user = $userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return $this->json(['message' => 'Token invalide'], 401);
        }

        $startDate = $request->query->get('start');
        $endDate = $request->query->get('end');

        if (!$startDate || !$endDate) {
            return $this->json(['message' => 'Les dates de début et de fin sont obligatoires'], 400);
        }

        return $this->json([
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'habits' => $mongoStatsService->getHistory(
                $user->getId(),
                $startDate,
                $endDate,
                $habitRepository->findBy(['owner' => $user])
            )
        ]);
    }
}