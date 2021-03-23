<?php

include( __DIR__ . '/functions.php' );
global $FapiPlugin;
$fapiLevels = $FapiPlugin->levels();

echo heading();
?>

<div class="page smallerPadding">
    <h3>Přehled členských sekcí a úrovní</h3>
	<?php echo showErrors(); ?>
    <div class="sectionsOverview">
		<?php
		$levels      = $fapiLevels->loadAsTerms();
		$mapToParent = array_reduce( $levels,
			function ( $carry, $l ) {
				$carry[ $l->term_id ] = $l->parent;

				return $carry;
			},
			                         [] );

		$topLevels     = array_filter( $levels,
			function ( $l ) {
				return $l->parent === 0;
			} );
		$pagesCount    = [];
		$levelsToPages = $fapiLevels->levelsToPages();
		$levelCount    = array_reduce( $levels,
			function ( $carry, $one ) use ( &$pagesCount, $levelsToPages ) {
				$carry[ $one->parent ] = ( isset( $carry[ $one->parent ] ) ) ? $carry[ $one->parent ] + 1 : 1;
				if ( $one->parent === 0 ) {
					$pagesCount[ $one->term_id ] = isset( $pagesCount[ $one->term_id ] ) ? $pagesCount[ $one->term_id ] + count( $levelsToPages[ $one->term_id ] ) : count( $levelsToPages[ $one->term_id ] );
				} else {
					$pagesCount[ $one->parent ] = isset( $pagesCount[ $one->parent ] ) ? $pagesCount[ $one->parent ] + count( $levelsToPages[ $one->term_id ] ) : count( $levelsToPages[ $one->term_id ] );
				}

				return $carry;
			},
			                           [] );

		$empty = true;

		$memberships  = $FapiPlugin->getAllMemberships();
		$usersInLevel = [];
		foreach ( $memberships as $userId => $ms ) {
			/** @var FapiMembership[] $ms */
			{
				foreach ( $ms as $m ) {
					if ( ! isset( $usersInLevel[ $m->levelId ] ) ) {
						$usersInLevel[ $m->levelId ] = [];
					}
					$usersInLevel[ $m->levelId ][ $userId ] = true;
					if ( isset( $mapToParent[ $m->levelId ] ) && $mapToParent[ $m->levelId ] !== 0 ) {
						// is child, count unique to parent too
						if ( ! isset( $carry[ $mapToParent[ $m->levelId ] ] ) ) {
							$carry[ $mapToParent[ $m->levelId ] ] = [];
						}
						$carry[ $mapToParent[ $m->levelId ] ][ $userId ] = true;
					}
				}
			}
		}
		$levelUsersCount = [];
		foreach ( $usersInLevel as $level => $users ) {
			$levelUsersCount[ $level ] = count( $users );
		}


		foreach ( $topLevels as $level ) {
			$empty = false;
			?>
            <div>
                <div class="name"><?php echo  $level->name ?></div>
                <div class="levelCount">Počet
                    úrovní: <?php echo  ( isset( $levelCount[ $level->term_id ] ) ) ? $levelCount[ $level->term_id ] : 0 ?></div>
                <div class="membersCount">Počet
                    registrovaných: <?php echo  ( isset( $levelUsersCount[ $level->term_id ] ) ) ? $levelUsersCount[ $level->term_id ] : 0 ?></div>
                <div class="pagesCount">Stránek v celé
                    sekci: <?php echo  ( isset( $pagesCount[ $level->term_id ] ) ) ? $pagesCount[ $level->term_id ] : 0 ?></div>
            </div>
		<?php } ?>

    </div>
	<?php if ( $empty ) { ?>

        <div class="emptyIndex">
            <img src="<?php echo  plugin_dir_url( __FILE__ ) . '../media/membership.svg' ?>">
            <p class="gray">
                Nemáte vytvořenou žádnou členskou sekci.<br>
                Novou sekci můžete vytvořit na záložce Sekce / úrovně.
            </p>
            <a href="<?php echo  fapilink( 'settingsSectionNew' ) ?>" class="btn primary">Přejít do záložky Sekce / úrovně</a>
        </div>

	<?php } ?>
</div>
<?php echo  help() ?>
</div>