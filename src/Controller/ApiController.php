<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Book;

final class ApiController extends AbstractController
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer) {
        $this->serializer = $serializer;
    }

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
    #[Route('/api/books', name: 'app_api_books', methods: ['GET'])]
    public function books(): JsonResponse
    {
        $fakeData = [
            new Book(1, "The Witcher vol.1", "Premier livre des aventures du sorceleur"),
            new Book(2, "The Witcher vol.2", "Deuxième livre des aventures du sorceleur"),
            new Book(3, "The Witcher vol.3", "Troisième livre des aventures du sorceleur")
        ];

        $jsonContent = $this->serializer->serialize($fakeData, 'json');
        $jsonResponse = new JsonResponse($jsonContent, json: true);

        return $jsonResponse;
    }
}
