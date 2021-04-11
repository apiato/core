<?php

namespace Apiato\Core\Abstracts\Transformers;

use Apiato\Core\Exceptions\CoreInternalErrorException;
use Apiato\Core\Exceptions\UnsupportedFractalIncludeException;
use ErrorException;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract as FractalTransformer;

abstract class Transformer extends FractalTransformer
{
    /**
     * @param $adminResponse
     * @param $clientResponse
     *
     * @return  array
     */
    public function ifAdmin($adminResponse, $clientResponse): array
    {
        $user = $this->user();

        if (!is_null($user) && $user->hasAdminRole()) {
            return array_merge($clientResponse, $adminResponse);
        }

        return $clientResponse;
    }

    /**
     * @return  Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        return Auth::user();
    }

    /**
     * @param mixed $data
     * @param callable|FractalTransformer $transformer
     * @param null $resourceKey
     *
     * @return Item
     */
    public function item($data, $transformer, $resourceKey = null): Item
    {
        // set a default resource key if none is set
        if (!$resourceKey && $data) {
            $resourceKey = $data->getResourceKey();
        }

        return parent::item($data, $transformer, $resourceKey);
    }

    /**
     * @param mixed $data
     * @param callable|FractalTransformer $transformer
     * @param null $resourceKey
     *
     * @return Collection
     */
    public function collection($data, $transformer, $resourceKey = null): Collection
    {
        // set a default resource key if none is set
        if (!$resourceKey && $data->isNotEmpty()) {
            $resourceKey = (string)$data->modelKeys()[0];
        }

        return parent::collection($data, $transformer, $resourceKey);
    }

    /**
     * @param Scope $scope
     * @param string $includeName
     * @param mixed $data
     *
     * @return ResourceInterface
     * @throws CoreInternalErrorException
     * @throws UnsupportedFractalIncludeException
     */
    protected function callIncludeMethod(Scope $scope, $includeName, $data): ResourceInterface
    {
        try {
            return parent::callIncludeMethod($scope, $includeName, $data);
        } catch (ErrorException $exception) {
            if (Config::get('apiato.requests.force-valid-includes', true)) {
                throw new UnsupportedFractalIncludeException($exception->getMessage());
            }
        } catch (Exception $exception) {
            throw new CoreInternalErrorException($exception->getMessage());
        }
    }
}
