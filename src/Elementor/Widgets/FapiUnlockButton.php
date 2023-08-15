<?php

namespace FapiMember\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Includes\Widgets\Traits\Button_Trait;

use FapiMember\FapiLevels;
use FapiMember\FapiMemberTools;

class FapiUnlockButton extends Widget_Base {

	use Button_Trait;

	public function get_name() {
		return 'FAPI Unlock Button';
	}

	public function get_title() {
		return esc_html__( 'FAPI Unlock Button', 'fapi-member' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_categories() {
		return [ 'fapi' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_button',
			[
				'label' => esc_html__( 'FAPI Unlock Button', 'fapi-member' ),
			]
		);

		$this->add_unlock_button_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'FAPI Unlock Button', 'fapi-member' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->register_button_style_controls();

		$this->end_controls_section();
	}

	protected function add_unlock_button_controls(){

		$args = [
			'section_condition' => [],
			'button_default_text' => esc_html__( 'Click here', 'elementor' ),
			'text_control_label' => esc_html__( 'Text', 'elementor' ),
			'alignment_control_prefix_class' => 'elementor%s-align-',
			'alignment_default' => '',
			'icon_exclude_inline_options' => [],
		];

		$this->add_control(
			'unlock_level',
			[
				'label' => esc_html__( 'Spřístupnit úroveň', 'fapi-member' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_applicable_levels(),
				'prefix_class' => 'elementor-button-',
				'condition' => $args['section_condition'],
				'description' => esc_html__( 'Na výběr jsou jen sekce, pro které bylo povoleno nastavení "Vyžadovat dokončení úrovně"
											  v části "Postupné uvolňování obsahu" FAPI Member' , 'fapi-member' )
			]
		);

		$this->add_control(
			'button_type',
			[
				'label' => esc_html__( 'Type', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( 'Default', 'elementor' ),
					'info' => esc_html__( 'Info', 'elementor' ),
					'success' => esc_html__( 'Success', 'elementor' ),
					'warning' => esc_html__( 'Warning', 'elementor' ),
					'danger' => esc_html__( 'Danger', 'elementor' ),
				],
				'prefix_class' => 'elementor-button-',
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
			'text',
			[
				'label' => $args['text_control_label'],
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => $args['button_default_text'],
				'placeholder' => $args['button_default_text'],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => esc_html__( 'Alignment', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
						'title' => esc_html__( 'Left', 'elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'elementor' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'elementor' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => $args['alignment_control_prefix_class'],
				'default' => $args['alignment_default'],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
			'size',
			[
				'label' => esc_html__( 'Size', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'sm',
				'options' => self::get_button_sizes(),
				'style_transfer' => true,
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label' => esc_html__( 'Icon', 'elementor' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin' => 'inline',
				'label_block' => false,
				'condition' => $args['section_condition'],
				'icon_exclude_inline_options' => $args['icon_exclude_inline_options'],
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label' => esc_html__( 'Icon Position', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left' => esc_html__( 'Before', 'elementor' ),
					'right' => esc_html__( 'After', 'elementor' ),
				],
				'condition' => array_merge( $args['section_condition'], [ 'selected_icon[value]!' => '' ] ),
			]
		);

		$this->add_control(
			'icon_indent',
			[
				'label' => esc_html__( 'Icon Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elementor-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
			'view',
			[
				'label' => esc_html__( 'View', 'elementor' ),
				'type' => Controls_Manager::HIDDEN,
				'default' => 'traditional',
				'condition' => $args['section_condition'],
			]
		);

		$this->add_control(
			'button_css_id',
			[
				'label' => esc_html__( 'Button ID', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'ai' => [
					'active' => false,
				],
				'default' => '',
				'title' => esc_html__( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'elementor' ),
				'description' => sprintf(
					esc_html__( 'Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows %1$sA-z 0-9%2$s & underscore chars without spaces.', 'elementor' ),
					'<code>',
					'</code>'
				),
				'separator' => 'before',
				'condition' => $args['section_condition'],
			]
		);
	}

	protected function get_applicable_levels(){
		global $FapiPlugin;
		$taxonomy = FapiLevels::TAXONOMY;
    	$args = array(
        	'taxonomy' => $taxonomy,
        	'hide_empty' => false,
    	);
    
    	$terms	= get_terms( $args );
    	$levels = array();

    	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        	foreach ( $terms as $term ) {
            	if ($term->parent !== 0){
            	$levels[] = array( 'id' => $term->term_id,
                            	   'name' => $term->name  );
            	}
        	}
    	}

		$filtered = array_filter( $levels, function ( $level ) use ( $FapiPlugin ) {
			
			$meta = get_term_meta( $level['id'], $FapiPlugin::LEVEL_UNLOCKING_META_KEY, true );
			if ( empty( $meta[ 'require_completion' ] ) ){
				return false;
			}
			if ( $meta[ 'require_completion' ] ){
				return true;
			}
		});

		$out = array();

		foreach ($filtered as $level) {
			$out[$level['id']] = $level['name'];
		}

		return array( '' =>esc_html( 'Zvolte úroveň', 'fapi-member')) + $out;

	}

	protected function render( Widget_Base $instance = null ) {

		if ( empty( $instance ) ) {
			$instance = $this;
		}
		
		$settings = $instance->get_settings_for_display();

		$levelToUnlock = !empty($settings['unlock_level']) ? $settings['unlock_level'] : '';

		$instance->add_render_attribute( 'wrapper', 'class', 'elementor-button-wrapper' );

		$instance->add_render_attribute( 'button', 'class', 'elementor-button' );

		$instance->add_render_attribute( 'button', 'type', 'submit' );

		if ( ! empty( $settings['button_css_id'] ) ) {
			$instance->add_render_attribute( 'button', 'id', $settings['button_css_id'] );
		}

		if ( ! empty( $settings['size'] ) ) {
			$instance->add_render_attribute( 'button', 'class', 'elementor-size-' . $settings['size'] );
		}

		if ( ! empty( $settings['hover_animation'] ) ) {
			$instance->add_render_attribute( 'button', 'class', 'elementor-animation-' . $settings['hover_animation'] );
		}

		echo FapiMemberTools::formStart('level_button_unlocking');
		?>
		<div <?php $instance->print_render_attribute_string( 'wrapper' ); ?>>
			<input type="hidden" name="unlock_level" value="<?php echo $levelToUnlock ?>">
			<button <?php $instance->print_render_attribute_string( 'button' ); ?>>
				<?php $this->render_text( $instance ); ?>
			</button>
		</div>
		</form>
		<?php
	}
}
