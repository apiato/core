<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\MultiWord\Data\Repositories;

use Workbench\App\Containers\MySection\MultiWord\Models\MultiWord;
use Workbench\App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of MultiWord
 *
 * @extends ParentRepository<TModel>
 */
class MultiWordRepository extends ParentRepository
{
}
