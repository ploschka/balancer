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
        if (!ctype_digit($j['memory']) or !ctype_digit($j['cpus']))
        {
            if (!is_int($j['memory']) or !is_int($j['cpus']))
            {
                return $this->json(null, 400);
            }
        }

        $m->setMemory($j['memory'])->setCpus($j['cpus'])->setFreeMemory($j['memory'])->setFreeCpus($j['cpus']);
        $ps = $lb->findProcessesForMachine($m);
        $em->persist($m);
        $em->flush();

        $updates = [];
        $ps = $ps ?? [];
        foreach ($ps as $p)
        {
            $mid = $m->getId();
            $updates[] = [
                'process_id' => $p->getId(),
                'machine_id' => $mid,
            ];
        }
        return $this->json($updates);
    }

    #[Route('/remove', name: 'remove_machine')]
    public function remove(Request $r, LoadBalancer $lb, EntityManagerInterface $em, MachineRepository $mrep): JsonResponse
    {
        $j = json_decode($r->getContent(), true);
        if (!ctype_digit($j['id']) and !is_int($j['id']))
        {
            return $this->json(null, 400);
        }

        $m = $mrep->find($j['id']);
        $updates = [];
        if (!is_null($m))
        {
            $ps = $lb->freeMachine($m);
            $ps = $ps ?? [];
            foreach ($ps as $p)
            {
                $mid = $p->getMachine() === null ? null : $p->getMachine()->getId();
                $updates[] = [
                    'process_id' => $p->getId(),
                    'machine_id' => $mid,
                ];
            }
            $em->remove($m);
            $em->flush();
        }

        return $this->json($updates);
    }
}
