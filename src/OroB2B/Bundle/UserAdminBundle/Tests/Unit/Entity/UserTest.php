<?php

namespace OroB2B\Bundle\UserAdminBundle\Tests\Unit\Entity;

use OroB2B\Bundle\UserAdminBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('test@example.com');

        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('test@example.com', $user->getUsername());
        $this->assertEquals('test@example.com', $user->getEmail());
    }
}