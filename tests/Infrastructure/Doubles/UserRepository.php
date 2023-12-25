<?php

namespace Apiato\Core\Tests\Infrastructure\Doubles;

use Apiato\Core\Abstracts\Repositories\Repository;

class UserRepository extends Repository
{
    protected $fieldSearchable = [
        'name' => 'ilike',
    ];

    public function model(): string
    {
        return User::class;
    }
}
