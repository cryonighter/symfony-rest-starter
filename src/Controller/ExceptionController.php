<?php

namespace App\Controller;

use App\DTO\Error\CommonError;
use App\DTO\Error\FieldValidationError;
use App\Exception\ValidationHttpException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Throwable;

class ExceptionController
{
    /**
     * General information about the error
     */
    private const GROUP_COMMON = 'common';

    /**
     * Detailed information about error that only developers need
     */
    private const GROUP_INTERNAL = 'internal';

    /**
     * Information on validation errors received from the user
     */
    private const GROUP_VALIDATION = 'validation';

    /**
     * @var string
     */
    private $environment;

    /**
     * @param string $environment
     */
    public function __construct(string $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param Request                     $request
     * @param Throwable                   $exception
     * @param DebugLoggerInterface | null $logger
     *
     * @return View
     */
    public function showAction(Request $request, Throwable $exception, ?DebugLoggerInterface $logger = null): View
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $this->createHttpExceptionView($exception);
        }

        return $this->createInternalExceptionView($exception);
    }

    /**
     * @param Throwable $exception
     *
     * @return View
     */
    private function createHttpExceptionView(Throwable $exception): View
    {
        $commonError = CommonError::createFromException('00', $exception->getMessage(), $exception);

        if ($exception instanceof ValidationHttpException) {
            $validationErrors = $exception->getValidationErrors();

            if (!empty(count($validationErrors))) {
                foreach ($exception->getValidationErrors() as $constraint) {
                    $commonError->setFieldError(
                        $constraint->getPropertyPath(),
                        new FieldValidationError('00', $constraint->getMessage())
                    );
                }
            }
        }

        return View::create($commonError, $exception->getStatusCode(), $exception->getHeaders())
            ->setContext((new Context())->addGroups([self::GROUP_COMMON, self::GROUP_VALIDATION]));
    }

    /**
     * @param Throwable $exception
     *
     * @return View
     */
    private function createInternalExceptionView(Throwable $exception): View
    {
        if ('dev' === $this->environment) {
            $message = $exception->getMessage();
            $serializationGroups = [self::GROUP_COMMON, self::GROUP_INTERNAL];
        } else {
            $message = 'Internal server error';
            $serializationGroups = [self::GROUP_COMMON];
        }

        $commonError = CommonError::createFromException('00', $message, $exception);

        return View::create($commonError, Response::HTTP_INTERNAL_SERVER_ERROR, [])
            ->setContext((new Context())->addGroups($serializationGroups));
    }
}
