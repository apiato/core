<?php

namespace Tests\Unit\Utilities;

use Apiato\Core\Utilities\PathHelper;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Infrastructure\Dummies\UselessClass;
use Tests\UnitTestCase;

#[CoversClass(PathHelper::class)]
class PathHelperTest extends UnitTestCase
{
    public function testGetShipFoldersNamesReturnsCorrectNames(): void
    {
        File::shouldReceive('directories')
            ->with(PathHelper::getShipDirectory())
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

    public function testGetClassFullNameFromFileReturnsCorrectFullName(): void
    {
        $filePath = realpath(__DIR__ . '/../../Infrastructure/Dummies/UselessClass.php');
        File::shouldReceive('get')
            ->with($filePath)
            ->andReturn();

        $result = PathHelper::getFQCNFromFile($filePath);

        $this->assertEquals(UselessClass::class, $result);
    }

    public function testGetClassTypeReturnsCorrectType(): void
    {
        $result = PathHelper::getClassType('HelloDearWorld');

        $this->assertEquals('World', $result);
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
