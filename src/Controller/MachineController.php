<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/machine')]
class MachineController extends AbstractController
{
    #[Route('/add', name: 'add_machine')]
    public function add(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/MachineController.php',
        ]);
    }

    #[Route('/remove', name: 'remove_machine')]
    public function remove(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/MachineController.php',
        ]);
    }
}
