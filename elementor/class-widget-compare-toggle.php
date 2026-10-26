<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPL_Widget_Compare_Toggle extends \Elementor\Widget_Base {

	public function get_name() {
		return 'cpl_compare_toggle';
	}

	public function get_title() {
		return 'CPL Comparar';
	}

	public function get_icon() {
		return 'eicon-radio';
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
				'default'     => 'Comparar',
				'placeholder' => 'Comparar',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_selector',
			[
				'label' => 'Selector',
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'selector_size',
			[
				'label' => 'Tamaño del círculo',
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 16,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cpl-compare__bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'selector_border_width',
			[
				'label' => 'Grosor del borde',
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 1,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cpl-compare__bullet' => 'border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'selector_dot_size',
			[
				'label' => 'Tamaño del punto interno',
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 8,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 2,
						'max' => 40,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cpl-compare__bullet::after' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'selector_spacing',
			[
				'label' => 'Espacio entre círculo y texto',
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 8,
					'unit' => 'px',
				],
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

		$this->start_controls_tabs( 'selector_style_tabs' );

		$this->start_controls_tab(
			'selector_tab_normal',
			[
				'label' => 'Normal',
			]
		);

		$this->add_control(
			'selector_color',
			[
				'label'     => 'Color',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7A7A7A',
				'selectors' => [
					'{{WRAPPER}} .cpl-compare__bullet' => 'border-color: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'selector_tab_hover',
			[
				'label' => 'Hover',
			]
		);

		$this->add_control(
			'selector_color_hover',
			[
				'label'     => 'Color hover',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#394C6A',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle:hover .cpl-compare__bullet' => 'border-color: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'selector_tab_active',
			[
				'label' => 'Activo',
			]
		);

		$this->add_control(
			'selector_color_active',
			[
				'label'     => 'Color activo',
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#394C6A',
				'selectors' => [
					'{{WRAPPER}} .cpl-toggle.is-active .cpl-compare__bullet' => 'border-color: {{VALUE}}; color: {{VALUE}};',
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
				$is_active = CPL_Database::selection_exists( $session_key, $post_id, 'compare' );
			}
		}

		$this->add_render_attribute(
			'button',
			[
				'class'          => 'cpl-toggle cpl-toggle-compare' . ( $is_active ? ' is-active' : '' ),
				'type'           => 'button',
				'data-post-id'   => $post_id,
				'data-list-type' => 'compare',
				'aria-pressed'   => $is_active ? 'true' : 'false',
			]
		);

		?>
		<button <?php echo $this->get_render_attribute_string( 'button' ); ?>>
			<span class="cpl-compare__bullet" aria-hidden="true"></span>

			<?php if ( '' !== $button_text ) : ?>
				<span class="cpl-toggle__text"><?php echo esc_html( $button_text ); ?></span>
			<?php endif; ?>
		</button>
		<?php
	}
}
