<?php

namespace Apiato\Core\Traits;

use Fractal;
use Request;
use ReflectionClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Pagination\AbstractPaginator;
use Apiato\Core\Abstracts\Transformers\Transformer;
use Apiato\Core\Exceptions\InvalidTransformerException;

/**
 * Class ResponseTrait
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
trait ResponseTrait
{

    /**
     * @var array
     */
    protected $metaData = [];

    /**
     * @param  $data
     * @param  null    $transformerName The transformer (e.g., Transformer::class or new Transformer()) to be applied
     * @param  array   $includes        additional resources to be included
     * @param  array   $meta            additional meta information to be applied
     * @param  null    $resourceKey     the resource key to be set for the TOP LEVEL resource
     * @return array
     */
    public function transform(
              $data,
              $transformerName = null,
        array $includes = [],
        array $meta = [],
              $resourceKey = null
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
        if (! ($transformer instanceof Transformer)) {
            throw new InvalidTransformerException();
        }

        // add specific meta information to the response message
        $this->metaData = [
            'include' => $transformer->getAvailableIncludes(),
            'custom'  => $meta,
        ];

        // no resource key was set
        if (! $resourceKey) {
            // get the resource key from the model
            $obj = null;
            if ($data instanceof AbstractPaginator) {
                $obj = $data->getCollection()->first();
            } elseif ($data instanceof Collection) {
                $obj = $data->first();
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
        if ($requestFilters = Request::get('filter')) {
            $result = $this->filterResponse($fractal->toArray(), explode(';', $requestFilters));
        } else {
            $result = $fractal->toArray();
        }

        return $result;
    }

    /**
     * @param  $data
     * @return $this
     */
    public function withMeta($data)
    {
        $this->metaData = $data;

        return $this;
    }

    /**
     * @param  $message
     * @param  int                             $status
     * @param  array                           $headers
     * @param  int                             $options
     * @return \Illuminate\Http\JsonResponse
     */
    public function json($message, $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse($message, $status, $headers, $options);
    }

    /**
     * @param  null           $message
     * @param  int            $status
     * @param  array          $headers
     * @param  int            $options
     * @return JsonResponse
     */
    public function created($message = null, $status = 201, array $headers = [], $options = 0)
    {
        return new JsonResponse($message, $status, $headers, $options);
    }

    /**
     * @param  null                            array      or string $message
     * @param  int                             $status
     * @param  array                           $headers
     * @param  int                             $options
     * @return \Illuminate\Http\JsonResponse
     */
    public function accepted($message = null, $status = 202, array $headers = [], $options = 0)
    {
        return new JsonResponse($message, $status, $headers, $options);
    }

    /**
     * @param  $responseArray
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleted($responseArray = null)
    {
        if (! $responseArray) {
            return $this->accepted();
        }

        $id        = $responseArray->getHashedKey();
        $className = (new ReflectionClass($responseArray))->getShortName();

        return $this->accepted([
            'message' => "$className ($id) Deleted Successfully.",
        ]);
    }

    /**
     * @param  int                             $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function noContent($status = 204)
    {
        return new JsonResponse(null, $status);
    }

    /**
     * @param  array   $responseArray
     * @param  array   $filters
     * @return array
     */
    private function filterResponse(array $responseArray, array $filters)
    {
        $filteredData = null;

        if ($this->array_is_associative($obj)) {
            return $this->filterObjectKeys($obj, $filters);
        }

        foreach ($obj as $key => $value) {
            array_set($filteredData, $key, $this->filterResponse($value, $filters));
        }

        return $filteredData;
    }

    /**
     * @param $array
     */
    public function array_is_associative($array)
    {
        // Create new Array,  Make it the same size as the input array
        $compareArray = array_pad([], count($array), 0);

        return (count(array_diff_key($array, $compareArray))) ? true : false;
    }

    /**
     * @param $obj
     * @param $filters
     */
    public function filterObjectKeys($obj, $filters)
    {
        $filteredData = [];

        foreach (array_dot($obj) as $key => $value) {
            foreach ($filters as $filter) {
                $keyWithWildcard = preg_replace("/\.(\d)+\./", ".*.", $key);

                if ($keyWithWildcard === $filter) {
                    array_set($filteredData, $key, $value);
                }
            }
        }

        return $filteredData;
    }

    /**
     * @return array
     */
    protected function parseRequestedIncludes(): array
    {
        return explode(',', Request::get('include'));
    }

}