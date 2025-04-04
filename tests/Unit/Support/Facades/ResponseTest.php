<?php

use Apiato\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Pest\Expectation;

describe(class_basename(Response::class), function (): void {
    it('should have the correct facade accessor', function (): void {
        expect(Response::create())
            ->toBeInstanceOf(Apiato\Http\Response::class);
    });

    dataset('method_params', function () {
        return [
            [null, [], 0],
            ['foo', [], 0],
            [[], [], 0],
            [[], ['foo' => 'bar'], 0],
            [[], [], 1],
            [[], ['foo' => 'bar'], 1],
        ];
    });

    it('can call the json method statically', function (
        mixed $data,
        array $headers,
        int $options,
    ): void {
        $result = Response::json($data, 200, $headers, $options);

        expect($result)
            ->toBeInstanceOf(JsonResponse::class)
            ->and($result->getData(true))
            ->when(!is_null($data), fn (Expectation $ex) => $ex->toBe($data))
            ->when(is_null($data), fn (Expectation $ex) => $ex->toBeArray()->toBeEmpty())
            ->and($result->getStatusCode())
            ->toBe(200)
            ->and($result->headers->get('foo'))
            ->when([] !== $headers, fn (Expectation $ex) => $ex->toBe('bar'))
            ->when(
                [] === $headers,
                fn (Expectation $ex) => $ex->toBeEmpty()
            ->and($result->getEncodingOptions())
            ->when(0 !== $options, fn (Expectation $ex) => $ex->toBe(1))
            ->when(0 === $options, fn (Expectation $ex) => $ex->toBe(0)),
            );
    })->with('method_params');

    it('can call the accepted method statically', function (
        mixed $data,
        array $headers,
        int $options,
    ): void {
        $result = Response::accepted($data, $headers, $options);

        expect($result)
            ->toBeInstanceOf(JsonResponse::class)
            ->and($result->getData(true))
            ->when(!is_null($data), fn (Expectation $ex) => $ex->toBe($data))
            ->when(is_null($data), fn (Expectation $ex) => $ex->toBeArray()->toBeEmpty())
            ->and($result->getStatusCode())
            ->toBe(202)
            ->and($result->headers->get('foo'))
            ->when([] !== $headers, fn (Expectation $ex) => $ex->toBe('bar'))
            ->when(
                [] === $headers,
                fn (Expectation $ex) => $ex->toBeEmpty()
            ->and($result->getEncodingOptions())
            ->when(0 !== $options, fn (Expectation $ex) => $ex->toBe(1))
            ->when(0 === $options, fn (Expectation $ex) => $ex->toBe(0)),
            );
    })->with('method_params');

    it('can call the created method statically', function (
        mixed $data,
        array $headers,
        int $options,
    ): void {
        $result = Response::created($data, $headers, $options);

        expect($result)
            ->toBeInstanceOf(JsonResponse::class)
            ->and($result->getData(true))
            ->when(!is_null($data), fn (Expectation $ex) => $ex->toBe($data))
            ->when(is_null($data), fn (Expectation $ex) => $ex->toBeArray()->toBeEmpty())
            ->and($result->getStatusCode())
            ->toBe(201)
            ->and($result->headers->get('foo'))
            ->when([] !== $headers, fn (Expectation $ex) => $ex->toBe('bar'))
            ->when(
                [] === $headers,
                fn (Expectation $ex) => $ex->toBeEmpty()
            ->and($result->getEncodingOptions())
            ->when(0 !== $options, fn (Expectation $ex) => $ex->toBe(1))
            ->when(0 === $options, fn (Expectation $ex) => $ex->toBe(0)),
            );
    })->with('method_params');

    it('can call the ok method statically', function (
        mixed $data,
        array $headers,
        int $options,
    ): void {
        $result = Response::ok($data, $headers, $options);

        expect($result)
            ->toBeInstanceOf(JsonResponse::class)
            ->and($result->getData(true))
            ->when(!is_null($data), fn (Expectation $ex) => $ex->toBe($data))
            ->when(is_null($data), fn (Expectation $ex) => $ex->toBeArray()->toBeEmpty())
            ->and($result->getStatusCode())
            ->toBe(200)
            ->and($result->headers->get('foo'))
            ->when([] !== $headers, fn (Expectation $ex) => $ex->toBe('bar'))
            ->when(
                [] === $headers,
                fn (Expectation $ex) => $ex->toBeEmpty()
            ->and($result->getEncodingOptions())
            ->when(0 !== $options, fn (Expectation $ex) => $ex->toBe(1))
            ->when(0 === $options, fn (Expectation $ex) => $ex->toBe(0)),
            );
    })->with('method_params');

    it('can call the noContent method statically', function (
        array $headers,
        int $options,
    ): void {
        $result = Response::noContent($headers, $options);

        expect($result)
            ->toBeInstanceOf(JsonResponse::class)
            ->and($result->getData(true))
            ->toBeEmpty()
            ->and($result->getStatusCode())
            ->toBe(204)
            ->and($result->headers->get('foo'))
            ->when([] !== $headers, fn (Expectation $ex) => $ex->toBe('bar'))
            ->when(
                [] === $headers,
                fn (Expectation $ex) => $ex->toBeEmpty()
            ->and($result->getEncodingOptions())
            ->when(0 !== $options, fn (Expectation $ex) => $ex->toBe(1))
            ->when(0 === $options, fn (Expectation $ex) => $ex->toBe(0)),
            );
    })->with([
        [[], 0],
        [[], 0],
        [[], 0],
        [['foo' => 'bar'], 0],
        [[], 1],
        [['foo' => 'bar'], 1],
    ]);
})->covers(Response::class)->only();
