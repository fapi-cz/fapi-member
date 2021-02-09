<?php


class FapiLevels
{
    private $levels = null;
    private $levelsToPages = null;

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

    public function levelsToPages()
    {
        if ($this->levelsToPages === null) {
            $levels = array_map(function($one) {
                return [
                    'term_id' => $one->term_id,
                    'name' => $one->name,
                ];
            }, $this->loadAsTerms());

            $this->levelsToPages = array_reduce($levels, function($carry, $lvl) {
                $pages = get_term_meta($lvl['term_id'], 'fapi_pages', true);
                $carry[$lvl['term_id']] = (empty($pages)) ? [] : array_values(json_decode($pages));
                return $carry;
            }, []);
        }
        return $this->levelsToPages;
    }
}