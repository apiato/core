<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Book\Data\Repositories;

use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Ship\Parents\Repositories\Repository as ParentRepository;

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
