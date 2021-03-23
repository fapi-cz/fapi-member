<!doctype html>
<html lang="cs">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title><?php echo  bloginfo( 'name' ) ?> Výběr úrovně</title>
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <?php wp_head() ?>
    </head>
    <body class="FapiLevelSelection">
        <div id="Wrapper">
            <h1>Výběr sekce</h1>
            <p>Prosím, zvolte jednu ze stránek, kam chcete vstoupit</p>
            <div class="pages">
				<?php
				$args  = array(
					'post_type' => 'page',
					'post__in'  => array_values( $pages )
				);
				$posts = get_posts( $args );
				foreach ( $posts as $post ) {
					if ( has_excerpt( $post ) ) {
						$excerpt = get_the_excerpt( $post );
					} else {
						$text    = strip_shortcodes(
							wp_strip_all_tags(
								get_the_content( null, null, $post )
							)
						);
						$excerpt = wp_trim_words( $text, 16, '' );
					}


					?>
                    <div>
						<?php
						if ( has_post_thumbnail( $post ) ) {
							echo get_the_post_thumbnail( $post, 'level-selection' );
						} else {
							echo '<div class="thumbPlaceholder"></div>';
						}
						?>
                        <h3><?php echo  get_the_title( $post ) ?></h3>
                        <p>
							<?php echo  $excerpt ?>
                        </p>
                        <div class="actions">
                            <a href="<?php echo  get_permalink( $post ) ?>">Vstoupit</a>
                        </div>
                    </div>
				<?php } ?>
            </div>
        </div>
		<?php wp_title(); ?>
    </body>
</html>