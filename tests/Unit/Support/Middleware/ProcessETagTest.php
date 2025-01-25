<?php

use Apiato\Support\Middleware\ProcessETag;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

describe(class_basename(ProcessETag::class), function (): void {
    beforeEach(function (): void {
        config(['apiato.requests.use-etag' => true]);
        $this->next = function (Illuminate\Http\Request $request): Response {
            return response('content');
        };
    });

    it('should add the ETag header to the response', function (string $method): void {
        $request = Request::create('http://localhost', $method);

        $response = (new ProcessETag())->handle($request, $this->next);

        expect($response->headers->get('Etag'))->toBe(md5('content'));
    })->with([
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ]);

    it('should set the status code to 304 if the ETag matches the request', function (string $method): void {
        $request = Request::create('http://localhost', $method, server: ['HTTP_IF_NONE_MATCH' => md5('content')]);

        $response = (new ProcessETag())->handle($request, $this->next);

        expect($response->getStatusCode())->toBe(304);
    })->with([
        'GET',
        'HEAD',
    ]);

    it('should not set the status code to 304 if the ETag does not match the request', function (): void {
        $request = Request::create('http://localhost', 'GET', server: ['HTTP_IF_NONE_MATCH' => 'invalid-etag']);

        $response = (new ProcessETag())->handle($request, $this->next);

        expect($response->getStatusCode())->not->toBe(304);
    });

    it('should not set the ETag header if the feature is disabled', function (): void {
        config(['apiato.requests.use-etag' => false]);
        $request = Request::create('http://localhost', 'GET', server: ['HTTP_IF_NONE_MATCH' => md5('content')]);

        $response = (new ProcessETag())->handle($request, $this->next);

        expect($response->headers->get('Etag'))->toBeNull();
    });

    it('should throw an exception if the request method is not GET or HEAD and the ETag header is present', function (): void {
        $request = Request::create('http://localhost', 'POST', server: ['HTTP_IF_NONE_MATCH' => md5('content')]);

        $this->expectException(PreconditionFailedHttpException::class);

        (new ProcessETag())->handle($request, $this->next);
    });

    it('should not set the status code to 304 if the request does not contain the if-none-match header', function (): void {
        $response = (new ProcessETag())->handle(request(), $this->next);

        expect($response->getStatusCode())->not->toBe(304);
    });

    it('should not set the status code to 304 if the request contains the if-none-match header but the ETag does not match', function (): void {
        $request = Request::create('http://localhost', 'GET', server: ['HTTP_IF_NONE_MATCH' => 'invalid-etag']);

        $response = (new ProcessETag())->handle($request, $this->next);

        expect($response->getStatusCode())->not->toBe(304);
    });
})->covers(ProcessETag::class);
