<?php

namespace Apiato\Core\Services;

use Apiato\Core\Abstracts\Transformers\Transformer;
use Apiato\Core\Contracts\HasResourceKey;
use Illuminate\Http\JsonResponse;
use League\Fractal\Scope;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Fractal;
use Spatie\Fractalistic\Exceptions\NoTransformerSpecified;

/**
 * A wrapper class for Spatie\Fractal\Fractal.
 *
 * @see Fractal
 */
class Response extends Fractal
{
    /**
     * Parse the Request's include query parameter and return the requested includes as model relations.
     *
     * For example, if the include query parameter is "books,children.books", this method will return:
     * ['books', 'children', 'children.books']
     */
    public static function getRequestedIncludesAsModelRelation(): array
    {
        $requestedIncludes = request()?->input(config('fractal.auto_includes.request_key'), []);

        return static::create()->manager->parseIncludes($requestedIncludes)->getRequestedIncludes();
    }

    /**
     * Create a new Response instance.
     *
     * @param mixed|null $data
     * @param callable|TransformerAbstract|string|null $transformer
     * @param SerializerAbstract|string|null $serializer
     */
    public static function create($data = null, $transformer = null, $serializer = null): static
    {
        $response = parent::create($data, $transformer, $serializer);

        $response->parseFieldsets(self::getRequestedFieldsets());

        return $response;
    }

    private static function getRequestedFieldsets(): array
    {
        $requestKey = config('apiato.requests.sparse_fieldsets.request_key');
        return request()?->input($requestKey) ?? [];
    }

    public function createData(): Scope
    {
        $this->withResourceName($this->defaultResourceName());
        $this->setAvailableIncludesMeta();

        if (is_null($this->transformer)) {
            throw new NoTransformerSpecified();
        }

        if (is_string($this->serializer)) {
            $this->serializer = new $this->serializer;
        }

        if (! is_null($this->serializer)) {
            $this->manager->setSerializer($this->serializer);
        }

        $this->manager->setRecursionLimit($this->recursionLimit);

        if (! empty($this->includes)) {
            $this->manager->parseIncludes($this->includes);
        }

        if (! empty($this->excludes)) {
            $this->manager->parseExcludes($this->excludes);
        }

        if (! empty($this->fieldsets)) {
            $this->manager->parseFieldsets($this->fieldsets);
        }

        return $this->manager->createData($this->getResource());
    }

    private function defaultResourceName(): string
    {
        if (is_string($this->getResourceName())) {
            return $this->getResourceName();
        }

        if ($this->data instanceof HasResourceKey) {
            return $this->data->getResourceKey();
        }

        if (!empty($this->data) && 'collection' === $this->determineDataType($this->data)) {
            // TODO: there was a problem $this->data->first() but I cant remember. It had to do with the data being an array
            // also check AbstractTransformer where we also do this check and use the first item. we also have the same problem there
            $firstItem = $this->data->first();
            if ($firstItem instanceof HasResourceKey) {
                return $firstItem->getResourceKey();
            }
        }

        return '';
    }

    private function setAvailableIncludesMeta(): void
    {
        $this->addMeta([
            'include' => $this->getTransformerAvailableIncludes(),
        ]);
    }

    private function getTransformerAvailableIncludes(): array
    {
        if (is_null($this->transformer) || is_callable($this->transformer)) {
            return [];
        }

        if (is_string($this->transformer)) {
            return (new $this->transformer())->getAvailableIncludes();
        }

        return $this->transformer->getAvailableIncludes();
    }

    /**
     * Returns a 202 Accepted response.
     */
    public function accepted(): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->respond(202);
    }

    public function getTransformer(): string|callable|TransformerAbstract|null
    {
        return $this->transformer;
    }

    /**
     * Returns a 201 Created response.
     */
    public function created(): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->respond(201);
    }

    /**
     * Returns a 204 No Content response.
     */
    public function noContent(): JsonResponse
    {
        $this->transformWith(Transformer::empty());

        return $this->respond(204);
    }

    /**
     * Returns a 200 OK response.
     */
    public function ok(): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->respond(200);
    }
}
