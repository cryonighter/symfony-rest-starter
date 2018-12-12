<?php

namespace App\EventListener;

use App\Data\Error\CommonError;
use App\Data\Error\FieldValidationError;
use App\Exception\ValidationHttpException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExceptionListener
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
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @var string
     */
    private $environment;

    /**
     * @param ViewHandlerInterface $viewHandler
     * @param string               $environment
     */
    public function __construct(ViewHandlerInterface $viewHandler, string $environment)
    {
        $this->viewHandler = $viewHandler;
        $this->environment = $environment;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();

        if ($exception instanceof HttpExceptionInterface) {
            $view = $this->createHttpExceptionView($exception);
        } else {
            $view = $this->createInternalExceptionView($exception);
        }

        $event->setResponse(
            $this->viewHandler->handle($view, $event->getRequest())
        );
    }

    /**
     * @param Throwable $exception
     *
     * @return View
     */
    private function createHttpExceptionView(Throwable $exception): View
    {
        $commonError = CommonError::createFromException('00', $exception->getMessage(), $exception);
        $context = (new Context())->addGroup(self::GROUP_COMMON);

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

            $context->addGroup(self::GROUP_VALIDATION);
        }

        return View::create($commonError, $exception->getStatusCode(), $exception->getHeaders())
            ->setContext($context);
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
