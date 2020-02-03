<?php

namespace Acme\FooBundle\Tests\Mock;

use Symfony\Component\Security\Core\User\UserInterface;

class UserMock implements UserInterface
{
    public function getUsername()
    {
        return 'JohnDoe';
    }

    public function getPassword()
    {
        return 'johnDoePass';
    }

    public function getSalt()
    {
        return '12345';
    }

    public function getRoles()
    {
        return array(
            'ROLE_USER'
        );
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}