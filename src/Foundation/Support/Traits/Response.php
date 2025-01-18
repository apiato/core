<?php

namespace Apiato\Foundation\Support\Traits;

use Apiato\Abstract\Models\Model;
use Apiato\Abstract\Transformers\Transformer;
use Apiato\Foundation\Exceptions\InvalidTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Spatie\Fractal\Facades\Fractal;

// TODO: Remove this after migrating everything to use Response facade
trait Response
{
    protected array $metaData = [];

    /**
     * @throws InvalidTransformer
     */
    public function transform(
        $data,
        $transformerName = null,
        array $includes = [],
        array $meta = [],
        $resourceKey = null,
    ) {
        // first, we need to create the transformer
        if ($transformerName instanceof Transformer) {
            // check, if we have provided a respective TRANSFORMER class
            $transformer = $transformerName;
        } else {
            // of if we just passed the classname
            $transformer = new $transformerName();
        }

        // now, finally check, if the class is really a TRANSFORMER
        if (!($transformer instanceof Transformer)) {
            throw new InvalidTransformer();
        }

        // add specific meta information to the response message
        $this->metaData = array_merge($this->metaData, [
            'include' => $transformer->getAvailableIncludes(),
            'custom' => $meta,
        ]);

        // no resource key was set
        if (!$resourceKey) {
            // get the resource key from the model
            $obj = null;
            if ($data instanceof AbstractPaginator) {
                $obj = $data->getCollection()->first();
            } elseif ($data instanceof Collection) {
                $obj = $data->first();
            } elseif (is_array($data) && [] !== $data) {
                $obj = $data[0];
            } else {
                $obj = $data;
            }

            // if we have an object, try to get its resourceKey
            if ($obj) {
                $resourceKey = $obj->getResourceKey();
            }
        }

        $fractal = Fractal::create($data, $transformer)->withResourceName($resourceKey)->addMeta($this->metaData);

        // read includes passed via query params in url
        $requestIncludes = $this->parseRequestedIncludes();

        // merge the requested includes with the one added by the transform() method itself
        $requestIncludes = array_unique(array_merge($includes, $requestIncludes));

        // and let fractal include everything
        $fractal->parseIncludes($requestIncludes);

        // apply request filters if available in the request
        if ($requestFilters = request()?->input(config('apiato.requests.params.filter', 'filter'))) {
            $result = $this->filterResponse($fractal->toArray(), explode(';', (string) $requestFilters));
        } else {
            $result = $fractal->toArray();
        }

        return $result;
    }

    protected function parseRequestedIncludes(): array
    {
        return explode(',', request()?->input('include') ?? '');
    }

    private function filterResponse(array $responseArray, array $filters): array
    {
        foreach ($responseArray as $k => $v) {
            if (in_array($k, $filters, true)) {
                // we have found our element - so continue with the next one
                continue;
            }

            if (is_array($v)) {
                // it is an array - so go one step deeper
                $v = $this->filterResponse($v, $filters);
                if (empty($v)) {
                    // it is an empty array - delete the key as well
                    unset($responseArray[$k]);
                } else {
                    $responseArray[$k] = $v;
                }
            } elseif (!in_array($k, $filters)) {
                // check if the array is not in our filter-list
                unset($responseArray[$k]);
            }
        }

        return $responseArray;
    }

    public function withMeta($data): self
    {
        $this->metaData = $data;

        return $this;
    }

    public function json($data, $status = 200, array $headers = [], $options = 0): JsonResponse
    {
        return new JsonResponse($data, $status, $headers, $options);
    }

    public function created($data = null, $status = 201, array $headers = [], $options = 0): JsonResponse
    {
        return new JsonResponse($data, $status, $headers, $options);
    }

    public function deleted(Model|null $deletedModel = null): JsonResponse
    {
        if (!$deletedModel instanceof Model) {
            return $this->accepted();
        }

        $id = $deletedModel->getHashedKey();
        $className = (new \ReflectionClass($deletedModel))->getShortName();

        return $this->accepted([
            'message' => "$className ($id) Deleted Successfully.",
        ]);
    }

    public function accepted($data = null, $status = 202, array $headers = [], $options = 0): JsonResponse
    {
        return new JsonResponse($data, $status, $headers, $options);
    }

    public function noContent($status = 204): JsonResponse
    {
        return new JsonResponse(null, $status);
    }
}
