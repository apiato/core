<?php

namespace Apiato\Http;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

final readonly class RequestRelation
{
    public function __construct(
        private Request $request,
    ) {
    }

    /**
     * Check if the Request has includes.
     */
    public function requestingIncludes(): bool
    {
        return $this->request->has(config('fractal.auto_includes.request_key'));
    }

    /**
     * Get the valid includes for the given model.
     * This method will filter out any invalid includes that are not relations on the model.
     *
     * @return string[]
     */
    public function getValidRelationsFor(Model $model): array
    {
        return $this->getDeepestRelations(
            ...array_filter(
                array_map(
                    static fn (string $include) => self::isValidRelationOf($model, ...explode('.', $include)),
                    $this->parseIncludes(),
                ),
            ),
        );
    }

    /**
     * @return string[]
     */
    public function getDeepestRelations(string ...$relations): array
    {
        return array_filter($relations, static function ($relation) use ($relations) {
            foreach ($relations as $otherRelation) {
                if ($relation !== $otherRelation && Str::startsWith($otherRelation, $relation . '.')) {
                    return false;
                }
            }

            return true;
        });
    }

    public static function isValidRelationOf(Model $model, string ...$relationParts): string|null
    {
        if ([] === $relationParts) {
            return null;
        }

        $relationName = self::figureOutRelationName(array_shift($relationParts));

        if (!method_exists($model, $relationName)) {
            return null;
        }

        /** @var Relation $relation */
        $relation = $model->{$relationName}();

        $nextModel = $relation->getRelated();

        if ([] === $relationParts) {
            return $relationName;
        }

        $nextRelation = self::isValidRelationOf($nextModel, ...$relationParts);

        if (!is_null($nextRelation)) {
            return $relationName . '.' . $nextRelation;
        }

        return null;
    }

    public static function figureOutRelationName(string $includeName): string
    {
        return Str::of($includeName)->camel();
    }

    /**
     * Parse the Request's include query parameter and return the requested includes as model relations.
     *
     * For example, if the include query parameter is "books,children.books", this method will return:
     * ['books', 'children', 'children.books']
     *
     * @return string[]
     */
    public function parseIncludes(): array
    {
        $requestKey = config('fractal.auto_includes.request_key');
        Assert::nullOrString($requestKey);

        $includes = $this->request->input($requestKey, []);

        if (is_array($includes)) {
            Assert::allString($includes);
        } else {
            Assert::string($includes);
        }

        return Response::create()->manager()->parseIncludes($includes)->getRequestedIncludes();
    }
}
