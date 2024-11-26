<?php

namespace Apiato\Core\Services;

use Apiato\Core\Abstracts\Transformers\Transformer;
use Apiato\Core\Contracts\HasResourceKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use League\Fractal\Scope;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Fractal;

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
        $requestedIncludes = Request::get(Config::get('apiato.requests.params.include', 'include'), []);

        return static::create()->manager->parseIncludes($requestedIncludes)->getRequestedIncludes();
    }

    /**
     * Create a new Response instance.
     *
     * @param null|mixed $data
     * @param callable|TransformerAbstract|null|string $transformer
     * @param SerializerAbstract|null|string $serializer
     *
     * @return static
     */
    public static function create($data = null, $transformer = null, $serializer = null): static
    {
        return parent::create($data, $transformer, $serializer);
    }

    public function createData(): Scope
    {
        $this->withResourceName($this->defaultResourceName());
        $this->parseFieldsets($this->getRequestedFieldsets());
        $this->setAvailableIncludesMeta();

        return parent::createData();
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

    private function getRequestedFieldsets(): array
    {
        // TODO: BREAKING CHANGE: rename the default to "fields"
        return Request::get(Config::get('apiato.requests.params.filter', 'filter')) ?? [];
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
