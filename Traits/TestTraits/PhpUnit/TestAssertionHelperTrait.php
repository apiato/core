<?php

namespace Apiato\Core\Traits\TestTraits\PhpUnit;

use Apiato\Core\Abstracts\Models\Model;
use Illuminate\Support\Collection;

trait TestAssertionHelperTrait
{
    /**
     * Assert that the model casts field is empty.
     * By default, the model casts will have the 'id' field as 'int'.
     * This method will exclude this field from the assertion.
     * If you want to add more fields, you can pass them as an array.
     */
    public function assertModelCastsIsEmpty(Model $model, array ...$extraDefaultField): void
    {
        $defaultCasts = [
            'id' => 'int',
        ];

        $casts = [...$defaultCasts, ...$extraDefaultField];

        $this->assertEmpty(array_diff($model->getCasts(), $casts));
    }

    /**
     * Check if the given id is in the given model collection by comparing hashed ids.
     * @param $id
     * @param Collection $collection
     * @return bool
     */
    public function inIds($id, Collection $collection): bool
    {
        return in_array($id, $collection->map(fn ($item) => $item->getHashedKey())->toArray());
    }
}
