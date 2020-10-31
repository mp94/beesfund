<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Project;

class HelperService
{
    /**
     * @param array $rewards
     * @return array
     */
    public function prepareRewardsData(array $rewards) {
        $rewardsArr = [];
        foreach ($rewards as $row) {
            $rewardsArr[] = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'description' => $row->getDescription(),
                'amount' => $row->getAmount()
            ];
        }
        return $rewardsArr;
    }

    /**
     * @param Project $project
     * @return array
     */
    public function prepareProjectData(Project $project) {
        $rewards = $this->prepareRewardsData($project->getRewards()->toArray());
        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'status' => $project->getStatus(),
            'rewards' => $rewards
        ];
    }
}