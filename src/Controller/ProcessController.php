<?php

namespace App\Controller;

use App\Entity\Process;
use App\Service\LoadBalancer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/process')]
class ProcessController extends AbstractController
{
    #[Route('/add', name: 'add_process')]
    public function add(Request $r, LoadBalancer $lb): JsonResponse
    {
        $p = new Process();
        $j = json_decode($r->getContent(), true);
        $p->setMemory($j['memory'])->setCpus($j['cpus']);
        $lb->findMachineForProcess($p);
        return $this->json([
        ]);
    }

    #[Route('/remove', name: 'remove_process')]
    public function remove(Request $r, LoadBalancer $lb): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProcessController.php',
        ]);
    }
}

