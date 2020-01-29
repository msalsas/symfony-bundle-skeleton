<?php

namespace Acme\FooBundle\DTO;

use Acme\FooBundle\Entity\Car;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CarDTO implements \JsonSerializable
{
    const ID = 'id';
    const BRAND = 'brand';
    const MODEL = 'model';

    protected $id;
    protected $brand;
    protected $model;

    public function __construct($id, $brand, $model)
    {
        $this->id = $id;
        $this->brand = $brand;
        $this->model = $model;
    }

    public static function fromRequest(Request $request)
    {
        $builder = new CarDTOBuilder();

        return $builder
            ->withId($request->request->get(self::ID))
            ->withBrand($request->request->get(self::BRAND))
            ->withModel($request->request->get(self::MODEL))
            ->build();

    }

    public static function toResponse(CarDTO $carDTO)
    {
        $response = new Response();
        $response->setContent($carDTO->jsonSerialize());

        return $response;
    }

    public static function toDTO(Car $car)
    {
        $builder = new CarDTOBuilder();

        return $builder
            ->withId($car->getId())
            ->withBrand($car->getBrand())
            ->withModel($car->getModel())
            ->build();
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

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'brand' => $this->brand,
            'model' => $this->model,
        );
    }
}