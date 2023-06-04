<?php

namespace Apiato\Core\Traits\TestTraits\PhpUnit;

use Apiato\Core\Abstracts\Models\Model;
use Apiato\Core\Abstracts\Requests\Request;
use Apiato\Core\Exceptions\CoreInternalErrorException;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

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

        foreach ($extraDefaultField as $field) {
            $defaultCasts = array_merge($defaultCasts, $field);
        }

        $this->assertEmpty(array_diff($model->getCasts(), $defaultCasts));
    }

    /**
     * Assert if the request authorize method calls the correct policy and the correct policy method with right parameters in the right order.
     * @param Request $request
     * @param string $policy
     * @param string $policyMethodName
     * @param array $policyMethodParameters Array elements can be a primitive type (e.g. NULL, boolean, integer, etc...) or an object. If it's an object, it will check if the parameter is an instance of that object.
     * @throws \Throwable
     */
    public function assertRequestAuthorizeCallsPolicyCorrectly(Request $request, string $policy, string $policyMethodName, array $policyMethodParameters): void
    {
        throw_if(!method_exists($this, 'authorize'),CoreInternalErrorException::class, 'The request does not have an authorize method.');

        $policyMock = $this->mock($policy, function (MockInterface $mock) use ($policyMethodName, $policyMethodParameters) {
            $mock->shouldReceive($policyMethodName)
                ->once()
                ->withArgs(function (...$parameters) use ($policyMethodParameters) {
                    if (count($parameters) !== count($policyMethodParameters)) {
                        return false;
                    }
                    for ($i = 0; $i < count($parameters); $i++) {
                        $parameterType = gettype($parameters[$i]);
                        if ($parameterType === 'object') {
                            if (!($parameters[$i]->is($policyMethodParameters[$i]))) {
                                return false;
                            }
                        } else {
                            if (!($parameterType == $policyMethodParameters[$i])) {
                                return false;
                            }
                        }
                    }
                    return true;
                })->andReturn(false);
        });

        $request->authorize($policyMock);
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
