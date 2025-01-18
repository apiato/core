<?php

namespace Apiato\Foundation\Configuration;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

final class Routing
{
    protected static \Closure $apiVersionResolver;
    /** @var string[] */
    private array $apiRouteDirs = [];
    /** @var string[] */
    private array $webRouteDirs = [];
    private string $apiPrefix = '/';
    private bool $apiVersionAutoPrefix = true;

    public function __construct()
    {
        $this->resolveApiVersionUsing(
            static function (string $file): string {
                return Str::of($file)
                    ->before('.php')
                    ->betweenFirst('.', '.')
                    ->value();
            },
        );
    }

    public function resolveApiVersionUsing(\Closure $callback): self
    {
        self::$apiVersionResolver = $callback;

        return $this;
    }

    public function loadApiRoutesFrom(string ...$path): self
    {
        $this->apiRouteDirs = $path;

        return $this;
    }

    public function loadWebRoutesFrom(string ...$path): self
    {
        $this->webRouteDirs = $path;

        return $this;
    }

    public function registerApiRoutes(): void
    {
        collect($this->apiRouteDirs)
            ->flatMap(static fn ($path) => \Safe\glob($path . '/*.php'))
            ->each(
                function (string $file) {
                    return Route::middleware($this->getApiMiddlewares())
                        ->domain(config('apiato.api.url'))
                        ->prefix($this->buildApiPrefixFor($file))
                        ->group($file);
                },
            );
    }

    /**
     * @return string[]
     */
    private function getApiMiddlewares(): array
    {
        $middlewares = ['api'];
        if (config('apiato.api.rate-limiter.enabled')) {
            $middlewares[] = 'throttle:' . config('apiato.api.rate-limiter.name');
        }

        return $middlewares;
    }

    private function buildApiPrefixFor(string $file): string
    {
        if ($this->apiVersionAutoPrefix) {
            return $this->apiPrefix . $this->resolveApiVersionFor($file);
        }

        return $this->apiPrefix;
    }

    private function resolveApiVersionFor(string $file): string
    {
        return app()->call(self::$apiVersionResolver, compact('file'));
    }

    public function getApiPrefix(): string
    {
        return $this->apiPrefix;
    }

    public function disableApiVersionAutoPrefix(): self
    {
        $this->apiVersionAutoPrefix = false;

        return $this;
    }

    public function prefixApiUrlsWith(string $prefix = '/'): self
    {
        Assert::nullOrRegex($prefix, '/^.*\/$/', 'The API prefix must end with a slash.');

        $this->apiPrefix = $prefix;

        return $this;
    }

    /**
     * @return string[]
     */
    public function webRoutes(): array
    {
        return collect($this->webRouteDirs)
            ->flatMap(static fn ($path) => \Safe\glob($path . '/*.php'))
            ->toArray();
    }
}
