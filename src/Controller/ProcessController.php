<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/process')]
class ProcessController extends AbstractController
{
    #[Route('/add', name: 'add_process')]
    public function add(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProcessController.php',
        ]);
    }

    #[Route('/remove', name: 'remove_process')]
    public function remove(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProcessController.php',
        ]);
    }
}

