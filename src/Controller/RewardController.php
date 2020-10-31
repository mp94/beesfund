<?php

declare(strict_types=1);
namespace App\Controller;

use App\Entity\Project;
use App\Entity\Reward;
use App\Repository\RewardRepository;
use App\Services\RewardService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;


class RewardController extends AbstractController
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
     * @RewardService $RewardService
     */
    private $RewardService;

    /**
     * RewardController constructor.
     * @param RewardRepository $rewardRepository
     * @param EntityManagerInterface $entityManager
     * @param RewardService $rewardService
     */
    public function __construct(
        RewardRepository $rewardRepository, EntityManagerInterface $entityManager, RewardService $rewardService
    )
    {
        $this->RewardRepository = $rewardRepository;
        $this->EntityManager = $entityManager;
        $this->RewardService = $rewardService;
    }

    /**
     * @Rest\Route("/reward", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function addReward(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $projId = $this->EntityManager->find(Project::class, $data['projectId']);
        if ($projId instanceof Project) {
            $reward = new Reward();
            $reward->setName($data['name'])
                ->setDescription($data['description'])
                ->setProjectId($projId)
                ->setAmount($data['amount']);
            try {
                $this->EntityManager->persist($reward);
                $this->EntityManager->flush();
            } catch (ORMException $e) {
                return new JsonResponse(['status' => 'error']);
            }
            return new JsonResponse(['status' => 'ok']);
        }
        return  new JsonResponse(['405'=> 'Invalid input']);
    }
}
