<?php

namespace Apiato\Core\Services;

use Apiato\Core\Contracts\HasResourceKey;
use Illuminate\Support\Facades\Request;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Fractal as SpatieFractal;

class Fractal extends SpatieFractal
{
    public function createData(): Scope
    {
        $this->withResourceName($this->defaultResourceName());
        $this->parseFieldsets($this->getRequestFieldsets());

        return parent::createData();
    }

    private function defaultResourceName(): string
    {
        if ($this->data instanceof HasResourceKey) {
            return $this->data->getResourceKey();
        }

        if (!empty($this->data) && 'collection' === $this->determineDataType($this->data)) {
            $firstItem = $this->data->first();
            if ($firstItem instanceof HasResourceKey) {
                return $firstItem->getResourceKey();
            }
        }

        return '';
    }

    private function getRequestFieldsets(): array
    {
        $fieldSets = [];
        if ($filters = Request::get('filter')) {
            foreach ($filters as $filter) {
                [$resourceName, $fields] = explode(':', $filter);
                $field = explode(';', $fields);
                $fieldSets[$resourceName] = $field;
            }
        }

        return $fieldSets;
    }

    public static function getRequestedIncludes(): array
    {
        return app(Manager::class)->parseIncludes(request('include', []))->getRequestedIncludes();
    }

    public function getTransformer(): string|callable|TransformerAbstract|null
    {
        return $this->transformer;
    }

    public function emptyTransformer(): callable
    {
        return static fn (): array => [];
    }
}
