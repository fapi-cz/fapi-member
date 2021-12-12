<?php

use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
?>
<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __('Členské sekce/úrovně', 'fapi'); ?></h3>
			<?php echo FapiMemberTools::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelection() ?>
        </div>
        <div class="b">
        </div>
    </div>
</div>
</div>
