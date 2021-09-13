<?php

namespace Apiato\Core\Traits\TestsTraits\PhpUnit;

use Apiato\Core\Exceptions\MissingTestEndpointException;
use Apiato\Core\Exceptions\UndefinedMethodException;
use Apiato\Core\Exceptions\WrongEndpointFormatException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use stdClass;
use Vinkla\Hashids\Facades\Hashids;

trait TestsRequestHelperTrait
{
    /**
     * property to be set on the user test class
     */
    protected string $endpoint = '';

    /**
     * property to be set on the user test class
     */
    protected bool $auth = true;

    protected TestResponse $response;

    protected string $responseContent;

    protected ?array $responseContentArray = null;

    protected ?stdClass $responseContentObject = null;

    /**
     * Allows users to override the default class property `endpoint` directly before calling the `makeCall` function.
     */
    protected ?string $overrideEndpoint = null;

    /**
     * Allows users to override the default class property `auth` directly before calling the `makeCall` function.
     */
    protected ?bool $overrideAuth = null;

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

        $this->logResponseData($httpResponse);

        return $this->setResponseObjectAndContent($httpResponse);
    }

    /**
     * read `$this->endpoint` property from the test class (`verb@uri`) and convert it to usable data
     * @throws WrongEndpointFormatException
     * @throws MissingTestEndpointException
     */
    private function parseEndpoint(): array
    {
        $this->validateEndpointExist();

        $separator = '@';

        $this->validateEndpointFormat($separator);

        // convert the string to array
        $asArray = explode($separator, $this->getEndpoint(), 2);

        // get the verb and uri values from the array
        extract(array_combine(['verb', 'uri'], $asArray));

        /** @var string $verb */
        /** @var string $uri */
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
        return !is_null($this->overrideEndpoint) ? $this->overrideEndpoint : $this->endpoint;
    }

    /**
     * @throws WrongEndpointFormatException
     */
    private function validateEndpointFormat($separator): void
    {
        // check if string contains the separator
        if (!strpos($this->getEndpoint(), $separator)) {
            throw new WrongEndpointFormatException();
        }
    }

    private function buildUrlForUri($uri): string
    {
        // add `/` at the beginning in case it doesn't exist
        if (!Str::startsWith($uri, '/')) {
            $uri = '/' . $uri;
        }

        return Config::get('apiato.api.url') . $uri;
    }

    private function dataArrayToQueryParam($data, $url): string
    {
        return $data ? $url . '?' . http_build_query($data) : $url;
    }

    /**
     * Attach Authorization Bearer Token to the request headers
     * if it does not exist already and the authentication is required
     * for the endpoint `$this->auth = true`.
     *
     * @param array $headers
     *
     * @return array
     */
    private function injectAccessToken(array $headers = []): array
    {
        // if endpoint is protected (requires token to access its functionality)
        if ($this->getAuth() && !$this->headersContainAuthorization($headers)) {
            // append the token to the header
            $headers['Authorization'] = 'Bearer ' . $this->getTestingUser()->token();
        }

        return $headers;
    }

    public function getAuth(): bool
    {
        return !is_null($this->overrideAuth) ? $this->overrideAuth : $this->auth;
    }

    private function headersContainAuthorization($headers): bool
    {
        return Arr::has($headers, 'Authorization');
    }

    private function logResponseData($httpResponse): void
    {
        $responseLoggerEnabled = Config::get('debugger.tests.response_logger');

        if ($responseLoggerEnabled) {
            Log::notice((string)get_object_vars($httpResponse->getData()));
        }
    }

    public function setResponseObjectAndContent($httpResponse)
    {
        $this->setResponseContent($httpResponse);
        return $this->response = $httpResponse;
    }

    public function getResponseContentArray()
    {
        return $this->responseContentArray ?: $this->responseContentArray = json_decode($this->getResponseContent(), true);
    }

    public function getResponseContent(): string
    {
        return $this->responseContent;
    }

    public function setResponseContent($httpResponse)
    {
        return $this->responseContent = $httpResponse->getContent();
    }

    /**
     * @throws \JsonException
     */
    public function getResponseContentObject()
    {
        return $this->responseContentObject ?: $this->responseContentObject = json_decode($this->getResponseContent(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Inject the ID in the Endpoint URI before making the call by
     * overriding the `$this->endpoint` property
     *
     * Example: you give it ('users/{id}/stores', 100) it returns 'users/100/stores'
     *
     * @param        $id
     * @param bool $skipEncoding
     * @param string $replace
     *
     * @return  $this
     */
    public function injectId($id, bool $skipEncoding = false, string $replace = '{id}'): static
    {
        // In case Hash ID is enabled it will encode the ID first
        $id = $this->hashEndpointId($id, $skipEncoding);
        $this->endpoint = str_replace($replace, $id, $this->endpoint);

        return $this;
    }

    private function hashEndpointId($id, $skipEncoding = false): string
    {
        return (Config::get('apiato.hash-id') && !$skipEncoding) ? Hashids::encode($id) : $id;
    }

    /**
     * Override the default class endpoint property before making the call
     *
     * to be used as follow: $this->endpoint('verb@uri')->makeCall($data);
     *
     * @param $endpoint
     *
     * @return  $this
     */
    public function endpoint($endpoint): static
    {
        $this->overrideEndpoint = $endpoint;

        return $this;
    }

    /**
     * Override the default class auth property before making the call
     *
     * to be used as follows: $this->auth('false')->makeCall($data);
     *
     * @param bool $auth
     *
     * @return  $this
     */
    public function auth(bool $auth): static
    {
        $this->overrideAuth = $auth;

        return $this;
    }

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     *
     * @param array $headers
     *
     * @return array
     */
    protected function transformHeadersToServerVars(array $headers): array
    {
        return collect($headers)->mapWithKeys(function ($value, $name) {
            $name = str_replace('-', '_', strtoupper($name));

            return [$this->formatServerHeaderKey($name) => $value];
        })->all();
    }

    private function getJsonVerb($text): string
    {
        return Str::replaceFirst('json:', '', $text);
    }
}
