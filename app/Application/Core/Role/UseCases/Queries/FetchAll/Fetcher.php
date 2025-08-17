<?php

declare(strict_types=1);

namespace App\Application\Core\Role\UseCases\Queries\FetchAll;

use App\Application\Core\Role\DTO\RoleDTO;
use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\Role\DTO\ResultDTO;
use App\Models\Role;

class Fetcher
{
    /**
     * @param RoleRepositoryInterface $roleRepository
     */
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    /**
     *
     * @return ResultDTO
     */
    public function fetch(): ResultDTO
    {
        $roles = $this->roleRepository->fetchAll();

        $roleDTOs = array_map(function (Role $role) {

            return new RoleDTO(
                id:              $role->getId(),
                name:            $role->getName(),
                slug:           $role->getSlug(),
            );
        }, $roles);

        return new ResultDTO($roleDTOs);
    }
}
