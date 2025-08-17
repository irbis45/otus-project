<?php

namespace App\Application\Core\Profile\UserCases\Queries\FetchyByUserId;

use App\Application\Core\Profile\DTO\ProfileDTO;
use App\Application\Core\User\Exceptions\UserNotFoundException;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\User;

class Fetcher
{
    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @param Query $query
     *
     * @return ProfileDTO
     * @throws UserNotFoundException
     */
    public function fetch(Query $query): ProfileDTO
    {
        /** @var ?User $user */
        $user = $this->userRepository->find($query->id);

        if (!$user) {
            throw new UserNotFoundException();
        }

        return new ProfileDTO(
            id:              $user->getId(),
            name:            $user->getName(),
            email:           $user->getEmail(),
        );
    }
}
