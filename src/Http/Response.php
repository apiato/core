<?php

namespace Apiato\Http;

use Apiato\Core\Transformers\Transformer;
use Apiato\Http\Resources\Collection;
use Apiato\Http\Resources\Item;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Fractal;
use Webmozart\Assert\Assert;

/**
 * A wrapper class for Spatie\Fractal\Fractal.
 *
 * @see Fractal
 */
class Response extends Fractal
{
    public function manager(): Manager
    {
        return $this->manager;
    }

    public function createData(): Scope
    {
        $this->withResourceName($this->resourceKeyOrDefault());
        $this->setAvailableIncludesMeta();

        return parent::createData();
    }

    private function resourceKeyOrDefault(): string
    {
        $resourceName = $this->getResourceName();
        if (is_null($resourceName)) {
            return $this->getResource()->getResourceKey();
        }

        return $resourceName;
    }

    private function setAvailableIncludesMeta(): void
    {
        $this->addMeta([
            'include' => $this->getTransformerAvailableIncludes(),
        ]);
    }

    /**
     * Returns the available includes of the transformer.
     *
     * @return string[]
     */
    private function getTransformerAvailableIncludes(): array
    {
        if (is_null($this->transformer) || is_callable($this->transformer)) {
            return [];
        }

        $includes = null;

        if (is_string($this->transformer)) {
            Assert::subclassOf($this->transformer, TransformerAbstract::class);

            $includes = (new $this->transformer())->getAvailableIncludes();
        }

        if ($this->transformer instanceof TransformerAbstract) {
            $includes = $this->transformer->getAvailableIncludes();
        }

        Assert::allString($includes);

        return $includes;
    }

    public function getResourceClass(): string
    {
        $this->dataType = $this->determineDataType($this->data);

        if ('item' === $this->dataType) {
            return Item::class;
        }

        if ('collection' === $this->dataType) {
            return Collection::class;
        }

        return parent::getResourceClass();
    }

    public function toArray(): array
    {
        return $this->createData()->toArray() ?? [];
    }

    /**
     * Create a new JSON response instance.
     *
     * @param array<string, mixed> $headers
     */
    public function json(mixed $data = null, int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        if (is_null($data) && !is_null($this->data)) {
            return $this->respond($status, $headers, $options);
        }

        return new JsonResponse($data, $status, $headers, $options);
    }

    /**
     * Returns a "202 - Accepted" response.
     *
     * @param array<string, mixed> $headers
     */
    public function accepted(mixed $data = null, array $headers = [], int $options = 0): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->json($data, 202, $headers, $options);
    }

    /**
     * Returns a "201 - Created" response.
     *
     * @param array<string, mixed> $headers
     */
    public function created(mixed $data = null, array $headers = [], int $options = 0): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->json($data, 201, $headers, $options);
    }

    /**
     * Returns a "200 - OK" response.
     *
     * @param array<string, mixed> $headers
     * */
    public function ok(mixed $data = null, array $headers = [], int $options = 0): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->json($data, 200, $headers, $options);
    }

    /**
     * Returns a "204 - No Content" response.
     *
     * @param array<string, mixed> $headers
     */
    public function noContent(array $headers = [], int $options = 0): JsonResponse
    {
        $this->transformWith(Transformer::empty());

        return new JsonResponse(null, 204, $headers, $options);
    }
}
