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
        if (!ctype_digit($j['memory']) or !ctype_digit($j['cpus']))
        {
            if (!is_int($j['memory']) or !is_int($j['cpus']))
            {
                return $this->json(null, 400);
            }
        }

        $p->setMemory($j['memory'])->setCpus($j['cpus']);
        $new_m = $lb->findMachineForProcess($p);
        $em->persist($p);
        $em->flush();

        $mid = $new_m === null ? null : $new_m->getId();
        return $this->json([
            'process_id' => $p->getId(),
            'machine_id' => $mid,
        ]);
    }

    #[Route('/remove', name: 'remove_process')]
    public function remove(Request $r, LoadBalancer $lb, EntityManagerInterface $em, ProcessRepository $prep): JsonResponse
    {
        $j = json_decode($r->getContent(), true);
        if (!ctype_digit($j['id']) and !is_int($j['id']))
        {
            return $this->json(null, 400);
        }

        $p = $prep->find($j['id']);
        $updates = [];
        if (!is_null($p))
        {
            $mid = $p->getMachine() === null ? null : $p->getMachine()->getId();
            $ps = $lb->freeProcess($p);

            $em->remove($p);
            $em->flush();

            $ps = $ps ?? [];
            foreach ($ps as $pp)
            {
                $updates[] = [
                    'process_id' => $pp->getId(),
                    'machine_id' => $mid,
                ];
            }
        }

        return $this->json($updates);
    }
}

