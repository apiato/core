<?php

namespace Apiato\Core\Traits\TestsTraits\PhpUnit;

use Illuminate\Http\UploadedFile;

trait TestsUploadHelperTrait
{
    public function getTestingFile($fileName, $stubDirPath, $mimeType = 'text/plain', $size = null): UploadedFile
    {
        $file = $stubDirPath . $fileName;

        return new UploadedFile($file, $fileName, $mimeType, $size, null, true); // null = null | $testMode = true
    }

    public function getTestingImage($imageName, $stubDirPath, $mimeType = 'image/jpeg', $size = null): UploadedFile
    {
        return $this->getTestingFile($imageName, $stubDirPath, $mimeType, $size);
    }
}
