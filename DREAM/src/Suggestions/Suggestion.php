<?php


namespace App\Suggestions;

/**
 * Class Suggestion
 * @package App\Controller\suggestions
 *
 * Helper class used to show the suggestions to the user. It contains
 * three attributes that represents the fields that will be shown to the user.
 */
class Suggestion
{
    /**
     * It represents the out-and-out suggestion to the user.
     */
    private $data;

    /**
     * A number representing the "ranking" of this suggestion.
     */
    private $number;

    /**
     * The reliability of the suggestion, i.e. the output of the last layer of the net.
     */
    private $reliability;

    /**
     * Suggestion constructor.
     * @param mixed $data
     * @param mixed $number
     * @param mixed $reliability
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