<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Service\LoadBalancer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/machine')]
class MachineController extends AbstractController
{
    #[Route('/add', name: 'add_machine')]
    public function add(Request $r, LoadBalancer $lb): JsonResponse
    {
        $m = new Machine();
        $j = json_decode($r->getContent(), true);
        $m->setMemory($j['memory'])->setCpus($j['cpus']);
        $lb->findProcessForMachine($m);
        return $this->json([
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
