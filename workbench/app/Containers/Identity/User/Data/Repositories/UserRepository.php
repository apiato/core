<?php

declare(strict_types=1);

namespace Workbench\App\Containers\Identity\User\Data\Repositories;

use Apiato\Core\Repositories\Repository as ParentRepository;
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
