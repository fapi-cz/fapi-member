<?php

global $FapiPlugin;

use FapiMember\FapiMembership;
use FapiMember\FapiMemberTools;

$fapiLevels = $FapiPlugin->levels();

echo FapiMemberTools::heading();
?>

<div class="page smallerPadding">
    <h3><?php echo __( 'Přehled členských sekcí a úrovní', 'fapi-member' ); ?></h3>
	<?php echo FapiMemberTools::showErrors(); ?>
    <div class="sectionsOverview">
		<?php
		$envelopes = $fapiLevels->loadAsTermEnvelopes();
		$mapToParent = array_reduce($envelopes,
			static function ($carry, $envelope) {
				$carry[$envelope->getTerm()->term_id] = $envelope->getTerm()->parent;

				return $carry;
			},
			[]
		);

		$topEnvelopes = array_filter($envelopes,
			static function ($envelope) {
				return $envelope->getTerm()->parent === 0;
			}
		);
		$pagesCount = [];
		$levelsToPages = $fapiLevels->levelsToPages();
		$levelCount = array_reduce($envelopes,
			static function ($carry, $envelope) use (&$pagesCount, $levelsToPages) {
				$one = $envelope->getTerm();
				$carry[$one->parent] = (isset($carry[$one->parent])) ? $carry[$one->parent] + 1 : 1;
				if ($one->parent === 0) {
					$pagesCount[$one->term_id] = isset($pagesCount[$one->term_id]) ? $pagesCount[$one->term_id] + count($levelsToPages[$one->term_id]) : count($levelsToPages[$one->term_id]);
				} else {
					$pagesCount[$one->parent] = isset($pagesCount[$one->parent]) ? $pagesCount[$one->parent] + count($levelsToPages[$one->term_id]) : count($levelsToPages[$one->term_id]);
				}

				return $carry;
			},
			[]);

		$empty = true;

		$memberships = $FapiPlugin->getAllMemberships();
		$usersInLevel = [];
		foreach ($memberships as $userId => $ms) {
			/** @var FapiMembership[] $ms */
			{
				foreach ($ms as $m) {
					if (!isset($usersInLevel[$m->level])) {
						$usersInLevel[$m->level] = [];
					}
					$usersInLevel[$m->level][$userId] = true;
					if (isset($mapToParent[$m->level]) && $mapToParent[$m->level] !== 0) {
						// is child, count unique to parent too
						if (!isset($carry[$mapToParent[$m->level]])) {
							$carry[$mapToParent[$m->level]] = [];
						}
						$carry[$mapToParent[$m->level]][$userId] = true;
					}
				}
			}
		}
		$levelUsersCount = [];
		foreach ($usersInLevel as $level => $users) {
			$levelUsersCount[$level] = count($users);
		}

		foreach ($topEnvelopes as $envelope) {
			$level = $envelope->getTerm();
			$empty = false;
			?>
            <div>
                <div class="name"><?php echo $level->name ?></div>
                <div class="levelCount"><?php echo __( 'Počet úrovní', 'fapi-member' ); ?>:
                    <span><?php echo (isset($levelCount[$level->term_id])) ? $levelCount[$level->term_id] : 0 ?></span>
                </div>
                <div class="membersCount"><?php echo __( 'Počet registrovaných', 'fapi-member' ); ?>:
                    <span><?php echo (isset($levelUsersCount[$level->term_id])) ? $levelUsersCount[$level->term_id] : 0 ?></span>
                </div>
                <div class="pagesCount"><?php echo __( 'Stránek v celé sekci', 'fapi-member' ); ?>:
                    <span><?php echo (isset($pagesCount[$level->term_id])) ? $pagesCount[$level->term_id] : 0 ?></span>
                </div>
            </div>
		<?php } ?>

    </div>
	<?php if ($empty) { ?>

        <div class="emptyIndex">
            <img src="<?php echo plugin_dir_url(__FILE__) . '../media/membership.svg' ?>">
            <p class="gray">
				<?php echo __( 'Nemáte vytvořenou žádnou členskou sekci.', 'fapi-member' ); ?><br>
				<?php echo __( 'Novou sekci můžete vytvořit na záložce Sekce / úrovně.', 'fapi-member' ); ?>
            </p>
            <a href="<?php echo FapiMemberTools::fapilink('settingsSectionNew') ?>" class="btn primary">
				<?php echo __( 'Přejít do záložky Sekce / úrovně', 'fapi-member' ); ?></a>
        </div>

	<?php } ?>
</div>
<?php echo FapiMemberTools::help() ?>
</div>
