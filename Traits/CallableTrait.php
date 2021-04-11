<?php

namespace Apiato\Core\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

trait CallableTrait
{
    /**
     * This function calls another class but wraps it in a DB-Transaction. This might be useful for CREATE / UPDATE / DELETE
     * operations in order to prevent the database from corrupt data. Internally, the regular call() method is used!
     *
     * @param string $class
     * @param array $runMethodArguments
     * @param array $extraMethodsToCall
     *
     * @return mixed
     */
    public function transactionalCall(string $class, $runMethodArguments = [], $extraMethodsToCall = [])
    {
        return DB::transaction(function () use ($class, $runMethodArguments, $extraMethodsToCall) {
            return $this->call($class, $runMethodArguments, $extraMethodsToCall);
        });
    }

    /**
     * This function will be called from anywhere (controllers, Actions,..) by the Apiato facade.
     * The $class input will usually be an Action or Task.
     *
     * @param string $class
     * @param array $runMethodArguments
     * @param array $extraMethodsToCall
     *
     * @return  mixed
     */
    public function call(string $class, $runMethodArguments = [], $extraMethodsToCall = [])
    {
        $class = App::make($class);

        $this->setUIIfExist($class);

        $this->callExtraMethods($class, $extraMethodsToCall);

        return $class->run(...$runMethodArguments);
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
