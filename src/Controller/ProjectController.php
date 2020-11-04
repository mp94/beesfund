<?php

declare(strict_types=1);
namespace App\Controller;

use App\Entity\Project;
use App\Entity\Reward;
use App\Repository\RewardRepository;
use App\Services\HelperService;
use App\Services\RewardService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use OutOfBoundsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ProjectController extends AbstractController
{
    /**
     * @RewardRepository $RewardRepository
     */
    private $RewardRepository;
    /**
     * @Doctrine\ORM\EntityManagerInterface $EntityManager
     */
    private $EntityManager;
    /**
     * @HelperService $HelperService
     */
    private $HelperService;
    /**
     * @RewardService $RewardService
     */
    private $RewardService;

    /**
     * ProjectController constructor.
     * @param RewardRepository $rewardRepository
     * @param EntityManagerInterface $entityManager
     * @param HelperService $HelperService
     * @param RewardService $RewardService
     */
    public function __construct(
        RewardRepository $rewardRepository,
        EntityManagerInterface $entityManager,
        HelperService $HelperService,
        RewardService $RewardService
    )
    {
        $this->RewardRepository = $rewardRepository;
        $this->EntityManager = $entityManager;
        $this->HelperService = $HelperService;
        $this->RewardService = $RewardService;
    }

    /**
     * @Rest\Route("/project/findByStatus", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getProjectsByStatus(Request $request) : Response {
        $values = ['draft','started','finished'];
        $data = [];
        $statuses = $request->query->get('status');
        foreach ($statuses as $status) {
            if (in_array($status,$values)) {
                $qb = $this->EntityManager->createQueryBuilder();
                $qb->select('p')
                    ->from(Project::class, 'p')
                    ->where('p.status = ?1')
                    ->setParameter(1,$status);
                $result = $qb->getQuery()->getResult();
                foreach ($result as $project) {
                    array_push($data,$this->HelperService->prepareProjectData($project) );
                }
            } else {
                return new JsonResponse(['code' => 400, 'message' => 'Invalid input']);
            }
        }
        if (!empty($data)) {
            return new JsonResponse($data);
        } else {
            return new JsonResponse(['code' => 404, 'message' => 'Not found']);
        }
    }

    /**
     * @Rest\Route("/project/{id}", methods={"GET"})
     * @param int $id
     * @return Response
     */
    public function getProjectById(int $id)
    {
        $response = new JsonResponse();
        $project = $this->EntityManager->find(Project::class, $id);
        if ($project instanceof Project) {
            $data = $this->HelperService->prepareProjectData($project);
            return $response->setData($data);
        }
        return new JsonResponse(['code' => 404, 'message' => 'Not found']);
    }

    /**
     * @Rest\Route("/project", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addProject(Request $request) {
        $data = json_decode($request->getContent(), true);
        $project = new Project();
        $project->setName($data['name'])
            ->setDescription($data['description'])
            ->setStatus($data['status']);
        $this->EntityManager->persist($project);
        $this->EntityManager->flush();
        $projectId = $this->EntityManager->find(Project::class, $project->getId());
        $rewards = $data['rewards'];
        if ($projectId instanceof Project) {
            foreach ($rewards as $reward) {
                $newReward = $this->RewardService->prepareReward($reward, $projectId);
                $this->EntityManager->persist($newReward);
                $this->EntityManager->flush();
            }
            return new JsonResponse(['code' => 200, 'message' => 'Ok']);
        } else {
            return new JsonResponse(['code' => 405, 'message' => 'Invalid input']);
        }
    }

    /**
     * @Rest\Route("/project/{id}", methods={"POST"})
     * @param Request $request
     * @param integer $id
     * @return JsonResponse
     */
    public function UpdateProjectWithFormData(Request $request, int $id) {
        $data = json_decode($request->getContent(), true);
        try {
            $tempName = $data['name'];
            $tempStatus = $data['status'];
        } catch (OutOfBoundsException $ex) {
            return new JsonResponse(['code' => 405, 'message' => 'Invalid input']);
        }
        $qb = $this->EntityManager->createQueryBuilder();
        $qb->update(Project::class, 'p')
            ->set('p.name','?1')
            ->set('p.status', '?2')
            ->where('p.id = ?3')
            ->setParameter(1,$tempName)
            ->setParameter(2,$tempStatus)
            ->setParameter(3,$id)
            ->getQuery()
            ->execute();
        return new JsonResponse(['code' => 200, 'message' => 'Ok']);
    }

    /**
     * @Rest\Route("/project/{id}", methods={"DELETE"})
     * @param integer $id
     * @return JsonResponse
     */
    public function deleteProject(int $id) {
        if (is_int($id) and $id > 0) {
            $temp = $this->EntityManager->find(Project::class, $id);
            if (is_null($temp)) {
                return new JsonResponse(['code' => 404, 'message' => 'Project not found']);
            } else {
                $this->EntityManager->remove($temp);
                $this->EntityManager->flush();
            }
        } else {
            return new JsonResponse(['code' => 400, 'message' => 'Invalid ID supplied']);
        }
        return new JsonResponse(['code' => 200, 'message' => 'Ok']);
    }

    /**
     * @Rest\Route("/project", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updateWithPut(Request $request) {
        $data = json_decode($request->getContent(), true);
        if (is_int($data['id']) and $data['id'] > 0) {
            $project = $this->EntityManager->find(Project::class, $data['id']);
            if (!is_null($project)) {
                $qb = $this->EntityManager->createQueryBuilder();
                $qb->update(Project::class, 'p')
                    ->set('p.name','?1')
                    ->set('p.description', '?2')
                    ->set('p.status', '?4')
                    ->where('p.id = ?3')
                    ->setParameter(1,$data['name'])
                    ->setParameter(2,$data['description'])
                    ->setParameter(3,$data['id'])
                    ->setParameter(4,$data['status'])
                    ->getQuery()
                    ->execute();
                $rewardsToUpdate = $data['rewards'];
                foreach ($rewardsToUpdate as $rtu) {
                    $updateQuery = $this->EntityManager->createQueryBuilder();
                    $updateQuery->update(Reward::class, 'r')
                        ->set('r.name', '?1')
                        ->set('r.description', '?2')
                        ->set('r.amount', '?3')
                        ->where('r.id = ?4')
                        ->setParameter(1,$rtu['name'])
                        ->setParameter(2,$rtu['description'])
                        ->setParameter(3,$rtu['amount'])
                        ->setParameter(4,$rtu['id'])
                        ->getQuery()
                        ->execute();
                }
                return new JsonResponse(['code' => 200, 'message' => 'Ok']);
            } else {
                return new JsonResponse(['code' => 404, 'message' => 'Project not found']);
            }
        } else {
            return new JsonResponse(['code' => 400, 'message' => 'Invalid ID supplied']);
        }
    }
}
