<?php

namespace App\Controller\Api;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\HabitStatsService;
use App\Service\MongoStatsService;

final class HabitController extends AbstractController
{
    #[Route('/api/habits', name: 'api_habits_list', methods: ['GET'])]
    public function list(
        Request $request, 
        UserRepository $userRepository, 
        HabitRepository $habitRepository,
        MongoStatsService $mongoStatsService
    ): JsonResponse
    {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if (!$token) {
            return $this->json(['message' => 'Token manquant'], 401);
        }

        $user = $userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return $this->json(['message' => 'Token invalide'], 401);
        }

        $date = $request->query->get('date') ?? (new \DateTime())->format('Y-m-d');

        $habits = $habitRepository->findBy(['owner' => $user]);

        $completedHabits = $mongoStatsService->getCompletedHabitsForDate(
            $user->getId(),
            $date
        );

        $data = array_map(function (Habit $habit) use ($completedHabits) {

            return [
                'id' => $habit->getId(),
                'title' => $habit->getTitle(),
                'days' => $habit->getDays(),
                'done' => in_array($habit->getId(), $completedHabits),
            ];
        }, $habits);

        return $this->json($data, 200);
    }

    #[Route('/api/habits', name: 'api_habits_create', methods: ['POST'])]
    public function create(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if (!$token) {
            return $this->json(['message' => 'Token manquant'], 401);
        }

        $user = $userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return $this->json(['message' => 'Token invalide'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (
            !is_array($data) ||
            empty($data['title']) ||
            !isset($data['days']) ||
            !is_array($data['days'])
        ) {
            return $this->json([
                'message' => 'Données invalides. Le titre et les jours sont requis.'
            ], 400);
        }

        $habit = new Habit();
        $habit->setTitle($data['title']);
        $habit->setDays($data['days']);
        $habit->setDone(false);
        $habit->setOwner($user);

        $entityManager->persist($habit);
        $entityManager->flush();

        return $this->json([
            'message' => 'Habitude créée',
            'habit' => [
                'id' => $habit->getId(),
                'title' => $habit->getTitle(),
                'done' => $habit->isDone(),
                'days' => $habit->getDays(),
            ]
        ], 201);
    }
    #[Route('/api/habits/{id}/toggle', name: 'api_habits_toggle', methods: ['PATCH'])]
    public function toggle(
        int $id,
        Request $request,
        UserRepository $userRepository,
        HabitRepository $habitRepository,
        EntityManagerInterface $entityManager,
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

        $habit = $habitRepository->find($id);

        if (!$habit) {
            return $this->json(['message' => 'Habitude introuvable'], 404);
        }

        if ($habit->getOwner() !== $user) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $date = $request->query->get('date') ?? (new \DateTime())->format('Y-m-d');

        $isCompleted = $mongoStatsService->isHabitCompletedForDate(
            $user->getId(),
            $habit->getId(),
            $date
        );

        $newCompletedStatus = !$isCompleted;

        $mongoStatsService->saveHabitStat([
            'userId' => $user->getId(),
            'habitId' => $habit->getId(),
            'habit' => $habit->getTitle(),
            'completed' => $newCompletedStatus,
            'date' => $date,
        ]);

        $entityManager->flush();

        return $this->json([
            'message' => 'Statut mis à jour',
            'habit' => [
                'id' => $habit->getId(),
                'title' => $habit->getTitle(),
                'done' => $newCompletedStatus,
                'days' => $habit->getDays(),
            ]
        ], 200);
    }
    #[Route('/api/habits/{id}', name: 'api_habits_update', methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
        UserRepository $userRepository,
        HabitRepository $habitRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if (!$token) {
            return $this->json(['message' => 'Token manquant'], 401);
        }

        $user = $userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return $this->json(['message' => 'Token invalide'], 401);
        }

        $habit = $habitRepository->find($id);

        if (!$habit) {
            return $this->json(['message' => 'Habitude introuvable'], 404);
        }

        if ($habit->getOwner() !== $user) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        if (isset($data['title']) && trim($data['title']) !== '') {
            $habit->setTitle(trim($data['title']));
        }

        if (isset($data['days'])) {
            if (!is_array($data['days'])) {
                return $this->json(['message' => 'Le champ days doit être un tableau'], 400);
            }

            $habit->setDays($data['days']);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Habitude mise à jour',
            'habit' => [
                'id' => $habit->getId(),
                'title' => $habit->getTitle(),
                'done' => $habit->isDone(),
                'days' => $habit->getDays(),
            ]
        ], 200);
    }
    #[Route('/api/habits/{id}', name: 'api_habits_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        Request $request,
        UserRepository $userRepository,
        HabitRepository $habitRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $token = $request->headers->get('X-AUTH-TOKEN');

        if (!$token) {
            return $this->json(['message' => 'Token manquant'], 401);
        }

        $user = $userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            return $this->json(['message' => 'Token invalide'], 401);
        }

        $habit = $habitRepository->find($id);

        if (!$habit) {
            return $this->json(['message' => 'Habitude introuvable'], 404);
        }

        if ($habit->getOwner() !== $user) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $entityManager->remove($habit);
        $entityManager->flush();

        return $this->json([
            'message' => 'Habitude supprimée'
        ], 200);
    }
}