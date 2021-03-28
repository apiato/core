<?php

namespace Apiato\Core\Foundation;

use Apiato\Core\Exceptions\ClassDoesNotExistException;
use Apiato\Core\Exceptions\MissingContainerException;
use Apiato\Core\Traits\CallableTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class Apiato
{
    use CallableTrait;

    /**
     * The Apiato version.
     */
    public const VERSION = '10.0.0';

    /**
     * Get the containers namespace value from the apiato config file
     */
    public function getContainersNamespace(): string
    {
        return Config::get('apiato.containers.namespace');
    }

    /**
     * Get the sections namespace value from the apiato config file
     */
    public function getSectionsNamespace(): string
    {
        return Config::get('apiato.sections.namespace');
    }

    /**
     * Get the containers names
     */
    public function getContainerNames(): array
    {
        $containersNames = [];

        foreach ($this->getContainerPaths() as $containersPath) {
            $containersNames[] = basename($containersPath);
        }

        return $containersNames;
    }

    /**
     * Get containers directories paths
     */
    public function getContainerPaths(): array
    {
        return File::directories(app_path('Containers'));
    }

    /**
     * Get the section names
     */
    public function getSectionNames(): array
    {
        $sectionNames = [];

        foreach ($this->getSectionPaths() as $sectionPath) {
            $sectionNames[] = basename($sectionPath);
        }

        // remove "ship" from list
        foreach ($sectionNames as $key => $value) {
            if (strtolower($value) === 'ship')
                array_splice($sectionNames, $key, 1);
        }

        return $sectionNames;
    }

    /**
     * Get section directories paths
     */
    public function getSectionPaths(): array
    {
        return File::directories(app_path());
    }

    /**
     * Get the port folders names
     */
    public function getShipFoldersNames(): array
    {
        $portFoldersNames = [];

        foreach ($this->getShipPath() as $portFoldersPath) {
            $portFoldersNames[] = basename($portFoldersPath);
        }

        return $portFoldersNames;
    }

    public function getShipPath(): array
    {
        return File::directories(app_path('Ship'));
    }

    /**
     * Build and return an object of a class from its file path
     *
     * @param $filePathName
     *
     * @return  mixed
     */
    public function getClassObjectFromFile($filePathName)
    {
        $classString = $this->getClassFullNameFromFile($filePathName);

        return new $classString;
    }

    /**
     * Get the full name (name \ namespace) of a class from its file path
     * result example: (string) "I\Am\The\Namespace\Of\This\Class"
     *
     * @param $filePathName
     *
     * @return  string
     */
    public function getClassFullNameFromFile($filePathName): string
    {
        return $this->getClassNamespaceFromFile($filePathName) . '\\' . $this->getClassNameFromFile($filePathName);
    }

    /**
     * Get the class namespace form file path using token
     *
     * @param $filePathName
     *
     * @return  null|string
     */
    protected function getClassNamespaceFromFile($filePathName): ?string
    {
        $src = file_get_contents($filePathName);

        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (!$namespace_ok) {
            return null;
        }

        return $namespace;
    }

    /**
     * Get the class name from file path using token
     *
     * @param $filePathName
     *
     * @return  mixed
     */
    protected function getClassNameFromFile($filePathName)
    {
        $php_code = file_get_contents($filePathName);

        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING
            ) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        return $classes[0];
    }

    /**
     * Check if a word starts with another word
     * @param string $word
     * @param string $startsWith
     * @return bool
     */
    public function stringStartsWith(string $word, string $startsWith): bool
    {
        return (substr($word, 0, strlen($startsWith)) === $startsWith);
    }

    /**
     * @param        $word
     * @param string $splitter
     * @param bool $uppercase
     *
     * @return  mixed|string
     */
    public function uncamelize($word, $splitter = " ", $uppercase = true)
    {
        $word = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0',
            preg_replace('/(?!^)[[:upper:]]+/', $splitter . '$0', $word));

        return $uppercase ? ucwords($word) : $word;
    }

    /**
     * Build namespace for a class in Container.
     *
     * @param $containerName
     * @param $className
     *
     * @return  string
     */
    public function buildClassFullName($containerName, $className): string
    {
        return 'App\Containers\\' . $containerName . '\\' . $this->getClassType($className) . 's\\' . $className;
    }

    /**
     * Get the last part of a camel case string.
     * Example input = helloDearWorld | returns = World
     *
     * @param $className
     *
     * @return  mixed
     */
    public function getClassType($className)
    {
        $array = preg_split('/(?=[A-Z])/', $className);

        return end($array);
    }

    /**
     * @param $containerName
     *
     * @throws MissingContainerException
     */
    public function verifyContainerExist($containerName): void
    {
        if (!is_dir(app_path('Containers/' . $containerName))) {
            throw new MissingContainerException("Container ($containerName) is not installed.");
        }
    }

    /**
     * @param $className
     *
     * @throws ClassDoesNotExistException
     */
    public function verifyClassExist($className): void
    {
        if (!class_exists($className)) {
            throw new ClassDoesNotExistException("Class ($className) is not installed.");
        }
    }

}
