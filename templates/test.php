<?php

global $FapiPlugin;

use FapiMember\Utils\AlertProvider;

$fapiLevels = $FapiPlugin->levels();

echo FapiMemberTools::heading();
?>

<div class="page smallerPadding">
    <h3>Test</h3>
	<?php echo AlertProvider::showErrors(); ?>
    <pre>
        
    </pre>

</div>
