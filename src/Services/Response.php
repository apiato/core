<?php

namespace Apiato\Core\Services;

use Apiato\Core\Contracts\HasResourceKey;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use League\Fractal\Scope;
use Spatie\Fractal\Fractal as SpatieFractal;

/**
 * A wrapper class for Spatie\Fractal\Fractal.
 *
 * @see SpatieFractal
 */
class Response extends SpatieFractal
{
    public function createData(): Scope
    {
        $this->withResourceName($this->defaultResourceName());
        $this->parseFieldsets($this->getRequestedFieldsets());
        $this->setAvailableIncludesMeta();

        return parent::createData();
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
        $fieldSets = [];
        // TODO: BREAKING CHANGE: rename the default to fieldset
        if ($requestFieldSets = Request::get(Config::get('apiato.requests.params.filter', 'filter'))) {
            foreach ($requestFieldSets as $fieldSet) {
                [$resourceName, $fields] = explode(':', $fieldSet);
                // TODO: Maybe just split by comma and remove the explode?
                //  Decide between the two ';', & ',' and stick with one
                $field = explode(';', $fields);
                $fieldSets[$resourceName] = $field;
            }
        }

        return $fieldSets;
    }
}
