<?php

use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Utils\AlertProvider;

echo FapiMemberTools::heading();
?>
<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __( 'Členské sekce/úrovně', 'fapi-member' ); ?></h3>
			<?php echo AlertProvider::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelection() ?>
        </div>
        <div class="b">
        </div>
    </div>
</div>
</div>
