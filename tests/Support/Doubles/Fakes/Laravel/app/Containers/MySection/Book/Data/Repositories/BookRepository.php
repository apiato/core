<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Repositories;

use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Models\Book;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Book
 *
 * @extends ParentRepository<TModel>
 */
class BookRepository extends ParentRepository
{
    protected $fieldSearchable = [
        // 'id' => '=',
    ];
}
