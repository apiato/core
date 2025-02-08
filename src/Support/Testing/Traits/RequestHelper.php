<?php

namespace Apiato\Support\Testing\Traits;

use Illuminate\Testing\TestResponse;
use Webmozart\Assert\Assert;

trait RequestHelper
{
    /**
     * Allows users to override the default class property `endpoint` directly before calling the `makeCall` function.
     */
    protected string|null $overrideEndpoint = null;
    protected string $endpoint = '';
    protected TestResponse $response;

    private string|null $url;

    public function makeCall(array $data = [], array $headers = []): TestResponse
    {
        $endpoint = $this->parseEndpoint();
        $verb = $endpoint['verb'];
        $url = $endpoint['url'];

        Assert::oneOf($verb, ['get', 'post', 'put', 'patch', 'delete'], 'Unsupported HTTP Verb (' . $verb . ')!');

        if ('get' === $verb) {
            $url = $this->dataArrayToQueryParam($data, $url);
        }

        return $this->json($verb, $url, $data, $headers);
    }

    /**
     * read `$this->endpoint` property from the test class (`verb@uri`) and convert it to usable data.
     *
     * @return array<string, string>
     */
    public function parseEndpoint(): array
    {
        $endpoint = $this->overrideEndpoint ?? $this->endpoint;

        if (empty($endpoint)) {
            throw new \RuntimeException('No endpoint provided. Please set the `$endpoint` property in your test class.');
        }

        $parts = explode('@', (string) $endpoint);

        if (2 !== count($parts) || in_array('', $parts, true)) {
            throw new \RuntimeException('Endpoint (' . $endpoint . ') is in the wrong format. Use (`verb@uri`).');
        }

        [$verb, $uri] = $parts;

        return [
            'verb' => $verb,
            'uri' => $uri,
            'url' => $this->buildUrlForUri($uri),
        ];
    }

    private function buildUrlForUri($uri): string
    {
        $uri = apiato()->routing()->getApiPrefix() . $uri;

        return $this->getUrl() . $uri;
    }

    private function getUrl(): string
    {
        // 'API_URL' value comes from `phpunit.xml` during testing
        return $this->url ?? config('apiato.api.url');
    }

    private function dataArrayToQueryParam(array $data, string $url): string
    {
        if ([] === $data) {
            return $url;
        }

        return $url . '?' . http_build_query($data);
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
        if (config('apiato.hash-id')) {
            return hashids()->encode($id);
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
    public function endpoint(string $endpoint): static
    {
        $this->overrideEndpoint = $endpoint;

        return $this;
    }

    /**
     * Override the default url before making the call.
     */
    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
