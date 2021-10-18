<?php


class FapiMembership
{
    /**
     * @var int 
     */
    public $level;
    public $registered;
    public $until;
    public $isUnlimited = false;

    public function __construct( $level, $registered = null, $until = null, $isUnlimited = false )
    {
        $this->level       = $level;
        $this->registered  = $registered;
        $this->until       = $until;
        $this->isUnlimited = $isUnlimited;
    }
}