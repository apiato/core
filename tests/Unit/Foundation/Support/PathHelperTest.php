<?php

namespace Tests\Unit\Foundation\Support;

use Apiato\Foundation\Support\PathHelper;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\UnitTestCase;

#[CoversClass(PathHelper::class)]
class PathHelperTest extends UnitTestCase
{
    public function testGetShipFoldersNamesReturnsCorrectNames(): void
    {
        File::shouldReceive('directories')
            ->with(PathHelper::getSharedDirectoryPath())
            ->andReturn(['/path/to/ship1', '/path/to/ship2']);

        $result = PathHelper::getShipFolderNames();

        $this->assertEquals(['ship1', 'ship2'], $result);
    }

    public function testGetSectionContainerNamesReturnsCorrectNames(): void
    {
        File::shouldReceive('directories')
            ->with(app_path('Containers' . DIRECTORY_SEPARATOR . 'Section1'))
            ->andReturn(['/path/to/container1', '/path/to/container2']);

        $result = PathHelper::getSectionContainerNames('Section1');

        $this->assertEquals(['container1', 'container2'], $result);
    }

    public function testGetAllContainerNamesReturnsCorrectNames(): void
    {
        File::shouldReceive('directories')
            ->with(app_path('Containers'))
            ->andReturn(['/path/to/Section1', '/path/to/Section2']);
        File::shouldReceive('directories')
            ->with(app_path('Containers' . DIRECTORY_SEPARATOR . 'Section1'))
            ->andReturn(['/path/to/Section1/Container1', '/path/to/Section1/Container2']);
        File::shouldReceive('directories')
            ->with(app_path('Containers' . DIRECTORY_SEPARATOR . 'Section2'))
            ->andReturn(['/path/to/Section2/Container1', '/path/to/Section2/Container2']);

        $result = PathHelper::getContainerNames();

        $this->assertEquals(['Container1', 'Container2', 'Container1', 'Container2'], $result);
    }
}
