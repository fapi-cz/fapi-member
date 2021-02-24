<?php


class FapiMembership
{
    public $levelId;
    public $registered;
    public $until;

    public function __construct($levelId, $registered = null, $until = null)
    {
        $this->levelId = $levelId;
        $this->registered = $registered;
        $this->until = $until;
    }
}