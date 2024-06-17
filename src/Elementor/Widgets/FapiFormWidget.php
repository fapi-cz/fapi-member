<?php

namespace FapiMember\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

final class FapiFormWidget extends Widget_Base {

	/**
	 * @var array<mixed>|null
	 */
	private array|null $formOptions = null;

	public function get_name() {
		return 'fapi_form';
	}

	public function get_title() {
		return esc_html__( 'FAPI form', 'fapi-member' );
	}

	public function get_icon() {
		return 'eicon-cart';
	}

	public function get_custom_help_url() {
		 return 'https://fapi.cz';
	}

	public function get_categories() {
		return array( 'fapi' );
	}

	public function get_keywords() {
		return array( 'fapi', 'sale', 'form' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'FAPI', 'fapi-member' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'path',
			array(
				'type'         => Controls_Manager::SELECT,
				'label'        => esc_html__( 'Prodejní formulář', 'fapi-member' ),
				'options'      => $this->getFormOptions(),
				'default'      => '',
				'prefix_class' => 'form-control',
			)
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo sprintf( '<script type="text/javascript" src="https://form.fapi.cz/script.php?id=%s"></script>', $settings['path'] );
	}

	protected function content_template() {
		 echo '<div style="text-align: center"> <<< Zde bude prodejní formulář >>> </div>';
	}

	/**
	 * @return array<mixed>
	 */
	private function getFormOptions() {
		if ( $this->formOptions !== null ) {
			return $this->formOptions;
		}

		global $FapiPlugin;
		global $apiService;

		$allClientsForms = array();
		$clients = $apiService->getApiClients();

		foreach ($clients as $client) {
			$allClientsForms[$client->getConnection()->getApiUser()] = $client->getForms();
		}

		$this->formOptions = array(
			'' => esc_html__(
				'-- vyberte prodejní formulář --',
				'fapi-member'
			),
		);

		if ( $allClientsForms === false || empty( $allClientsForms ) ) {
			return $this->formOptions;
		}

		foreach ($allClientsForms as $client => $clientForms) {
			foreach ($clientForms as $form) {
				$this->formOptions[ $form['path'] ] = $form['name'] . sprintf( ' (%s)', $client );
			}
		}

		return $this->formOptions;
	}

}
