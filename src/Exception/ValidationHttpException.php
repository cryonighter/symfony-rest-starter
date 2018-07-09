<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class ValidationHttpException extends Exception implements HttpExceptionInterface
{
    /**
     * @var int
     */
    private $statusCode = Response::HTTP_BAD_REQUEST;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var array | string[]
     */
    private $validationErrors;

    /**
     * @param string                           $message
     * @param ConstraintViolationListInterface $validationErrors
     * @param array | string[]                 $headers
     * @param int                              $code
     * @param Throwable | null                 $previous
     */
    public function __construct(
        string $message,
        ConstraintViolationListInterface $validationErrors,
        array $headers = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->validationErrors = $validationErrors;
        $this->headers = $headers;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return ConstraintViolationListInterface | ConstraintViolationInterface[]
     */
    public function getValidationErrors(): ConstraintViolationListInterface
    {
        return $this->validationErrors;
    }
}
