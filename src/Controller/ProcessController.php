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
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/process')]
class ProcessController extends AbstractController
{
    private LoadBalancer $lb;
    private EntityManagerInterface $em;
    private ProcessRepository $prep;
    private ValidatorInterface $validator;

    private Constraint $int_constraint;
    private Constraint $positive_constraint;

    public function __construct(LoadBalancer $lb, EntityManagerInterface $em, ProcessRepository $prep, ValidatorInterface $validator)
    {
        $this->lb = $lb;
        $this->em = $em;
        $this->prep = $prep;
        $this->validator = $validator;

        $this->int_constraint = new Assert\Type('integer');
        $this->positive_constraint = new Assert\Positive();
    }
    #[Route('/add', name: 'add_process')]
    public function add(Request $r): JsonResponse
    {
        $p = new Process();
        $j = json_decode($r->getContent(), true);
        if (is_null($j))
        {
            return $this->json(null, 400);
        }

        $p->setMemory($j['memory'])->setCpus($j['cpus']);

        $errors = $this->validator->validate($p);
        if (count($errors) > 0)
        {
            return $this->json(null, 400);
        }

        $new_m = $this->lb->findMachineForProcess($p);
        $this->em->persist($p);
        $this->em->flush();

        $updates = [];
        if (is_null($new_m))
        {
            $updates = ['process_id' => $p->getId()];
        }
        else
        {
            $updates = [
                'process_id' => $p->getId(),
                'machine_id' => $new_m->getId(),
            ];
        }

        return $this->json($updates);
    }

    #[Route('/remove', name: 'remove_process')]
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

        $p = $this->prep->findById($j['id']);
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

