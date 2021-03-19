<?php

include( __DIR__ . '/functions.php' );

echo heading();

$topic = ( isset( $_GET['topic'] ) ) ? $_GET['topic'] : null;
$path  = ( $topic ) ? sprintf( '%s/help/%s.php', __DIR__, $topic ) : null;
if ( $path && file_exists( $path ) ) {
	include $path;
} else {
	include __DIR__ . '/help/_none.php';
}

echo help();
?>
</div>