<?php

namespace Apiato\Core\Abstracts\Exceptions;

use Exception as BaseException;
use Illuminate\Support\Facades\Config;
use Log;

abstract class Exception extends BaseException
{
    private const DEFAULT_STATUS_CODE = 500;
    protected string $environment;
    protected $message;
    protected $code;

    public function __construct(
        ?string $message = null,
        ?int $code = null,
        ?BaseException $previous = null
    )
    {
        // Detect and set the running environment
        $this->environment = Config::get('app.env');

        $this->message = $this->prepareMessage($message);
        $this->code = $this->prepareStatusCode($code);
    }

    /**
     * @param string|null $message
     *
     * @return string|null
     */
    private function prepareMessage(?string $message = null): ?string
    {
        return is_null($message) ? $this->message : $message;
    }

    private function prepareStatusCode(?int $code = null): int
    {
        return is_null($code) ? $this->findStatusCode() : $code;
    }

    private function findStatusCode(): int
    {
        return $this->code ?? Self::DEFAULT_STATUS_CODE;
    }

    /**
     * Help developers debug the error without showing these details to the end user.
     * Usage: `throw (new MyCustomException())->debug($e)`.
     *
     * @param $error
     * @param $force
     *
     * @return $this
     */
    public function debug($error, bool $force = false)
    {
        if ($error instanceof BaseException) {
            $error = $error->getMessage();
        }

        if ($this->environment != 'testing' || $force === true) {
            Log::error('[DEBUG] ' . $error);
        }

        return $this;
    }
}
