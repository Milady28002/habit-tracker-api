<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Service\HabitStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class StatsController extends AbstractController
{
    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function getStats(
        Request $request,
        UserRepository $userRepository,
        HabitStatsService $statsService
    ): JsonResponse {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if (!$token) {
            return $this->json(['message' => 'Token manquant'], 401);
        }

        $user = $userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return $this->json(['message' => 'Token invalide'], 401);
        }

        $stats = $statsService->getStatsByUser($user->getId());

        return $this->json($stats);
    }
}