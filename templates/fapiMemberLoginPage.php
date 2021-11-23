<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php use FapiMember\FapiMemberTools;

		echo __('Přihlášení do členské sekce') ?></title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
	<?php wp_head() ?>
</head>
<body>
<div id="Wrapper">
    <h2 style="text-align: center;"><?php echo __('Přihlášení do členské sekce') ?></h2>

    <div class="pages">
        <div style="margin: 0 auto; width: max-content">
			<?php echo FapiMemberTools::shortcodeLoginForm(); ?>
        </div>
    </div>
</div>
<?php wp_title(); ?>
</body>
</html>
