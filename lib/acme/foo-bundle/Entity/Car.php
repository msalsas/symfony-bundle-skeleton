<?php

namespace Acme\FooBundle\Entity;

class Car
{
    protected $id;
    protected $brand;
    protected $model;
    protected $user;
    protected $createdAt;

    public function __construct()
    {
        $now = new \DateTime();
        $this->createdAt = $now->getTimestamp();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    public function getModel()
    {
        return $this->model;
    }

    function setModel($model)
    {
        $this->model = $model;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}