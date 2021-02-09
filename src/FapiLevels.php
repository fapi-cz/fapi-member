<?php


class FapiLevels
{
    private $levels = null;

    public function loadAsTerms()
    {
        if ($this->levels === null) {
            $this->levels = get_terms(
                [
                    'taxonomy' => 'fapi_levels',
                    'hide_empty' => false,
                ]
            );
        }
        return $this->levels;
    }
}