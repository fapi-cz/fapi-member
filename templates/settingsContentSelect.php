<?php

include( __DIR__ . '/functions.php' );

echo FapiMemberTools::heading();
?>
<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3>Struktura uzavřených sekcí a úrovní</h3>
			<?php echo FapiMemberTools::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelection() ?>
        </div>
        <div class="b">
        </div>
    </div>
</div>
</div>