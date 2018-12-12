<?php

namespace App\Data\Error;

use JMS\Serializer\Annotation as Serializer;
use Throwable;

class CommonError
{
    /**
     * @Serializer\SerializedName("code")
     * @Serializer\Groups({"common"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $code;

    /**
     * @Serializer\SerializedName("message")
     * @Serializer\Groups({"common"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $message;

    /**
     * @Serializer\SerializedName("file")
     * @Serializer\Groups({"internal"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $file;

    /**
     * @Serializer\SerializedName("line")
     * @Serializer\Groups({"internal"})
     * @Serializer\Type("int")
     *
     * @var int
     */
    private $line;

    /**
     * @Serializer\SerializedName("stack_trace")
     * @Serializer\Groups({"internal"})
     * @Serializer\Type("array")
     *
     * @var array
     */
    private $stackTrace;

    /**
     * @Serializer\SerializedName("previous")
     * @Serializer\Groups({"internal"})
     * @Serializer\Type("App\DTO\Error\CommonError")
     *
     * @var CommonError | null
     */
    private $previous;

    /**
     * @Serializer\SerializedName("fields")
     * @Serializer\Groups({"validation"})
     * @Serializer\Type("array<string, App\DTO\Error\FieldValidationError>")
     *
     * @var string
     */
    private $fieldErrors;

    /**
     * @param string           $code
     * @param string           $message
     * @param Throwable | null $exception
     *
     * @return CommonError
     */
    public static function createFromException(string $code, string $message, ?Throwable $exception = null)
    {
        $previous = $exception->getPrevious();

        return new static(
            $code,
            $message,
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTrace(),
            empty($previous) ? null : static::createFromException(
                $previous->getCode(),
                $previous->getMessage(),
                $previous
            )
        );
    }

    /**
     * @param string             $code
     * @param string             $message
     * @param string             $file
     * @param int                $line
     * @param array              $stackTrace
     * @param CommonError | null $previous
     * @param array              $fieldErrors
     */
    public function __construct(
        string $code,
        string $message,
        string $file,
        int $line,
        array $stackTrace = [],
        CommonError $previous = null,
        array $fieldErrors = []
    ) {
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->stackTrace = $stackTrace;
        $this->previous = $previous;
        $this->fieldErrors = $fieldErrors;
    }

    /**
     * @param string               $fieldName
     * @param FieldValidationError $fieldErrors
     */
    public function setFieldError(string $fieldName, FieldValidationError $fieldErrors): void
    {
        $this->fieldErrors[$fieldName] = $fieldErrors;
    }
}
