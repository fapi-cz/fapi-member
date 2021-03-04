<?php


class FapiMembershipLoader
{
    const MEMBERSHIP_META_KEY = 'fapi_user_memberships';
    const MEMBERSHIP_HISTORY_META_KEY = 'fapi_user_memberships_history';

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
        if (count($memberships) === 0) {
            return;
        }
        $meta = [];
        $meta = array_map(function($one) {
            $t = (array)$one;
            if ($one->registered instanceof DateTimeInterface) {
                $t['registered'] = $one->registered->format(FapiMemberPlugin::DF);
            }
            if ($one->until instanceof DateTimeInterface) {
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
            $until = DateTime::createFromFormat(FapiMemberPlugin::DF, $one['until']);
            if ($until < $now && !$one['isUnlimited']) {
                return false;
            }
            $registered = DateTime::createFromFormat(FapiMemberPlugin::DF, $one['registered']);
            if ($removeFuture && ($registered > $now)) {
                return false;
            }
            return true;
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
            $t->isUnlimited = $one['isUnlimited'];

            return $t;
        }, $meta);
        if ($atEnd !== $atStart) {
            $this->saveForUser($userId, $memberships);
        }
        return $memberships;
    }

    public function saveMembershipToHistory($userId, FapiMembership $membership)
    {
        $meta = get_user_meta($userId, self::MEMBERSHIP_HISTORY_META_KEY, true);
        if ($meta === '') {
            $meta =  [];
        }
        $meta[] = $membership;
        update_user_meta($userId, self::MEMBERSHIP_HISTORY_META_KEY, $meta);
    }

    public function didUserHadLevelMembershipBefore($userId, $levelId)
    {
        $memberships = get_user_meta($userId, self::MEMBERSHIP_HISTORY_META_KEY, true);
        if ($memberships === '') {
            $memberships =  [];
        }
        foreach ($memberships as $m) {
            /** @var FapiMembership $m */
            if ($m->level === $levelId) {
                return true;
            }
        }
        return false;
    }
}