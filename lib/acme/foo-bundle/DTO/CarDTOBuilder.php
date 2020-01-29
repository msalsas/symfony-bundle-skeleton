<?php

namespace Acme\FooBundle\DTO;

class CarDTOBuilder
{
    protected $id;
    protected $brand;
    protected $model;

    public function withId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function withBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    public function withModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function build()
    {
        return new CarDTO(
            $this->id,
            $this->brand,
            $this->model
        );
    }
}