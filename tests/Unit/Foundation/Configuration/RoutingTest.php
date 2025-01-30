<?php

use Apiato\Foundation\Configuration\Routing;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;

describe(class_basename(Routing::class), function (): void {
    it('can collect web routes from paths', function (): void {
        $configuration = new Routing();
        $configuration->loadWebRoutesFrom(
            app_path('Containers/*/*/UI/WEB/Routes'),
        );

        expect($configuration->webRoutes())->toBe([
            app_path('Containers/MySection/Author/UI/WEB/Routes/ListAuthors.php'),
            app_path('Containers/MySection/Book/UI/WEB/Routes/CreateBook.v1.public.php'),
            app_path('Containers/MySection/Book/UI/WEB/Routes/ListBooks.php'),
        ]);
    });

    it('can set api prefix', function (): void {
        $configuration = new Routing();
        $configuration->prefixApiUrlsWith('api/');

        expect($configuration->getApiPrefix())->toBe('api/');
    });

    it('does not apply api version from the route file name for web routes', function (): void {
        $configuration = new Routing();
        $configuration->loadWebRoutesFrom(
            app_path('Containers/*/*/UI/WEB/Routes'),
        );

        $webRoutes = getRoutesByMiddleware('web')->map(
            static fn (Route $route) => $route->uri(),
        )->toArray();

        expect($webRoutes)->toBe([
            'authors',
            'books',
            'books/create',
        ]);
    });

    it('applies api version prefix from the route file name', function (): void {
        $configuration = new Routing();
        $configuration->loadApiRoutesFrom(
            app_path('Containers/*/*/UI/API/Routes'),
        );

        $apiRoutes = getRoutesByMiddleware('api')->flatMap(
            static fn (Route $route): array => [
                $route->methods(),
                $route->uri(),
                $route->gatherMiddleware(),
                $route->domain(),
            ],
        )->toArray();

        expect($apiRoutes)->toBe([
            ['GET', 'HEAD'],
            'v1/authors/{author}/children/{children}/books/{book}',
            [
                'api',
                'throttle:api',
            ],
            'localhost',
            ['POST'],
            'v1/books',
            [
                'api',
                'throttle:api',
            ],
            'localhost',
            ['GET', 'HEAD'],
            'v3/authors',
            [
                'api',
                'throttle:api',
            ],
            'localhost',
            ['GET', 'HEAD'],
            'v4/books',
            [
                'api',
                'throttle:api',
            ],
            'localhost',
        ]);
    });

    /**
     * @return Collection<int, Route>
     */
    function getRoutesByMiddleware(string $middleware): Collection
    {
        $routes = Illuminate\Support\Facades\Route::getRoutes()->getRoutes();

        return collect($routes)
            ->filter(
                static fn (Route $route) => collect($route->gatherMiddleware())
                    ->contains($middleware),
            )->sortBy(
                static fn (Route $route) => $route->uri(),
            )->values();
    }
})->covers(Routing::class);
