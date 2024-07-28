<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Repository\MachineRepository;
use App\Service\LoadBalancer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/machine')]
class MachineController extends AbstractController
{
    #[Route('/add', name: 'add_machine')]
    public function add(Request $r, LoadBalancer $lb, EntityManagerInterface $em): JsonResponse
    {
        $m = new Machine();
        $j = json_decode($r->getContent(), true);
        $m->setMemory($j['memory'])->setCpus($j['cpus']);
        $lb->findProcessForMachine($m);
        $em->persist($m);
        $em->flush();
        return $this->json([
        ]);
    }

    #[Route('/remove', name: 'remove_machine')]
    public function remove(Request $r, LoadBalancer $lb, EntityManagerInterface $em, MachineRepository $mrep): JsonResponse
    {
        $j = json_decode($r->getContent(), true);
        $m = $mrep->find($j['id']);
        $p = $m->getProcess();
        $em->beginTransaction();
        $em->remove($m);
        $em->flush();
        $lb->findMachineForProcess($p);
        $em->flush();
        $em->commit();
        return $this->json([
        ]);
    }
}
