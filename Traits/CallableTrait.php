<?php

namespace Apiato\Core\Traits;

use Apiato\Core\Foundation\Facades\Apiato;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Class CallableTrait.
 *
 * @author  Mahmoud Zalt <mahmoud@zalt.me>
 */
trait CallableTrait
{

    /**
     * This function will be called from Controllers and Actions.
     * The $class input will mainly be an Action or Task.
     *
     * @param       $class
     * @param array $runArguments
     * @param array $methods
     *
     * @return  mixed
     */
    public function call($class, $runArguments = [], $methods = [])
    {
        $class = $this->resolveClass($class);

        $this->setUIIfExist($class);

        $this->callExtraMethods($class, $methods);

        return $class->run(...$runArguments);
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

            $containerName = $this->capitalizeFirstLetter($parsedClass[0]);
            $className = $parsedClass[1];

            Apiato::verifyContainerExist($containerName);

            $class = $classFullName = Apiato::buildClassFullName($containerName, $className);

            Apiato::verifyClassExist($classFullName);
        } else {
            Log::warning('It is recommended to use the apiato caller style (containerName@className) for ' . $class);
        }

        return App::make($class);
    }

    /**
     * Split containerName@someClass into container name and class name
     *
     * @param        $class
     * @param string $delimiter
     *
     * @return  array
     */
    private function parseClassName($class, $delimiter = '@')
    {
        return explode($delimiter, $class);
    }

    /**
     * If it's apiato Style caller like this: containerName@someClass
     *
     * @param        $class
     * @param string $separator
     *
     * @return  int
     */
    private function needsParsing($class, $separator = '@')
    {
        return preg_match('/' . $separator . '/', $class);
    }

    /**
     * @param $string
     *
     * @return  string
     */
    private function capitalizeFirstLetter($string)
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
    private function setUIIfExist($class)
    {
        if (method_exists($class, 'setUI') && property_exists($this, 'ui')) {
            $class->setUI($this->ui);
        }
    }

    /**
     * @param $class
     * @param $methods
     */
    private function callExtraMethods($class, $methods)
    {
        // allows calling other methods in the class before calling the main `run` function.
        foreach ($methods as $methodInfo) {
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
    private function callWithArguments($class, $methodInfo)
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
    private function callWithoutArguments($class, $methodInfo)
    {
        if (method_exists($class, $methodInfo)) {
            $class->$methodInfo();
        }
    }

}
