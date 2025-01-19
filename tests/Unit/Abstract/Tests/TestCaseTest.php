<?php

namespace Tests\Unit\Abstract\Tests;

use Apiato\Abstract\Tests\TestCase;
use Apiato\Foundation\Support\Traits\HashId;
use Apiato\Foundation\Support\Traits\Testing\Assertions;
use Apiato\Foundation\Support\Traits\Testing\RequestHelper;
use Apiato\Foundation\Support\Traits\Testing\TestingUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

describe(class_basename(TestCase::class), function () {
    it('uses expected traits', function () {
        $expectedTraits = [
            TestingUser::class,
            RequestHelper::class,
            Assertions::class,
            HashId::class,
            LazilyRefreshDatabase::class,
        ];

        expect(TestCase::class)->toUseTraits($expectedTraits);
    });
})->coversClass(TestCase::class);
