<?php

global $FapiPlugin;
global $membershipRepository;

use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Utils\AlertProvider;
use FapiMember\Deprecated\FapiLevels;
use FapiMember\Deprecated\FapiMembership;

$fapiLevels = $FapiPlugin->levels();

echo FapiMemberTools::heading();
?>

<div class="page smallerPadding">
    <h3><?php echo __( 'Přehled členských sekcí a úrovní', 'fapi-member' ); ?></h3>
	<?php echo AlertProvider::showErrors(); ?>
    <div class="sectionsOverview">
		<?php

		$all_stored_post_types = get_option(OptionKey::POST_TYPES, array());

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

		foreach($pagesCount as $level_id => &$pages_count) {

			if (isset($all_stored_post_types[$level_id])) {

	            $posts = get_posts(
		            array(
			            'post_type'   => $all_stored_post_types[$level_id],
			            'post_status' => array( 'publish' ),
			            'numberposts' => -1,
       			    )
	            );

	            $pages_count += count($posts);
    		}
		}
		unset($pages_count);

		$empty = true;

		$memberships = $membershipRepository->getAll();
		$usersInLevel = [];

		foreach ($memberships as $userId => $userMemberships) {
			/** @var FapiMembership[] $ms */
			{
				foreach ($userMemberships as $membership) {
					if (!isset($usersInLevel[$membership->getLevelId()])) {
						$usersInLevel[$membership->getLevelId()] = [];
					}
					$usersInLevel[$membership->getLevelId()][$userId] = true;

					if (
						isset($mapToParent[$membership->getLevelId()]) &&
						$mapToParent[$membership->getLevelId()] !== 0
					) {
						// is child, count unique to parent too
						if (!isset($carry[$mapToParent[$membership->getLevelId()]])) {
							$carry[$mapToParent[$membership->getLevelId()]] = [];
						}
						$carry[$mapToParent[$membership->getLevelId()]][$userId] = true;
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
            <div class="mainLevel">
                <div class="name"><?php echo $level->name ?></div>
                <div class="levelCount"><?php echo __( 'Počet úrovní', 'fapi-member' ); ?>:
                    <span><?php echo (isset($levelCount[$level->term_id])) ? $levelCount[$level->term_id] : 0 ?></span>
                </div>
                <div class="membersCount"><?php echo __( 'Počet registrovaných', 'fapi-member' ); ?>:
                    <span><?php echo (isset($levelUsersCount[$level->term_id])) ? $levelUsersCount[$level->term_id] : 0 ?></span>
					<?php if (isset($levelUsersCount[$level->term_id])) {
						echo '<div><a href="' . FapiMemberTools::fapilink('memberList'.'&sectionID='.$level->term_id).'" class="btn outline">' . __('Seznam členů', 'fapi-member') . '</a></div>';
					}
					?>
                </div>
                <div class="pagesCount"><?php echo __( 'Stránek v celé sekci', 'fapi-member' ); ?>:
                    <span><?php echo (isset($pagesCount[$level->term_id])) ? $pagesCount[$level->term_id] : 0 ?></span>
					
                </div>	
            </div>
			<div class="subLevels collapsibleContent" style="max-height: 0px;">
				<?php
				$subLevels = get_term_children($level->term_id, FapiLevels::TAXONOMY);
				if (!is_wp_error($subLevels)) {
					foreach ($subLevels as $subLevel) { 
						$subLevelTerm=get_term_by('ID', $subLevel, FapiLevels::TAXONOMY);
						?>
						<div class="subLevel">
							<h3><?php echo $subLevelTerm->name ?></h3>
							<div class="membersCount" style="display:inline;"><?php echo __('Počet registrovaných', 'fapi-member'); ?>:
								<span><?php echo (isset($levelUsersCount[$subLevel])) ? $levelUsersCount[$subLevel] : 0 ?></span>
								<?php if (isset($levelUsersCount[$subLevel])) {
									echo '<div><a href="' . FapiMemberTools::fapilink('memberList') . '&sectionID=' . $subLevel . '" class="btn outline">' . __('Seznam členů', 'fapi-member') . '</a></div>';
								}
								?>
							</div>
						</div>
				<?php
					}
				}
				?>
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
