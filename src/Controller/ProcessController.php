<?php

namespace App\Controller;

use App\Entity\Process;
use App\Repository\ProcessRepository;
use App\Service\LoadBalancer;
use Doctrine\ORM\EntityManagerInterface;
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
    public function remove(Request $r, LoadBalancer $lb, EntityManagerInterface $em, ProcessRepository $prep): JsonResponse
    {
        $j = json_decode($r->getContent(), true);
        $p = $prep->find($j['id']);
        $m = $p->getMachine();
        $em->remove($p);
        $lb->findProcessForMachine($m);
        return $this->json([
        ]);
    }
}

