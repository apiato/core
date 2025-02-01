<?php

use Apiato\Http\Middleware\ValidateJsonContent;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;

describe(class_basename(ValidateJsonContent::class), function (): void {
    beforeEach(function (): void {
        config(['apiato.requests.use-etag' => true]);
        $this->next = (fn (Illuminate\Http\Request $request): Response => response('content'));
    });

    it('should throw an exception if the request does not expect JSON and feature is enabled', function (): void {
        config(['apiato.requests.force-accept-header' => true]);
        $request = Request::create('/test', 'GET');
        $request->headers->remove('Accept');
        $middleware = new ValidateJsonContent();

        $this->expectException(RuntimeException::class);

        $middleware->handle($request, $this->next);
    });

    it('should not throw an exception if the request expects JSON or feature is disabled', function (bool $enabled): void {
        config(['apiato.requests.force-accept-header' => $enabled]);
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept', 'application/json');
        $middleware = new ValidateJsonContent();

        $response = $middleware->handle($request, $this->next);

        expect($response)->toBeInstanceOf(Response::class);
    })->with([
        true,
        false,
    ]);
})->covers(ValidateJsonContent::class);
