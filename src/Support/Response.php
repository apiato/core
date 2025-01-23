<?php

namespace Apiato\Support;

use Apiato\Abstract\Transformers\Transformer;
use Apiato\Contracts\HasResourceKey;
use Illuminate\Http\JsonResponse;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Fractal;
use Webmozart\Assert\Assert;

/**
 * A wrapper class for Spatie\Fractal\Fractal.
 *
 * @see Fractal
 */
final class Response extends Fractal
{
    /**
     * Parse the Request's include query parameter and return the requested includes as model relations.
     *
     * For example, if the include query parameter is "books,children.books", this method will return:
     * ['books', 'children', 'children.books']
     *
     * @return string[]
     */
    public static function getRequestedIncludes(): array
    {
        $requestedIncludes = request()?->input(config('fractal.auto_includes.request_key'), []);
        Assert::isArray($requestedIncludes);
        Assert::allString($requestedIncludes);

        return self::create()->manager->parseIncludes($requestedIncludes)->getRequestedIncludes();
    }

    public function createData(): Scope
    {
        $this->withResourceName($this->defaultResourceName());
        $this->setAvailableIncludesMeta();

         return parent::createData();
    }

    private function defaultResourceName(): string
    {
        if (!is_null($this->getResourceName())) {
            return $this->getResourceName();
        }

        if ($this->data instanceof HasResourceKey) {
            return $this->data->getResourceKey();
        }

        if (!empty($this->data) && 'collection' === $this->determineDataType($this->data)) {
            // TODO: there was a problem with $this->data->first() but I cant remember.
            // It had something to do with the data being an array I think.
            // Also check the AbstractTransformer where we also do this check and use the first item.
            // We also have the same problem there.
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
