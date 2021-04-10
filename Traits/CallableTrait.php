<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Exceptions\ClassDoesNotExistException;
use Apiato\Core\Exceptions\MissingContainerException;
use Apiato\Core\Foundation\Facades\Apiato;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait CallableTrait
{
    /**
     * This function calls another class but wraps it in a DB-Transaction. This might be useful for CREATE / UPDATE / DELETE
     * operations in order to prevent the database from corrupt data. Internally, the regular call() method is used!
     *
     * @param       $class
     * @param array $runMethodArguments
     * @param array $extraMethodsToCall
     *
     * @return mixed
     */
    public function transactionalCall($class, $runMethodArguments = [], $extraMethodsToCall = [])
    {
        return DB::transaction(function () use ($class, $runMethodArguments, $extraMethodsToCall) {
            return $this->call($class, $runMethodArguments, $extraMethodsToCall);
        });
    }

    /**
     * This function will be called from anywhere (controllers, Actions,..) by the Apiato facade.
     * The $class input will usually be an Action or Task.
     *
     * @param       $class
     * @param array $runMethodArguments
     * @param array $extraMethodsToCall
     *
     * @return  mixed
     * @throws ClassDoesNotExistException
     * @throws MissingContainerException
     */
    public function call($class, $runMethodArguments = [], $extraMethodsToCall = [])
    {
        $class = $this->resolveClass($class);

        $this->setUIIfExist($class);

        $this->callExtraMethods($class, $extraMethodsToCall);

        return $class->run(...$runMethodArguments);
    }

    /**
     * Get instance from a class string
     *
     * @param $class
     *
     * @return  mixed
     * @throws ClassDoesNotExistException
     * @throws MissingContainerException
     */
    private function resolveClass($class)
    {
        if ($this->needsParsing($class)) {
            $parsedClass = $this->parseClassName($class);
            $sectionName = $parsedClass['section_name'];
            $containerName = $parsedClass['container_name'];
            $className = $parsedClass['class_name'];

            if (!Apiato::containerExist($containerName, $sectionName)) {
                throw new MissingContainerException("($containerName) Container is not installed in ($sectionName) Section.");
            }

            $class = $classFullName = Apiato::buildClassFullName($containerName, $className, $sectionName);
            if (!class_exists($classFullName)) {
                throw new ClassDoesNotExistException("Class ($classFullName) is not installed.");
            }
        } else {
            if (Config::get('apiato.logging.log-wrong-apiato-caller-style', true)) {
                Log::debug('It is recommended to use the apiato caller style (containerName@className) for ' . $class);
            }
        }

        return App::make($class);
    }

    /**
     * If it's apiato Style caller like this: containerName@someClass
     *
     * @param string $class
     * @param string $separator
     *
     * @return  int
     */
    private function needsParsing(string $class, $separator = '@'): int
    {
        return preg_match('/' . $separator . '/', $class);
    }

    /**
     * Split sectionName:containerName@someClass into section name, container name and class name
     *
     * @param string $class
     * @param string $sectionDelimeter
     * @param string $classDelimiter
     * @return  array
     */
    private function parseClassName(string $class, string $sectionDelimeter = ':', string $classDelimiter = '@'): array
    {
        $parsedContainerAndClass = explode($classDelimiter, $class);
        $containerName = $this->capitalizeFirstLetter($parsedContainerAndClass[0]);
        $className = $this->capitalizeFirstLetter($parsedContainerAndClass[1]);
        $sectionName = null;
        $parsedSectionAndContainer = explode($sectionDelimeter, $parsedContainerAndClass[0]);

        if ($this->isCalledWithSection($parsedSectionAndContainer)) {
            $sectionName = $this->capitalizeFirstLetter($parsedSectionAndContainer[0]);
            $containerName = $this->capitalizeFirstLetter($parsedSectionAndContainer[1]);
        }

        return [
            'section_name' => $sectionName,
            'container_name' => $containerName,
            'class_name' => $className,
        ];
    }

    /**
     * @param $string
     *
     * @return  string
     */
    private function capitalizeFirstLetter($string): string
    {
        return ucfirst($string);
    }

    /**
     * @param $parsedClass
     * @return bool
     */
    private function isCalledWithSection($parsedClass): bool
    {
        return count($parsedClass) > 2;
    }

    /**
     *
     * $this->ui is coming, should be attached on the parent controller, from where the actions was called.
     * It can be WebController and ApiController. Each of them has ui, to inform the action
     * if it needs to handle the request differently.
     *
     * @param $class
     */
    private function setUIIfExist($class): void
    {
        if (method_exists($class, 'setUI') && property_exists($this, 'ui')) {
            $class->setUI($this->ui);
        }
    }

    /**
     * @param $class
     * @param $extraMethodsToCall
     */
    private function callExtraMethods($class, $extraMethodsToCall): void
    {
        // allows calling other methods in the class before calling the main `run` function.
        foreach ($extraMethodsToCall as $methodInfo) {
            // if is array means it method has arguments
            if (is_array($methodInfo)) {
                $this->callWithArguments($class, $methodInfo);
            } else {
                // if is string means it's just the method name without arguments
                $this->callWithoutArguments($class, $methodInfo);
            }
        }
    }

    /**
     * @param $class
     * @param $methodInfo
     */
    private function callWithArguments($class, $methodInfo): void
    {
        $method = key($methodInfo);
        $arguments = $methodInfo[$method];
        if (method_exists($class, $method)) {
            $class->$method(...$arguments);
        }
    }

    /**
     * @param $class
     * @param $methodInfo
     */
    private function callWithoutArguments($class, $methodInfo): void
    {
        if (method_exists($class, $methodInfo)) {
            $class->$methodInfo();
        }
    }
}
