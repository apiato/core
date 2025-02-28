<?php

namespace Apiato\Http;

use Apiato\Abstract\Transformers\Transformer;
use Apiato\Http\Resources\Collection;
use Apiato\Http\Resources\Item;
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
        $requestKey = config('fractal.auto_includes.request_key');
        Assert::nullOrString($requestKey);
        $includes = request()->input($requestKey, []);

        if (is_array($includes)) {
            Assert::allString($includes);
        } else {
            Assert::string($includes);
        }

        return self::create()->manager->parseIncludes($includes)->getRequestedIncludes();
    }

    public function createData(): Scope
    {
        $this->withResourceName($this->resourceKeyOrDefault());
        $this->setAvailableIncludesMeta();

        return parent::createData();
    }

    public function getResourceClass(): string
    {
        if ('item' === $this->dataType) {
            return Item::class;
        }

        if ('collection' === $this->dataType) {
            return Collection::class;
        }

        return parent::getResourceClass();
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

    /**
     * Returns a "202 - Accepted" response.
     */
    public function accepted(): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->respond(202);
    }

    /**
     * Returns a "201 - Created" response.
     */
    public function created(): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->respond(201);
    }

    /**
     * Returns a "204 - No Content" response.
     */
    public function noContent(): JsonResponse
    {
        $this->transformWith(Transformer::empty());

        return $this->respond(204);
    }

    /**
     * Returns a "200 - OK" response.
     */
    public function ok(): JsonResponse
    {
        if (is_null($this->getTransformer())) {
            $this->transformWith(Transformer::empty());
        }

        return $this->respond(200);
    }
}
