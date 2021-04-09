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
     */
    private function resolveClass($class)
    {
        // in case passing apiato style names such as containerName@classType
        if ($this->needsParsing($class)) {
            $parsedClass = $this->parseClassName($class);
            $className = $parsedClass[1];
            $sectionName = null;

            $parsedPath = explode(':', $parsedClass[0]);
            if (count($parsedPath) > 1) {
                $sectionName = $this->capitalizeFirstLetter($parsedPath[0]);
                $containerName = $this->capitalizeFirstLetter($parsedPath[1]);
            } else {
                $containerName = $this->capitalizeFirstLetter($parsedPath[0]);
            }

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
     * @param        $class
     * @param string $separator
     *
     * @return  int
     */
    private function needsParsing($class, $separator = '@'): int
    {
        return preg_match('/' . $separator . '/', $class);
    }

    /**
     * Split containerName@someClass into container name and class name
     *
     * @param        $class
     * @param string $delimiter
     *
     * @return  array
     */
    private function parseClassName($class, $delimiter = '@'): array
    {
        return explode($delimiter, $class);
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
