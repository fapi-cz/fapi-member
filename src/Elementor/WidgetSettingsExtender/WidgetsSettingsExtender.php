<?php

namespace FapiMember\Elementor\WidgetSettingsExtender;

use Elementor\Controls_Manager;
use WP_Term;

final class WidgetsSettingsExtender {


	private static $sections = null;

	public static function register() {
		add_action(
			'elementor/element/after_section_end',
			function ( $section, $section_id ) {
				if ( 'section_custom_attributes_pro' !== $section_id ) {
					return;
				}

				self::doExtend( $section );
			},
			10,
			2
		);
	}

	/**
	 * @return void
	 */
	public static function doExtend( $section ) {
		$section->start_controls_section(
			'fapi-member',
			array(
				'label' => __( 'FAPI Member', 'fapi-member' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$section->add_control(
			'hasSectionOrLevel',
			array(
				'label'       => __( 'Zobrazit pokud návštěvník', 'fapi-member' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'1' => array(
						'title' => esc_html__( 'je člen sekce/úrovně', 'fapi-member' ),
					),
					'0' => array(
						'title' => esc_html__( 'není členem sekce/úrovně', 'fapi-member' ),
					),
					''  => array(
						'title' => esc_html__( 'zobrazit všem návštěvníkům (vybrané sekce a úrovně se ignorují)', 'fapi-member' ),
					),
				),
				'description' => esc_html__( 'Obsah se zobrazí v případě že člen je/není přiřazený v členské sekci nebo úrovni nebo všem návštěvníkům.', 'fapi-member' ),
				'show_label'  => true,
				'default'     => '',
			)
		);

		$levels = self::getLevels();

		$section->add_control(
			'fapiSectionAndLevels',
			array(
				'label'    => esc_html__( 'Členské sekce a úrovně', 'fapi-member' ),
				'type'     => Controls_Manager::SELECT2,
				'multiple' => true,
				'options'  => $levels,
			)
		);

		$section->end_controls_section();
	}

	/**
	 * @return array<mixed>
	 */
	private static function getLevels() {
		global $FapiPlugin;

		if ( self::$sections !== null ) {
			return self::$sections;
		}

		self::$sections = array();
		$termEnvelopes  = $FapiPlugin->levels()->loadAsTermEnvelopes();

		foreach ( $termEnvelopes as $termEnvelope ) {
			$term = $termEnvelope->getTerm();

			if ( $term->parent === 0 ) {
				self::$sections[ $term->term_id ] = $term->name;

				continue;
			}

			/** @var WP_Term $parentTerm */
			$parentTerm = $FapiPlugin->levels()->loadById( $term->parent );

			self::$sections[ $term->term_id ] = $parentTerm->name . ' - ' . $term->name;
		}

		return self::$sections;
	}

}
