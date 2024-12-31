<?php

namespace Tests\Infrastructure\Doubles;

use Apiato\Core\Abstracts\Repositories\Repository as ParentRepository;

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
