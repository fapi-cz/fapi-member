<?php


class FapiLevels
{
    const TAXONOMY = 'fapi_levels';


    public static $emailTypes = [
        'afterRegistration',
        'afterMembershipProlonged',
        'afterAdding'
    ];

    private $levels = null;
    private $levelsToPages = null;

    public function loadAsTerms()
    {
        if ($this->levels === null) {
            $this->levels = get_terms(
                [
                    'taxonomy' => self::TAXONOMY,
                    'hide_empty' => false,
                ]
            );
        }
        return $this->levels;
    }

    public function loadById($id)
    {
        if ($this->levels === null) {
            return get_term_by('ID', $id, self::TAXONOMY);
        }
        $f = array_filter($this->levels, function(WP_Term $one) use ($id) {
            return $one->term_id === (int)$id;
        });
        if (count($f) >= 1) {
            return array_values($f)[0];
        }
        return null;
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

    public function constructEmailTemplateKey($type)
    {
        return sprintf('fapi_email_%s', $type);
    }

    public function loadEmailTemplatesForLevel($levelId, $useCascade = false)
    {
        $meta = [];
        foreach (self::$emailTypes as $type) {
            $template = get_term_meta($levelId, $this->constructEmailTemplateKey($type), true);
            if (!empty($template)) {
                $meta[$type] = $template;
            }
        }
        return $meta;
    }
}