<?php

namespace Acme\FooBundle\Tests;

use Acme\FooBundle\Service\Service;
use Acme\FooBundle\Tests\Mock\UserMock;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\Translator;

class ServiceTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    protected $emMock;
    protected $requestStackMock;
    protected $translator;
    protected $userMock;
    protected $tokenStorageMock;

    public function setUp()
    {
        parent::setUp();
        $this->setDefaultMocks();
        $this->translator = new Translator('en');
    }

    protected function setDefaultMocks()
    {
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->willReturn($emRepositoryMock);

        $requestMock = $this->getMockBuilder(Request::class)->getMock();
        $this->requestStackMock = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getCurrentRequest'))
            ->getMock();
        $this->requestStackMock->expects($this->any())
            ->method('getCurrentRequest')->willReturn($requestMock);
    }

    protected function setUserMocks()
    {
        $this->userMock = $this->getMockBuilder(UserMock::class)
            ->getMock();

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($this->userMock);

        $this->tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenStorageMock->method('getToken')->willReturn($tokenMock);
    }

    public function testFooWithIntegers()
    {
        $service = new Service($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 'bar', 67, 12);

        $foo = $service->foo(4, 3);

        $this->assertSame('This is an uncertain  output' , $foo);
    }
}