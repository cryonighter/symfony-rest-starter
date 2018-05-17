<?php

namespace App\Exception\HttpException;

use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidationHttpException extends BadRequestHttpException
{
    /**
     * @var array | string[]
     */
    private $validationErrors;

    /**
     * @param null             $message
     * @param Exception | null $previous
     * @param int              $code
     * @param array | string[] $validationErrors
     */
    public function __construct($message, Exception $previous = null, $code = 0, array $validationErrors = [])
    {
        parent::__construct($message, $previous, $code);

        $this->validationErrors = $validationErrors;
    }

    /**
     * @return array | string[]
     */
    public function getValidationErrors(): array {
        return $this->validationErrors;
    }
}
