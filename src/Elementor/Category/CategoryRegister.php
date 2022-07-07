<?php

namespace FapiMember\Elementor\Category;

final class CategoryRegister {

	public static function register() {
		add_action(
			'elementor/elements/categories_registered',
			function ( $elementsManager ) {
				$elementsManager->add_category(
					'fapi',
					array(
						'title' => esc_html__( 'FAPI', 'fapi-member' ),
						'icon'  => 'fa fa-cart',
					)
				);
			}
		);
	}

}
