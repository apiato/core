<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Services\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @internal
 */
trait CanEagerLoadTrait
{
    /**
     * Eager load relations if requested by the client via "include" query parameter.
     * This is a workaround for incompatible third-party packages. (Fractal, L5Repo).
     *
     * @see https://apiato.atlassian.net/browse/API-905
     */
    protected function eagerLoadRequestedIncludes(): void
    {
        $this->scopeQuery(function (Builder|Model $model) {
            if (request()?->has(config('fractal.auto_includes.request_key'))) {
                $validIncludes = [];
                // TODO: Do we need to do the same for the excludes?
                // TODO: Or default includes! Are they eager loaded by default?
                // TODO: What if the include has parameters? e.g. include=books:limit(5|3)
                foreach (Response::getRequestedIncludes() as $includeName) {
                    $relationParts = explode('.', $includeName);
                    $camelCasedIncludeName = $this->filterInvalidRelations($this->model, $relationParts);
                    if ($camelCasedIncludeName) {
                        $validIncludes[] = $camelCasedIncludeName;
                    }
                }

                return $model->with($validIncludes);
            }

            return $model;
        });
    }

    // TODO: rename this method or maybe keep the name but dont return null.
    // Returning null causes multiple if() guard clauses as you can see
    private function filterInvalidRelations(Builder|Model $model, array $relationParts): string|null
    {
        if (empty($relationParts)) {
            return null;
        }

        $relation = $this->figureOutRelationName(array_shift($relationParts));

        if (!method_exists($model, $relation)) {
            return null;
        }

        $nextModel = $model->$relation()->getRelated();

        if (empty($relationParts)) {
            return $relation;
        }

        $nextRelation = $this->filterInvalidRelations($nextModel, $relationParts);

        if (is_null($nextRelation)) {
            return null;
        }

        return $relation . '.' . $nextRelation;
    }

    private function figureOutRelationName(string $includeName): string
    {
        return Str::of($includeName)
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->title()
            ->replace(' ', '')
            ->camel();
    }
}
