<?php

namespace App\Http\ViewHandler;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SerializeViewHandler
{
    /**
     * @param ViewHandler $viewHandler
     * @param View        $view
     * @param Request     $request
     * @param string      $format
     *
     * @return Response
     */
    public function createResponse(ViewHandler $viewHandler, View $view, Request $request, string $format): Response
    {
        $response = $viewHandler->createResponse($view, $request, $format);

        $contentType = $response->headers->get('Content-Type');
        $response->headers->set('Content-Type', "$contentType; charset=UTF-8");

        return $response;
    }
}
