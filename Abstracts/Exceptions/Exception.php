<?php

namespace Apiato\Core\Abstracts\Exceptions;

use App\Ship\Exceptions\Codes\ErrorCodeManager;
use Exception as BaseException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\MessageBag;
use Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;

/**
 * Class Exception.
 *
 * @author  Mahmoud Zalt <mahmoud@zalt.me>
 */
abstract class Exception extends SymfonyHttpException
{

    protected ?MessageBag $errors;

    /**
     * Default status code.
     *
     * @var int
     */
    const DEFAULT_STATUS_CODE = Response::HTTP_INTERNAL_SERVER_ERROR;

    protected string $environment;

    private $customData;

    /**
     * Exception constructor.
     *
     * @param null $message
     * @param null $errors
     * @param null $statusCode
     * @param int $code
     * @param BaseException|null $previous
     * @param array $headers
     */
    public function __construct(
        ?string $message = null,
        ?array $errors = null,
        ?int $statusCode = null,
        int $code = 0,
        ?BaseException $previous = null,
        array $headers = []
    )
    {

        // detect and set the running environment
        $this->environment = Config::get('app.env');

        $message = $this->prepareMessage($message);
        $this->errors = $this->prepareError($errors);
        $statusCode = $this->prepareStatusCode($statusCode);

        $this->logTheError($statusCode, $message, $code);

        parent::__construct($statusCode, $message, $previous, $headers, $code);

        $this->clearCustomData();

        $this->code = $this->evaluateErrorCode();
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

    public function getErrors(): MessageBag
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors->isNotEmpty();
    }

    /**
     * @param $statusCode
     * @param $message
     * @param $code
     */
    private function logTheError($statusCode, $message, $code)
    {
        // if not testing environment, log the error message
        if ($this->environment != 'testing') {
            Log::error('[ERROR] ' .
                'Status Code: ' . $statusCode . ' | ' .
                'Message: ' . $message . ' | ' .
                'Errors: ' . $this->errors . ' | ' .
                'Code: ' . $code
            );
        }
    }

    /**
     * @param null $errors
     *
     * @return  MessageBag|null
     */
    private function prepareError(?array $errors = null)
    {
        return is_null($errors) ? new MessageBag() : $this->prepareArrayError($errors);
    }

    /**
     * @param array $errors
     *
     * @return  array|MessageBag
     */
    private function prepareArrayError(array $errors = [])
    {
        return is_array($errors) ? new MessageBag($errors) : $errors;
    }

    /**
     * @param null $message
     *
     * @return  null
     */
    private function prepareMessage(?string $message = null): ?string
    {
        return is_null($message) && property_exists($this, 'message') ? $this->message : $message;
    }

    /**
     * @param $statusCode
     *
     * @return  int
     */
    private function prepareStatusCode($statusCode = null)
    {
        return is_null($statusCode) ? $this->findStatusCode() : $statusCode;
    }

    /**
     * @return  int
     */
    private function findStatusCode()
    {
        return property_exists($this, 'httpStatusCode') ? $this->httpStatusCode : Self::DEFAULT_STATUS_CODE;
    }

    /**
     * @return mixed
     */
    public function getCustomData()
    {
        return $this->customData;
    }

    protected function clearCustomData()
    {
        $this->customData = null;
    }

    /**
     * Append customData to the exception and return the Exception!
     *
     * @param $customData
     *
     * @return $this
     */
    public function overrideCustomData($customData)
    {
        $this->customData = $customData;
        return $this;
    }

    public function getErrorCode(): int
    {
        return $this->code;
    }

    /**
     * Overrides the code with the application error code (if set)
     *
     * @return int
     */
    private function evaluateErrorCode(): int
    {
        $code = $this->getErrorCode();

        if (is_array($code)) {
            $code = ErrorCodeManager::getCode($code);
        }

        return $code;
    }
}
