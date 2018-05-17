<?php

namespace App\Controller;

use App\Exception\HttpException\ValidationHttpException;
use Exception;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionController extends FOSRestController
{
    /**
     * @param Request   $request
     * @param Exception $exception
     *
     * @return View
     */
    public function showAction(Request $request, Exception $exception): View
    {
        if ($exception instanceof HttpExceptionInterface) {
            $httpStatusCode = $exception->getStatusCode();
            $httpHeaders = $exception->getHeaders();
            $httpBody = [
                'code' => '00',
                'message' => $exception->getMessage(),
            ];

            if ($exception instanceof ValidationHttpException) {
                $validationErrors = $exception->getValidationErrors();

                if (!empty($validationErrors)) {
                    $httpBody['fields'] = array_map(
                        function (string $errorMessage): array {
                            return [
                                'code' => '00',
                                'message' => $errorMessage,
                            ];
                        },
                        $exception->getValidationErrors()
                    );
                }
            }
        } else {
            $httpStatusCode = 500;
            $httpHeaders = [];
            $httpBody = [
                'code' => '00',
                'message' => 'Внутренняя ошибка сервера',
            ];
        }

        return View::create($httpBody, $httpStatusCode, $httpHeaders);
    }
}
