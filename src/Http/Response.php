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
 * This class extends the functionality of the Fractal library to provide
 * additional features such as resource key defaults, meta information for
 * available includes, and helper methods for creating JSON responses.
 *
 * @see Fractal
 */
class Response extends Fractal
{
    /**
     * Get the Fractal Manager instance.
     */
    public function manager(): Manager
    {
        return $this->manager;
    }

    /**
     * Create a Fractal Scope instance with the current resource and meta information.
     */
    public function createData(): Scope
    {
        $this->withResourceName($this->resourceKeyOrDefault());
        $this->setAvailableIncludesMeta();

        return parent::createData();
    }

    /**
     * Get the resource key or a default value if none is set.
     */
    private function resourceKeyOrDefault(): string
    {
        $resourceName = $this->getResourceName();
        if (is_null($resourceName)) {
            return $this->getResource()->getResourceKey();
        }

        return $resourceName;
    }

    /**
     * Add meta information about available includes to the response.
     */
    private function setAvailableIncludesMeta(): void
    {
        $this->addMeta([
            'include' => $this->getTransformerAvailableIncludes(),
        ]);
    }

    /**
     * Get the available includes of the transformer.
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

    /**
     * Get the resource class based on the data type.
     */
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

    /**
     * Convert the response data to an array.
     */
    public function toArray(): array
    {
        return $this->createData()->toArray() ?? [];
    }

    /**
     * Create a new JSON response.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array<string, mixed>  $headers
     * @param  int  $options
     *
     * @return JsonResponse
     */
    public function json(mixed $data = null, int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        if (is_null($data) && !is_null($this->data)) {
            return $this->respond($status, $headers, $options);
        }

        return new JsonResponse($data, $status, $headers, $options);
    }

    /**
     * Return a 202 "Accepted" response.
     *
     * @param  mixed  $data
     * @param  array<string, mixed>  $headers
     * @param  int  $options
     *
     * @return JsonResponse
     */
    public function accepted(mixed $data = null, array $headers = [], int $options = 0): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->json($data, 202, $headers, $options);
    }

    /**
     * Return a 201 "Created" response.
     *
     * @param  mixed  $data
     * @param  array<string, mixed>  $headers
     * @param  int  $options
     *
     * @return JsonResponse
     */
    public function created(mixed $data = null, array $headers = [], int $options = 0): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->json($data, 201, $headers, $options);
    }

    /**
     * Return a 200 "OK" response.
     *
     * @param  mixed  $data
     * @param  array<string, mixed>  $headers
     * @param  int  $options
     *
     * @return JsonResponse
     */
    public function ok(mixed $data = null, array $headers = [], int $options = 0): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->json($data, 200, $headers, $options);
    }

    /**
     * Return a 204 "No Content" response.
     *
     * @param  array<string, mixed>  $headers
     * @param  int  $options
     *
     * @return JsonResponse
     */
    public function noContent(array $headers = [], int $options = 0): JsonResponse
    {
        $this->transformWith(Transformer::empty());

        return new JsonResponse(null, 204, $headers, $options);
    }
}
