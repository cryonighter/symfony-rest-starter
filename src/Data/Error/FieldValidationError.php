<?php

namespace App\Data\Error;

use JMS\Serializer\Annotation as Serializer;

class FieldValidationError
{
    /**
     * @Serializer\SerializedName("code")
     * @Serializer\Groups({"validation"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $code;

    /**
     * @Serializer\SerializedName("message")
     * @Serializer\Groups({"validation"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $message;

    /**
     * @param string $code
     * @param string $message
     */
    public function __construct(string $code, string $message)
    {
        $this->code = $code;
        $this->message = $message;
    }
}
