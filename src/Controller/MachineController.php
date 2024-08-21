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
    private LoadBalancer $lb;
    private EntityManagerInterface $em;
    private MachineRepository $mrep;

    public function __construct(LoadBalancer $lb, EntityManagerInterface $em, MachineRepository $mrep)
    {
        $this->lb = $lb;
        $this->em = $em;
        $this->mrep = $mrep;
    }

    #[Route('/add', name: 'add_machine')]
    public function add(Request $r): JsonResponse
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
        $ps = $this->lb->findProcessesForMachine($m);
        $this->em->persist($m);
        $this->em->flush();

        $updates = [];
        if (is_null($ps))
        {
            $updates = ['machine_id' => $m->getId()];
        }
        else
        {
            foreach ($ps as $p)
            {
                $mid = $m->getId();
                $updates[] = [
                    'process_id' => $p->getId(),
                    'machine_id' => $mid,
                ];
            }
        }
        return $this->json($updates);
    }

    #[Route('/remove', name: 'remove_machine')]
    public function remove(Request $r): JsonResponse
    {
        $j = json_decode($r->getContent(), true);
        if (!ctype_digit($j['id']) and !is_int($j['id']))
        {
            return $this->json(null, 400);
        }

        $m = $this->mrep->find($j['id']);
        $updates = [];
        if (!is_null($m))
        {
            $ps = $this->lb->freeMachine($m);
            $ps = $ps ?? [];
            foreach ($ps as $p)
            {
                $mid = $p->getMachine() === null ? null : $p->getMachine()->getId();
                $updates[] = [
                    'process_id' => $p->getId(),
                    'machine_id' => $mid,
                ];
            }
            $this->em->remove($m);
            $this->em->flush();
        }

        return $this->json($updates);
    }
}
