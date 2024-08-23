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
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/machine')]
class MachineController extends AbstractController
{
    private LoadBalancer $lb;
    private EntityManagerInterface $em;
    private MachineRepository $mrep;
    private ValidatorInterface $validator;

    private Constraint $int_constraint;
    private Constraint $positive_constraint;

    public function __construct(LoadBalancer $lb, EntityManagerInterface $em, MachineRepository $mrep, ValidatorInterface $validator)
    {
        $this->lb = $lb;
        $this->em = $em;
        $this->mrep = $mrep;
        $this->validator = $validator;

        $this->int_constraint = new Assert\Type('integer');
        $this->positive_constraint = new Assert\Positive();
    }

    #[Route('/add', name: 'add_machine')]
    public function add(Request $r): JsonResponse
    {
        $m = new Machine();
        $j = json_decode($r->getContent(), true);
        if (is_null($j))
        {
            return $this->json(null, 400);
        }

        $m->setMemory($j['memory'])->setCpus($j['cpus'])->setFreeMemory($j['memory'])->setFreeCpus($j['cpus']);

        $errors = $this->validator->validate($m);
        if (count($errors) > 0)
        {
            return $this->json(null, 400);
        }

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
                $updates[] = [
                    'process_id' => $p->getId(),
                    'machine_id' => $m->getId(),
                ];
            }
        }
        return $this->json($updates);
    }

    #[Route('/remove', name: 'remove_machine')]
    public function remove(Request $r): JsonResponse
    {
        $j = json_decode($r->getContent(), true);
        if (is_null($j))
        {
            return $this->json(null, 400);
        }

        $errors = $this->validator->validate($j['id'], [$this->int_constraint, $this->positive_constraint]);

        if (count($errors) > 0)
        {
            return $this->json(null, 400);
        }

        $m = $this->mrep->findById($j['id']);
        $updates = [];
        if (!is_null($m))
        {
            $ps = $this->lb->freeMachine($m);
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
