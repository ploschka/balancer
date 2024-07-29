<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Repository\MachineRepository;
use App\Service\LoadBalancer;
use Doctrine\Common\Collections\ArrayCollection;
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
        $p = $lb->findProcessForMachine($m);
        $em->persist($m);
        $em->flush();

        $pid = $p === null ? null : $p->getId();
        return $this->json([
            'process_id' => $pid,
            'machine_id' => $m->getId(),
        ]);
    }

    #[Route('/remove', name: 'remove_machine')]
    public function remove(Request $r, LoadBalancer $lb, EntityManagerInterface $em, MachineRepository $mrep): JsonResponse
    {
        $updates = [];
        $j = json_decode($r->getContent(), true);
        if (!ctype_digit($j['id']) and !is_int($j['id']))
        {
            return $this->json(null, 400);
        }

        $m = $mrep->find($j['id']);
        if (!is_null($m))
        {
            $ps = $lb->freeMachine($m);
            $em->remove($m);
            $em->flush();
        }

        return $this->json($updates);
    }
}
