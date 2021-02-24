<?php


class FapiMembershipLoader
{
    const MEMBERSHIP_META_KEY = 'fapi_user_memberships';

    public $levelId;
    public $registered;
    public $until;

    protected $fapiLevels;
    protected $levels;

    public function __construct(FapiLevels $levels)
    {
        $this->fapiLevels = $levels;
    }

    public function levels()
    {
        if ($this->levels === null) {
            $this->levels = $this->fapiLevels->loadAsTerms();
        }
        return $this->levels;
    }

    /**
     * @param int $userId
     * @param FapiMembership[] $memberships
     */
    public function saveForUser($userId, $memberships)
    {
        $meta = [];
        $meta = array_map(function($one) {
            /** @var FapiMembership $one */
            $t = [
                'level' => $one->levelId,
            ];
            if ($one->registered instanceof DateTimeInterface) {
                $t['registered'] = $one->registered->format(FapiMemberPlugin::DF);
                $t['until'] = $one->until->format(FapiMemberPlugin::DF);
            }
            return $t;
        }, $memberships);
        update_user_meta( $userId, self::MEMBERSHIP_META_KEY, $meta );
    }

    public function loadForUser($userId, $removeFuture = false)
    {
        $meta = get_user_meta($userId, self::MEMBERSHIP_META_KEY, true);
        if ($meta === '') {
            return [];
        }
        $atStart = count($meta);
        // cleanup - remove nonexistent levels, remove outdated memberships
        $levelIds = array_reduce($this->levels(), function($carry, $one) {
            $carry[] = $one->term_id;
            return $carry;
        }, []);
        $meta = array_filter($meta, function($one) use ($levelIds) {
             return in_array($one['level'], $levelIds);
        });
        $meta = array_filter($meta, function($one) use ($removeFuture) {
            $now = new DateTime();
            if (!isset($one['until'])) {
                // is child level
                return true;
            }
            $until = DateTime::createFromFormat(FapiMemberPlugin::DF, $one['until']);
            if ($until < $now) {
                return false;
            }
            $registered = DateTime::createFromFormat(FapiMemberPlugin::DF, $one['registered']);
            if ($removeFuture && ($registered > $now)) {
                return false;
            }
            return true;
        });
        // remove orphaned children
        $levels = $this->levels();
        $metaLevelsId = array_reduce($meta, function($carry, $one) {
            $carry[] = $one['level'];
            return $carry;
        }, []);
        $meta = array_filter($meta, function($one) use ($levels, $metaLevelsId) {
            if (isset($one['registered'])) {
                // is parent level
                return true;
            }
            $levelKey = array_search($one['level'], array_column($levels, 'term_id'));
            $level = $levels[$levelKey];
            $parentId = $level->parent;
            return in_array($parentId, $metaLevelsId);
        });
        $atEnd = count($meta);
        $memberships = array_map(function($one) {
            $t = new FapiMembership($one['level']);

            $t->registered = (isset($one['registered'])) ?
                DateTime::createFromFormat(FapiMemberPlugin::DF, $one['registered']) :
                null;
            $t->until = (isset($one['until'])) ?
                DateTime::createFromFormat(FapiMemberPlugin::DF, $one['until']) :
                null;

            return $t;
        }, $meta);
        if ($atEnd !== $atStart) {
            $this->saveForUser($userId, $memberships);
        }
        return $memberships;
    }
}