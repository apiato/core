<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Services\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

trait CanEagerLoadTrait {
    /**
     * Eager load relations if requested by the client via ?include=... in the URL.
     * This is a workaround for incompatible third-party packages. (Fractal, L5Repo)
     *
     * @link https://apiato.atlassian.net/browse/API-905
     */
    protected function eagerLoadRequestedRelations(): void
    {
        $this->scopeQuery(function (Builder|Model $model) {
            if (Request::has(config('apiato.requests.params.include', 'include'))) {
                $validIncludes = [];
                foreach (Response::getRequestedIncludes() as $includeName) {
                    $relationParts = explode('.', $includeName);
                    $camelCasedIncludeName = $this->validateNestedRelations($this->model, $relationParts);
                    if ($camelCasedIncludeName) {
                        $validIncludes[] = $camelCasedIncludeName;
                    }
                }

                return $model->with($validIncludes);
            }

            return $model;
        });
    }

    private function validateNestedRelations(Builder|Model $model, array $relationParts): string|null
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

        $nextRelation = $this->validateNestedRelations($nextModel, $relationParts);

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
