<?php

declare(strict_types=1);

use Apiato\Foundation\Apiato;
use Apiato\Support\HashidsManagerDecorator;

use function Safe\file_put_contents;
use function Safe\mkdir;
use function Safe\rmdir;
use function Safe\unlink;

describe('helpers', function (): void {
    it('can get the Apiato instance', function (): void {
        expect(apiato())->toBeInstanceOf(Apiato::class);
    })->coversFunction('apiato');

    it("can get the path to the application's shared directory", function (): void {
        expect(shared_path())->toBe(base_path('app/Ship'));
    })->coversFunction('shared_path');

    it('can get the Hashids instance', function (): void {
        expect(hashids())->toBeInstanceOf(HashidsManagerDecorator::class);
    })->coversFunction('hashids');

    it('can find php files recursively', function (): void {
        $baseDir = sys_get_temp_dir() . '/test_' . uniqid('', true);
        mkdir($baseDir . '/subdir', 0777, true);
        file_put_contents($baseDir . '/file1.php', '<?php');
        file_put_contents($baseDir . '/subdir/file2.php', '<?php');

        $files = recursiveGlob($baseDir . '/*.php');
        expect($files)->toHaveCount(2);

        // Clean up
        unlink($baseDir . '/file1.php');
        unlink($baseDir . '/subdir/file2.php');
        rmdir($baseDir . '/subdir');
        rmdir($baseDir);
    });
});
