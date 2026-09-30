<?php declare(strict_types = 1);

namespace FapiMember\Divi5;

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

final class FormModule
{
	public static function register(): void
	{
		if (function_exists('et_builder_d5_enabled') && et_builder_d5_enabled() && class_exists(ModuleRegistration::class)) {
			ModuleRegistration::register_module(__DIR__ . '/form', ['render_callback' => [self::class, 'render']]);
		}
	}

	public static function renderEmbed(array $attrs): string
	{
		$path = $attrs['formPath']['innerContent']['desktop']['value'] ?? '';
		if (!is_string($path) || !preg_match('/\A[a-zA-Z0-9_-]+\z/', $path)) {
			return '';
		}

		return '<script type="text/javascript" src="' . esc_url('https://form.fapi.cz/script.php?id=' . rawurlencode($path)) . '"></script>';
	}

	public static function render(array $attrs, string $content, $block, $elements): string
	{
		return Module::render([
			'orderIndex' => $block->parsed_block['orderIndex'],
			'storeInstance' => $block->parsed_block['storeInstance'],
			'attrs' => $attrs,
			'elements' => $elements,
			'id' => $block->parsed_block['id'],
			'moduleClassName' => 'fapi_member_form',
			'name' => $block->block_type->name,
			'moduleCategory' => $block->block_type->category,
			'classnamesFunction' => [self::class, 'classnames'],
			'stylesComponent' => [self::class, 'styles'],
			'scriptDataComponent' => [self::class, 'scriptData'],
			'children' => $elements->style_components(['attrName' => 'module'])
				. '<div class="et_pb_module_inner">' . self::renderEmbed($attrs) . '</div>',
		]);
	}

	public static function classnames(array $args): void
	{
		$args['classnamesInstance']->add(ElementClassnames::classnames(['attrs' => $args['attrs']['module']['decoration'] ?? []]));
	}

	public static function styles(array $args): void
	{
		Style::add([
			'id' => $args['id'],
			'name' => $args['name'],
			'orderIndex' => $args['orderIndex'],
			'storeInstance' => $args['storeInstance'],
			'styles' => [$args['elements']->style([
				'attrName' => 'module',
				'styleProps' => ['disabledOn' => ['disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null]],
			])],
		]);
	}

	public static function scriptData(array $args): void
	{
		$args['elements']->script_data(['attrName' => 'module']);
	}
}
