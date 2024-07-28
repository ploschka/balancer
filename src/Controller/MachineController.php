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
        if (!ctype_digit($j['memory'] or !ctype_digit($j['cpus'])))
        {
            return $this->json(null, 400);
        }

        $m->setMemory($j['memory'])->setCpus($j['cpus']);
        $lb->findProcessForMachine($m);
        $em->persist($m);
        $em->flush();

        $pid = $m->getProcess() === null ? null : $m->getProcess()->getId();
        return $this->json([
            'process_id' => $pid,
            'machine_id' => $m->getId(),
        ]);
    }

    #[Route('/remove', name: 'remove_machine')]
    public function remove(Request $r, LoadBalancer $lb, EntityManagerInterface $em, MachineRepository $mrep): JsonResponse
    {
        $mid = null;
        $pid = null;

        $j = json_decode($r->getContent(), true);
        if (!ctype_digit($j['id']))
        {
            return $this->json(null, 400);
        }

        $m = $mrep->find($j['id']);
        $p = $m->getProcess();
        $em->beginTransaction();
        $em->remove($m);
        $em->flush();
        if (!is_null($p))
        {
            $lb->findMachineForProcess($p);
            $mid = $p->getMachine() === null ? null : $p->getMachine()->getId();
            $pid = $p->getId();
        }

        $em->flush();
        $em->commit();

        return $this->json([
            'process_id' => $pid,
            'machine_id' => $mid,
        ]);
    }
}
