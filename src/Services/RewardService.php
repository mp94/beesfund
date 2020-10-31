<?php

declare(strict_types=1);

namespace App\Services;


use App\Entity\Project;
use App\Entity\Reward;


class RewardService
{
    /**
     * @param $data
     * @param Project $projId
     * @return Reward
     */
    public function prepareReward($data, Project $projId) {
        $reward = new Reward();
        $reward->setName($data['name'])
            ->setDescription($data['description'])
            ->setProjectId($projId)
            ->setAmount($data['amount']);
        return $reward;
    }



}