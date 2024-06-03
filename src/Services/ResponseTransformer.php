<?php

namespace Apiato\Core\Services;

use Apiato\Core\Contracts\HasResourceKey;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use League\Fractal\Scope;
use Spatie\Fractal\Fractal as SpatieFractal;

/**
 * A wrapper class for Spatie\Fractal\Fractal
 *
 * @see \Spatie\Fractal\Fractal
 */
class ResponseTransformer extends SpatieFractal
{
    public function createData(): Scope
    {
        $this->withResourceName($this->defaultResourceName());
        $this->parseFieldsets($this->getRequestedFieldsets());

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

    private function getRequestedFieldsets(): array
    {
        $fieldSets = [];
        if ($filters = Request::get(Config::get('apiato.requests.params.filter', 'filter'))) {
            foreach ($filters as $filter) {
                [$resourceName, $fields] = explode(':', $filter);
                $field = explode(';', $fields);
                $fieldSets[$resourceName] = $field;
            }
        }

        return $fieldSets;
    }
}
