<?php


namespace App\Controller\suggestions;


class SuggestionChoice
{

    /**
     * says whether the user is looking for a fertilizer suggestion or crop suggestion.
     */
    private $type;

    /**
     * defines the crop to fertilize or the area dimension on which planting the new crop.
     */
    private $data;


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