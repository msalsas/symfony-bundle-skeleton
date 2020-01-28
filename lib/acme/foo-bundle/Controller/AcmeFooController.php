<?php

namespace Acme\FooBundle;

use Acme\FooBundle\DTO\CarDTO;
use Acme\FooBundle\Service\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AcmeFooController extends AbstractController
{
    // TODO: Define the services to be injected as properties
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function foo(RequestStack $requestStack, $a, $b)
    {
        $request = $requestStack->getCurrentRequest();

        if (!$request->isXmlHttpRequest()) {
            throw new AccessDeniedException();
        }

        try {
            // TODO: Your service call
            $result = $this->service->foo($a, $b);
        } catch (AccessDeniedException $e) {
            // TODO: Catch exception access denied
            return $this->json($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            // TODO: Catch unknown exception
            return $this->json($e->getMessage(), $e->getCode());
        }

        return $this->json($result, 200);
    }

    public function createCar(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();

        if (!$request->isXmlHttpRequest()) {
            throw new AccessDeniedException();
        }

        try {
            // TODO: Example using DTO
            $carDTO = CarDTO::fromRequest($request);
            $car = CarDTO::toEntity($carDTO);

            $car = $this->service->createCar($car);

            $carDTO = CarDTO::toDTO($car);
            $response = CarDTO::toResponse($carDTO);

        } catch (AccessDeniedException $e) {
            // TODO: Catch exception access denied
            return $this->json($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            // TODO: Catch unknown exception
            return $this->json('Unknown exception', $e->getCode());
        }

        return $response;
    }
}