<?php


namespace App\Controller\suggestions;


class Suggestion
{
    private $data;

    private $number;

    private $reliability;

    /**
     * Suggestion constructor.
     * @param $data
     * @param $number
     * @param $reliability
     */
    public function __construct($data, $number, $reliability)
    {
        $this->data = $data;
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

}