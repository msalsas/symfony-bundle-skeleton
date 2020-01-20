<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utils;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use function Symfony\Component\String\u;

/**
 * This class is used to provide an example of integrating simple classes as
 * services into a Symfony application.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class Validator
{
    public function validateUsername(?string $username): string
    {
        if (empty($username)) {
            throw new InvalidArgumentException('The username can not be empty.');
        }

        if (1 !== preg_match('/^[a-z_]+$/', $username)) {
            throw new InvalidArgumentException('The username must contain only lowercase latin characters and underscores.');
        }

        return $username;
    }

    public function validatePassword(?string $plainPassword): string
    {
        if (empty($plainPassword)) {
            throw new InvalidArgumentException('The password can not be empty.');
        }

        if (u($plainPassword)->trim()->length() < 6) {
            throw new InvalidArgumentException('The password must be at least 6 characters long.');
        }

        return $plainPassword;
    }

    public function validateEmail(?string $email): string
    {
        if (empty($email)) {
            throw new InvalidArgumentException('The email can not be empty.');
        }

        if (null === u($email)->indexOf('@')) {
            throw new InvalidArgumentException('The email should look like a real email.');
        }

        return $email;
    }

    public function validateFullName(?string $fullName): string
    {
        if (empty($fullName)) {
            throw new InvalidArgumentException('The full name can not be empty.');
        }

        return $fullName;
    }

    public function validateDomainName(?string $domainName): string
    {
        if (empty($domainName)) {
            throw new InvalidArgumentException('The domain name can not be empty.');
        }

        if (1 !== preg_match('/^[A-Za-z-]+$/', $domainName)) {
            throw new InvalidArgumentException('The domain name must contain only latin characters and dashes.');
        }

        return $domainName;
    }

    public function validateBundleName(?string $bundleName): string
    {
        if (empty($bundleName)) {
            throw new InvalidArgumentException('The bundle name can not be empty.');
        }

        if (1 !== preg_match('/^[A-Za-z-]+$/', $bundleName)) {
            throw new InvalidArgumentException('The bundle name must contain only latin characters and dashes.');
        }

        return $bundleName;
    }

    public function validateBundleDescription(?string $description): string
    {
        if (empty($description)) {
            throw new InvalidArgumentException('The bundle description can not be empty.');
        }

        return $description;
    }

    public function validateBundleKeywords(?string $keywords): string
    {
        if (empty($keywords)) {
            throw new InvalidArgumentException('The bundle keywords can not be empty.');
        }

        if (1 !== preg_match('/^\[[\s\'"]+[\w]+[\s\'"]+(,[\s\'\"]+[\w]+[\s\'\"]+)*]$/', $keywords)) {
            throw new InvalidArgumentException('The keywords must be like ["foo", "bar"].');
        }

        return $keywords;
    }
}
