<?php

namespace Apiato\Core\Traits\TestTraits\PhpUnit;

use Apiato\Core\Exceptions\MissingTestEndpointException;
use Apiato\Core\Exceptions\UndefinedMethodException;
use Apiato\Core\Exceptions\WrongEndpointFormatException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Vinkla\Hashids\Facades\Hashids;

trait TestRequestHelperTrait
{
    /**
     * property to be set on the user test class.
     */
    protected string $endpoint = '';

    /**
     * property to be set on the user test class.
     */
    protected bool $auth = true;

    protected TestResponse $response;

    protected string $responseContent;

    protected array|null $responseContentArray = null;

    protected \stdClass|null $responseContentObject = null;

    /**
     * Allows users to override the default class property `endpoint` directly before calling the `makeCall` function.
     */
    protected string|null $overrideEndpoint = null;

    /**
     * Allows users to override the default class property `auth` directly before calling the `makeCall` function.
     */
    protected bool|null $overrideAuth = null;

    /**
     * @throws WrongEndpointFormatException
     * @throws MissingTestEndpointException
     * @throws UndefinedMethodException
     */
    public function makeCall(array $data = [], array $headers = []): TestResponse
    {
        // Get or create a testing user. It will get your existing user if you already called this function from your
        // test. Or create one if you never called this function from your tests "Only if the endpoint is protected".
        $this->getTestingUser();

        // read the $endpoint property from the test and set the verb and the uri as properties on this trait
        $endpoint = $this->parseEndpoint();
        $verb = $endpoint['verb'];
        $url = $endpoint['url'];

        // validating user http verb input + converting `get` data to query parameter
        switch ($verb) {
            case 'get':
                $url = $this->dataArrayToQueryParam($data, $url);
                break;
            case 'post':
            case 'put':
            case 'patch':
            case 'delete':
                break;
            default:
                throw new UndefinedMethodException('Unsupported HTTP Verb (' . $verb . ')!');
        }

        $httpResponse = $this->json($verb, $url, $data, $this->injectAccessToken($headers));

        return $this->setResponseObjectAndContent($httpResponse);
    }

    /**
     * read `$this->endpoint` property from the test class (`verb@uri`) and convert it to usable data.
     * @return array<string, string>
     *
     * @throws WrongEndpointFormatException
     * @throws MissingTestEndpointException
     */
    private function parseEndpoint(): array
    {
        $this->validateEndpointExist();

        $separator = '@';

        $this->validateEndpointFormat($separator);

        $asArray = explode($separator, $this->getEndpoint(), 2);

        // get the verb and uri values from the array
        extract(array_combine(['verb', 'uri'], $asArray));

        /* @var string $verb */
        /* @var string $uri */
        return [
            'verb' => $verb,
            'uri' => $uri,
            'url' => $this->buildUrlForUri($uri),
        ];
    }

    /**
     * @throws MissingTestEndpointException
     */
    private function validateEndpointExist(): void
    {
        if (!$this->getEndpoint()) {
            throw new MissingTestEndpointException();
        }
    }

    public function getEndpoint(): string
    {
        if (!is_null($this->overrideEndpoint)) {
            return $this->overrideEndpoint;
        }

        return $this->endpoint;
    }

    /**
     * @throws WrongEndpointFormatException
     */
    private function validateEndpointFormat(string $separator): void
    {
        if (!strpos($this->getEndpoint(), $separator)) {
            throw new WrongEndpointFormatException();
        }
    }

    private function buildUrlForUri($uri): string
    {
        $uri = Config::get('apiato.api.prefix') . $uri;

        if (!Str::startsWith($uri, '/')) {
            $uri = '/' . $uri;
        }

        return Config::get('apiato.api.url') . $uri;
    }

    private function dataArrayToQueryParam(array $data, string $url): string
    {
        if (empty($data)) {
            return $url;
        }

        return $url . '?' . http_build_query($data);
    }

    /**
     * Attach Authorization Bearer Token to the request headers
     * if it does not exist already and the authentication is required
     * for the endpoint, e.g., `$this->auth = true`.
     */
    private function injectAccessToken(array $headers = []): array
    {
        // if endpoint is protected (requires token to access its functionality)
        if ($this->getAuth() && !$this->headersContainAuthorization($headers)) {
            // create token
            $accessToken = $this->getTestingUser()->createToken('token')->accessToken;
            // give it to user
            $this->getTestingUser()->withAccessToken($accessToken);
            // append the token to the header
            $headers['Authorization'] = 'Bearer ' . $accessToken;
        }

        return $headers;
    }

    public function getAuth(): bool
    {
        if (is_null($this->overrideAuth)) {
            return $this->auth;
        }

        return $this->overrideAuth;
    }

    private function headersContainAuthorization($headers): bool
    {
        return Arr::has($headers, 'Authorization');
    }

    public function setResponseObjectAndContent(TestResponse $httpResponse): TestResponse
    {
        $this->setResponseContent($httpResponse);

        return $this->response = $httpResponse;
    }

    /**
     * @throws \JsonException
     */
    public function getResponseContentArray()
    {
        if ($this->responseContentArray) {
            return $this->responseContentArray;
        }

        return $this->responseContentArray = json_decode($this->getResponseContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getResponseContent(): string
    {
        return $this->responseContent;
    }

    // TODO: @next - add return type
    public function setResponseContent(TestResponse $httpResponse)
    {
        return $this->responseContent = $httpResponse->getContent();
    }

    /**
     * @throws \JsonException
     */
    public function getResponseContentObject()
    {
        if ($this->responseContentObject) {
            return $this->responseContentObject;
        }

        return $this->responseContentObject = json_decode($this->getResponseContent(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Inject the ID in the Endpoint URI before making the call by
     * overriding the `$this->endpoint` property.
     */
    public function injectId($id, bool $skipEncoding = false, string $replace = '{id}'): static
    {
        if (!$skipEncoding) {
            $id = $this->hashIdIfEnabled($id);
        }

        if (is_null($this->overrideEndpoint)) {
            $this->endpoint = str_replace($replace, $id, $this->endpoint);
        } else {
            $this->overrideEndpoint = str_replace($replace, $id, $this->overrideEndpoint);
        }

        return $this;
    }

    private function hashIdIfEnabled($id): string
    {
        if (Config::get('apiato.hash-id')) {
            return Hashids::encode($id);
        }

        return $id;
    }

    /**
     * Override the default class endpoint property before making the call.
     *
     * Note: The order in which you call this function is crucial.
     * Make sure to call it before injectId(),
     * or else injectId() will not replace the ID in the overridden endpoint.
     */
    // TODO: @next - add $endpoint parameter type
    public function endpoint($endpoint): static
    {
        $this->overrideEndpoint = $endpoint;

        return $this;
    }

    /**
     * Override the default class auth property before making the call.
     */
    public function auth(bool $auth): static
    {
        $this->overrideAuth = $auth;

        return $this;
    }

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     */
    protected function transformHeadersToServerVars(array $headers): array
    {
        return collect($headers)->mapWithKeys(function ($value, $name) {
            $name = str_replace('-', '_', strtoupper($name));

            return [$this->formatServerHeaderKey($name) => $value];
        })->all();
    }
}
