<!doctype html>
<html lang="cs">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title><?= bloginfo('name') ?> Výběr úrovně</title>
        <link rel="stylesheet" href="<?= plugin_dir_url(__DIR__ ).'/../media/fapi-member-public.css' ?>">
    </head>
    <body class="FapiLevelSelection">
        <div id="Wrapper">
            <h1><?= bloginfo('name') ?>: Výběr úrovně</h1>
            <p>Prosím zvolte jednu z výchozích stránek:</p>
            <div class="pages">
                <?php
                $args = array(
                    'post_type' => 'page',
                    'post__in' => array_values($pages)
                );
                $posts = get_posts($args);
                foreach ($posts as $post) {
                ?>
                    <div>
                        <?php
                            if (has_post_thumbnail($post)) {
                                echo get_the_post_thumbnail($post, 'level-selection');
                            }
                        ?>
                        <h3><?= get_the_title($post) ?></h3>
                        <div class="actions">
                            <a href="<?= get_permalink($post) ?>">Číst dále</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php wp_title(); ?>
    </body>
</html>