<?php

namespace Apiato\Core\Generator\Traits;

use Illuminate\Support\Facades\File;

trait SuggestionHelperTrait
{
    public function getActionsList(
        string $section,
        string $container,
        bool $removeActionPostFix = false,
        bool $removePhpPostFix = true,
        bool $unCamelizeAndReplaceWithSpace = false,
    ): array {
        $actionsDirectory = base_path('app/Containers/' . $section . '/' . $container . '/Actions');
        $files = $this->getAllFilesFromDirectory($actionsDirectory);

        $actions = [];

        foreach ($files as $action) {
            $fileName = $originalFileName = $action->getFilename();

            if ($removeActionPostFix) {
                $fileName = str_replace(['Action.php'], '', $fileName);
            }
            if ($removePhpPostFix) {
                $fileName = str_replace(['.php'], '', $fileName);
            }
            if ($unCamelizeAndReplaceWithSpace) {
                $fileName = uncamelize($fileName);
            }

            $actions[] = $fileName;
        }

        return $actions;
    }

    public function getModelsList(
        string $section,
        string $container,
        bool $removeModelPostFix = false,
        bool $removePhpPostFix = true,
        bool $unCamelizeAndReplaceWithSpace = false,
    ): array {
        $modelsDirectory = base_path('app/Containers/' . $section . '/' . $container . '/Models');
        $files = $this->getAllFilesFromDirectory($modelsDirectory);

        $models = [];

        foreach ($files as $model) {
            $fileName = $originalFileName = $model->getFilename();

            if ($removeModelPostFix) {
                $fileName = str_replace(['.php'], '', $fileName);
            }
            if ($removePhpPostFix) {
                $fileName = str_replace(['.php'], '', $fileName);
            }
            if ($unCamelizeAndReplaceWithSpace) {
                $fileName = uncamelize($fileName);
            }

            $models[] = $fileName;
        }

        return $models;
    }

    public function getControllersList(
        string $section,
        string $container,
        bool $removeControllerPostFix = false,
        bool $removePhpPostFix = true,
        bool $unCamelizeAndReplaceWithSpace = false,
    ): array {
        $controllersDirectory = base_path('app/Containers/' . $section . '/' . $container . '/UI/API/Controllers');
        $files = $this->getAllFilesFromDirectory($controllersDirectory);

        $controllers = [];

        foreach ($files as $controller) {
            $fileName = $originalFileName = $controller->getFilename();

            if ($removeControllerPostFix) {
                $fileName = str_replace(['Controller.php'], '', $fileName);
            }
            if ($removePhpPostFix) {
                $fileName = str_replace(['.php'], '', $fileName);
            }
            if ($unCamelizeAndReplaceWithSpace) {
                $fileName = uncamelize($fileName);
            }

            $controllers[] = $fileName;
        }

        return $controllers;
    }

    private function getAllFilesFromDirectory(string $directory): array
    {
        try {
            return File::allFiles($directory);
        } catch (\Exception) {
            return [];
        }
    }
}
