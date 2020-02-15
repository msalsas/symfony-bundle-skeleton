<?php

namespace Acme\FooBundle\Service;

use Acme\FooBundle\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorBagInterface;

class Service
{

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TokenStorageInterface
     */
    protected $token;

    /**
     * @var RequestStack
     */
    protected $request;

    /**
     * @var TranslatorBagInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $bar;

    /**
     * @var integer
     */
    protected $integerFoo;

    /**
     * @var integer
     */
    protected $integerBar;

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $token,
        RequestStack $requestStack,
        TranslatorBagInterface $translator,
        $bar,
        $integerFoo,
        $integerBar
    ) {
        $this->em = $em;
        $this->token = $token;
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
        $this->bar = $bar;
        $this->integerFoo = (int) $integerFoo;
        $this->integerBar = (int) $integerBar;
    }

    public function foo($a, $b)
    {
        return 'This is an uncertain ' . $this->translator->trans($this->bar[0]) . ' output ' . ($a + $b) * $this->integerFoo / $this->integerBar;
    }

    public function getCars()
    {
        $user = $this->token->getToken()->getUser();
        if (!method_exists($user, 'getUsername')) {
            $user = null;
        }
        $carRepository = $this->em->getRepository(Car::class);

        return $carRepository->findBy(['user' => $user]);
    }

    public function createCar($brand, $model)
    {
        $car = new Car();
        $user = $this->token->getToken()->getUser();

        $car->setBrand($brand);
        $car->setModel($model);
        if (method_exists($user, 'getUsername')) {
            $car->setUser($user);
        }

        $this->em->persist($car);
        $this->em->flush();

        return $car;
    }
}