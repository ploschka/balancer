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
    public function add(Request $r, LoadBalancer $lb, EntityManagerInterface $em): JsonResponse
    {
        $p = new Process();
        $j = json_decode($r->getContent(), true);
        if (!ctype_digit($j['memory'] or !ctype_digit($j['cpus'])))
        {
            return $this->json(null, 400);
        }

        $p->setMemory($j['memory'])->setCpus($j['cpus']);
        $lb->findMachineForProcess($p);
        $em->persist($p);
        $em->flush();

        $mid = $p->getMachine() === null ? null : $p->getMachine()->getId();
        return $this->json([
            'process_id' => $p->getId(),
            'machine_id' => $mid,
        ]);
    }

    #[Route('/remove', name: 'remove_process')]
    public function remove(Request $r, LoadBalancer $lb, EntityManagerInterface $em, ProcessRepository $prep): JsonResponse
    {
        $pid = null;
        $mid = null;

        $j = json_decode($r->getContent(), true);
        if (!ctype_digit($j['id']))
        {
            return $this->json(null, 400);
        }

        $p = $prep->find($j['id']);
        $m = $p->getMachine();
        $em->remove($p);
        if (!is_null($m))
        {
            $lb->findProcessForMachine($m);
            $pid = $m->getProcess() === null ? null : $m->getProcess()->getId();
            $mid = $m->getId();
        }

        $em->flush();


        return $this->json([
            'process_id' => $pid,
            'machine_id' => $mid,
        ]);
    }
}

