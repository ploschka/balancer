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
    private LoadBalancer $lb;
    private EntityManagerInterface $em;
    private ProcessRepository $prep;

    public function __construct(LoadBalancer $lb, EntityManagerInterface $em, ProcessRepository $prep)
    {
        $this->lb = $lb;
        $this->em = $em;
        $this->prep = $prep;
    }
    #[Route('/add', name: 'add_process')]
    public function add(Request $r): JsonResponse
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
        $new_m = $this->lb->findMachineForProcess($p);
        $this->em->persist($p);
        $this->em->flush();

        $mid = $new_m === null ? null : $new_m->getId();
        return $this->json([
            'process_id' => $p->getId(),
            'machine_id' => $mid,
        ]);
    }

    #[Route('/remove', name: 'remove_process')]
    public function remove(Request $r): JsonResponse
    {
        $j = json_decode($r->getContent(), true);
        if (!ctype_digit($j['id']) and !is_int($j['id']))
        {
            return $this->json(null, 400);
        }

        $p = $this->prep->find($j['id']);
        $updates = [];
        if (!is_null($p))
        {
            $mid = $p->getMachine() === null ? null : $p->getMachine()->getId();
            $ps = $this->lb->freeProcess($p);

            $this->em->remove($p);
            $this->em->flush();

            if (is_null($ps))
            {
                $updates = ['machine_id' => $mid];
            }
            else
            {
                foreach ($ps as $pp)
                {
                    $updates[] = [
                        'process_id' => $pp->getId(),
                        'machine_id' => $mid,
                    ];
                }
            }
        }

        return $this->json($updates);
    }
}

