<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
    #[Route('/api/ping', name: 'app_api_ping', methods: ['GET'])]
    public function ping(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
        ]);
    }

    #[Route('/api/hello/{name}', name: 'app_api_hello', methods: ['GET'])]
    public function hello(string $name): JsonResponse
    {
        return $this->json([
            'message' => "Bonjour {$name} !",
        ]);
    }
}
