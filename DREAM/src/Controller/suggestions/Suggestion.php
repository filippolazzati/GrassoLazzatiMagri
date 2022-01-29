<?php


namespace App\Controller\suggestions;


class Suggestion
{
    private $data;

    private $type;

    private $number;

    private $reliability;

    /**
     * Suggestion constructor.
     * @param $data
     * @param $type
     * @param $number
     * @param $reliability
     */
    public function __construct($data, $type, $number, $reliability)
    {
        $this->data = $data;
        $this->type = $type;
        $this->number = $number;
        $this->reliability = $reliability;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number): void
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getReliability()
    {
        return $this->reliability;
    }

    /**
     * @param mixed $reliability
     */
    public function setReliability($reliability): void
    {
        $this->reliability = $reliability;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }


}