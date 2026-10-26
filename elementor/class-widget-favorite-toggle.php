<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPL_Widget_Favorite_Toggle extends \Elementor\Widget_Base {

	public function get_name() {
		return 'cpl_favorite_toggle';
	}

	public function get_title() {
		return 'CPL Favorito';
	}

	public function get_icon() {
		return 'eicon-heart-o';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_script_depends() {
		return [ 'cpl-frontend' ];
	}

	public function get_style_depends() {
		return [ 'cpl-frontend' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => 'Contenido',
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'       => 'Texto',
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'Favorito',
			]
		);

		$this->add_control(
			'main_icon',
			[
				'label'   => 'Ícono',
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-hand-paper',
					'library' => 'fa-solid',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_background',
			[
				'label' => 'Fondo',
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => 'Padding',
				'type'  => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => 'Border Radius',
				'type'  => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'button_style_tabs' );

		$this->start_controls_tab(
			'button_tab_normal',
			[
				'label' => 'Normal',
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label'     => 'Color de fondo',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_tab_hover',
			[
				'label' => 'Hover',
			]
		);

		$this->add_control(
			'button_bg_color_hover',
			[
				'label'     => 'Color de fondo hover',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_tab_active',
			[
				'label' => 'Activo',
			]
		);

		$this->add_control(
			'button_bg_color_active',
			[
				'label'     => 'Color de fondo activo',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle.is-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => 'Ícono',
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => 'Tamaño',
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle__icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cpl-toggle__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'icon_style_tabs' );

		$this->start_controls_tab(
			'icon_tab_normal',
			[
				'label' => 'Normal',
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => 'Color',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7A7A7A',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle .cpl-toggle__icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .cpl-toggle .cpl-toggle__icon svg' => 'fill: {{VALUE}};',
				],
			]
		);


		$this->end_controls_tab();

		$this->start_controls_tab(
			'icon_tab_hover',
			[
				'label' => 'Hover',
			]
		);

		$this->add_control(
			'icon_color_hover',
			[
				'label'     => 'Color hover',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#394C6A',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle:hover .cpl-toggle__icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .cpl-toggle:hover .cpl-toggle__icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'icon_tab_active',
			[
				'label' => 'Activo',
			]
		);

		$this->add_control(
			'icon_color_active',
			[
				'label'     => 'Color activo',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#394C6A',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle.is-active .cpl-toggle__icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .cpl-toggle.is-active .cpl-toggle__icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_text',
			[
				'label' => 'Texto',
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_spacing',
			[
				'label' => 'Espacio entre ícono y texto',
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .cpl-toggle__text',
			]
		);

		$this->start_controls_tabs( 'text_style_tabs' );

		$this->start_controls_tab(
			'text_tab_normal',
			[
				'label' => 'Normal',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => 'Color texto',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7A7A7A',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle__text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'text_tab_hover',
			[
				'label' => 'Hover',
			]
		);

		$this->add_control(
			'text_color_hover',
			[
				'label'     => 'Color texto hover',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#394C6A',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle:hover .cpl-toggle__text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'text_tab_active',
			[
				'label' => 'Activo',
			]
		);

		$this->add_control(
			'text_color_active',
			[
				'label'     => 'Color texto activo',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#394C6A',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle.is-active .cpl-toggle__text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$post_id = get_the_ID();

		if ( ! $post_id || 'vehiculo' !== get_post_type( $post_id ) ) {
			return;
		}

		$settings    = $this->get_settings_for_display();
		$is_active   = false;
		$button_text = ! empty( $settings['button_text'] ) ? $settings['button_text'] : '';

		if ( ! CPL_Plugin::is_elementor_editor_context() ) {
			$session_key = CPL_Session::get_session_key();

			if ( ! empty( $session_key ) ) {
				$is_active = CPL_Database::selection_exists( $session_key, $post_id, 'favorite' );
			}
		}

		$this->add_render_attribute(
			'button',
			[
				'class'          => 'cpl-toggle cpl-toggle-favorite' . ( $is_active ? ' is-active' : '' ),
				'type'           => 'button',
				'data-post-id'   => $post_id,
				'data-list-type' => 'favorite',
				'aria-pressed'   => $is_active ? 'true' : 'false',
			]
		);

		?>
		<button <?php echo $this->get_render_attribute_string( 'button' ); ?>>
			<span class="cpl-toggle__icon" aria-hidden="true">
				<?php \Elementor\Icons_Manager::render_icon( $settings['main_icon'], [ 'aria-hidden' => 'true' ] ); ?>
			</span>

			<?php if ( '' !== $button_text ) : ?>
				<span class="cpl-toggle__text"><?php echo esc_html( $button_text ); ?></span>
			<?php endif; ?>
		</button>
		<?php
	}
}
