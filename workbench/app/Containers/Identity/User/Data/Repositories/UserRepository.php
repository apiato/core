<?php

namespace Workbench\App\Containers\Identity\User\Data\Repositories;

use Apiato\Abstract\Repositories\Repository as ParentRepository;
use Workbench\App\Containers\Identity\User\Models\User;

class UserRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'name' => 'ilike',
    ];

    public function model(): string
    {
        return User::class;
    }
}
