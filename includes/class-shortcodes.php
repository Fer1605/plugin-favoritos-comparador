<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPL_Shortcodes {

	private static $compare_price_field = [ 'label' => 'Precio', 'source' => 'meta', 'key' => 'precio', 'json_detail' => 'precio' ];

	private static $priority_compare_keys = [ 'clase_vehiculo', 'modelo', 'tipo', 'transmision', 'kilometraje' ];

	private static $compare_fields = [
		'clase_vehiculo'   => [ 'label' => 'CLASE DE VEH' . "\xC3\x8D" . 'CULO', 'source' => 'custom', 'key' => 'clase_vehiculo', 'json_detail' => 'claseVehiculo' ],
		'modelo'           => [ 'label' => 'MODELO', 'source' => 'meta', 'key' => 'modelo', 'json_detail' => 'modelo' ],
		'tipo'             => [ 'label' => 'Tipo', 'source' => 'taxonomy', 'key' => 'tipo-de-vehiculo', 'json_detail' => 'tipoVehiculoWeb' ],
		'transmision'      => [ 'label' => 'Transmisión', 'source' => 'taxonomy', 'key' => 'transmision', 'fallback_meta' => 'transmision', 'json_aliases' => [ 'transmision', 'transmisión' ] ],
		'kilometraje'      => [ 'label' => 'Kilometraje', 'source' => 'meta', 'key' => 'kilometraje', 'json_detail' => 'kilometraje' ],
		'cc'               => [ 'label' => 'C.C.', 'source' => 'meta', 'key' => 'cc', 'json_aliases' => [ 'c.c.', 'cc', 'cilindrada' ] ],
		'pasajeros'        => [ 'label' => 'Pasajeros', 'source' => 'meta', 'key' => 'pasajeros', 'json_aliases' => [ 'pasajeros' ] ],
		'cilindros'        => [ 'label' => 'Cilindros', 'source' => 'meta', 'key' => 'cilindros', 'json_aliases' => [ 'cilindros' ] ],
		'color'            => [ 'label' => 'Color', 'source' => 'taxonomy', 'key' => 'color', 'json_aliases' => [ 'color' ] ],
		'caballos_fuerza'  => [ 'label' => 'Caballos de fuerza', 'source' => 'taxonomy', 'key' => 'caballo-de-fuerza-filtro', 'fallback_meta' => 'caballos_de_fuerza', 'json_aliases' => [ 'caballos de fuerza', 'caballos fuerza', 'hp' ] ],
		'motor'            => [ 'label' => 'Motor', 'source' => 'meta', 'key' => 'motor', 'json_aliases' => [ 'tipo de motor', 'motor' ] ],
		'velocidades'      => [ 'label' => 'Velocidades', 'source' => 'meta', 'key' => 'caja_de_velocidades', 'json_aliases' => [ 'velocidades', 'caja de velocidades' ] ],
		'traccion'         => [ 'label' => 'Tracción', 'source' => 'taxonomy', 'key' => 'traccion', 'fallback_meta' => 'traccion', 'json_aliases' => [ 'traccion', 'tracción' ] ],
		'frenos'           => [ 'label' => 'Frenos', 'source' => 'taxonomy', 'key' => 'freno', 'fallback_meta' => 'frenos', 'json_aliases' => [ 'frenos', 'freno' ] ],
		'iluminacion'      => [ 'label' => 'Iluminación', 'source' => 'meta', 'key' => 'iluminacion', 'json_aliases' => [ 'iluminacion', 'iluminación' ] ],
		'ventanas'         => [ 'label' => 'Ventanas', 'source' => 'meta', 'key' => 'ventanas', 'json_aliases' => [ 'ventanas' ] ],
		'aros'             => [ 'label' => 'Aros', 'source' => 'meta', 'key' => 'aros', 'json_aliases' => [ 'aros' ] ],
		'rin'              => [ 'label' => 'Rin', 'source' => 'meta', 'key' => 'llantas', 'json_aliases' => [ 'rin', 'llantas' ] ],
		'bolsas_aire'      => [ 'label' => 'Bolsas de aire', 'source' => 'meta', 'key' => 'bolsas_de_aire', 'json_aliases' => [ 'bolsas de aire', 'airbags' ] ],
		'ac'               => [ 'label' => 'A/C', 'source' => 'meta', 'key' => 'climatizacion', 'json_aliases' => [ 'a/c', 'ac', 'climatizacion', 'climatización' ] ],
		'tapiceria'        => [ 'label' => 'Tapicería', 'source' => 'meta', 'key' => 'tapiceria', 'json_aliases' => [ 'tapiceria', 'tapicería' ] ],
	];

	private static $canonical_json_additional_fields = [
		'valvulas'                => [ 'group' => 'Motor y Dirección', 'label' => 'Válvulas', 'source' => 'meta', 'key' => 'valvulas', 'legacy_meta' => [ 'valvulas', 'válvulas' ], 'json_aliases' => [ 'valvulas', 'válvulas' ] ],
		'torque'                  => [ 'group' => 'Motor y Dirección', 'label' => 'Torque', 'source' => 'meta', 'key' => 'torque', 'legacy_meta' => [ 'torque' ], 'json_aliases' => [ 'torque' ] ],
		'combustible'             => [ 'group' => 'Motor y Dirección', 'label' => 'Combustible', 'source' => 'meta', 'key' => 'combustible', 'legacy_meta' => [], 'json_aliases' => [ 'combustible' ] ],
		'puertas'                 => [ 'group' => 'Motor y Dirección', 'label' => 'Puertas', 'source' => 'meta', 'key' => 'puertas', 'legacy_meta' => [ 'puertas' ], 'json_aliases' => [ 'puertas' ] ],
		'capacidad_combustible'   => [ 'group' => 'Dimensiones y Capacidades', 'label' => 'Capacidad de Combustible', 'source' => 'meta', 'key' => 'capacidad_combustible', 'legacy_meta' => [ 'capacidad_combustible', 'capacidadCombustible', 'capacidad_de_combustible' ], 'json_aliases' => [ 'capacidad de combustible', 'capacidadCombustible' ] ],
		'alto'                    => [ 'group' => 'Dimensiones y Capacidades', 'label' => 'Alto', 'source' => 'meta', 'key' => 'alto', 'legacy_meta' => [ 'alto' ], 'json_aliases' => [ 'alto' ] ],
		'ancho'                   => [ 'group' => 'Dimensiones y Capacidades', 'label' => 'Ancho', 'source' => 'meta', 'key' => 'ancho', 'legacy_meta' => [ 'ancho' ], 'json_aliases' => [ 'ancho' ] ],
		'largo'                   => [ 'group' => 'Dimensiones y Capacidades', 'label' => 'Largo', 'source' => 'meta', 'key' => 'largo', 'legacy_meta' => [ 'largo' ], 'json_aliases' => [ 'largo' ] ],
		'distancia_ejes'          => [ 'group' => 'Dimensiones y Capacidades', 'label' => 'Distancia entre Ejes', 'source' => 'meta', 'key' => 'distancia_ejes', 'legacy_meta' => [ 'distancia_ejes', 'distanciaEntreEjes', 'distancia_entre_ejes' ], 'json_aliases' => [ 'distancia entre ejes', 'distanciaEntreEjes' ] ],
		'peso'                    => [ 'group' => 'Dimensiones y Capacidades', 'label' => 'Peso', 'source' => 'meta', 'key' => 'peso', 'legacy_meta' => [ 'peso' ], 'json_aliases' => [ 'peso' ] ],
		'consumo_combustible'     => [ 'group' => 'Dimensiones y Capacidades', 'label' => 'Consumo de Combustible', 'source' => 'meta', 'key' => 'consumo_combustible', 'legacy_meta' => [ 'consumo_combustible', 'consumoCombustible', 'consumo_de_combustible' ], 'json_aliases' => [ 'consumo de combustible', 'consumoCombustible' ] ],
		'seguridad_adicional'     => [ 'group' => 'Seguridad', 'label' => 'Seguridad Adicional', 'source' => 'meta', 'key' => 'seguridad_adicional', 'legacy_meta' => [ 'seguridad_adicional', 'seguridadAdicional' ], 'json_aliases' => [ 'seguridad adicional', 'seguridadAdicional' ] ],
		'asistencia_manejo'       => [ 'group' => 'Seguridad', 'label' => 'Asistencia de Manejo', 'source' => 'meta', 'key' => 'asistencia_manejo', 'legacy_meta' => [ 'asistencia_manejo', 'asistenciaManejo', 'asistencia_de_manejo' ], 'json_aliases' => [ 'asistencia de manejo', 'asistenciaManejo' ] ],
		'audio_entretenimiento'   => [ 'group' => 'Interior', 'label' => 'Audio y Entretenimiento', 'source' => 'meta', 'key' => 'audio_entretenimiento', 'legacy_meta' => [ 'audio_entretenimiento', 'audioEntretenimiento', 'audio_y_entretenimiento' ], 'json_aliases' => [ 'audio y entretenimiento', 'audioEntretenimiento' ] ],
		'bocinas'                 => [ 'group' => 'Interior', 'label' => 'Bocinas', 'source' => 'meta', 'key' => 'bocinas', 'legacy_meta' => [ 'bocinas' ], 'json_aliases' => [ 'bocinas' ] ],
		'timon'                   => [ 'group' => 'Interior', 'label' => 'Timón', 'source' => 'meta', 'key' => 'timon', 'legacy_meta' => [ 'timon', 'timón' ], 'json_aliases' => [ 'timon', 'timón' ] ],
		'cerradura'               => [ 'group' => 'Interior', 'label' => 'Cerradura', 'source' => 'meta', 'key' => 'cerradura', 'legacy_meta' => [ 'cerradura' ], 'json_aliases' => [ 'cerradura' ] ],
		'asientos'                => [ 'group' => 'Interior', 'label' => 'Asientos', 'source' => 'meta', 'key' => 'asientos', 'legacy_meta' => [ 'asientos' ], 'json_aliases' => [ 'asientos' ] ],
		'retrovisores'            => [ 'group' => 'Exterior', 'label' => 'Retrovisores', 'source' => 'meta', 'key' => 'retrovisores', 'legacy_meta' => [ 'retrovisores' ], 'json_aliases' => [ 'retrovisores' ] ],
		'otros_exterior'          => [ 'group' => 'Exterior', 'label' => 'Otros Exterior', 'source' => 'meta', 'key' => 'otros_exterior', 'legacy_meta' => [ 'otros_exterior', 'otrosExterior' ], 'json_aliases' => [ 'otros exterior', 'otrosExterior' ] ],
		'garantia'                => [ 'group' => 'GARANTÍA Y EXTRAS', 'label' => 'GARANTÍA', 'source' => 'meta', 'key' => 'garantiaTexto', 'legacy_meta' => [ 'garantiaTexto', 'garantia_texto' ], 'json_aliases' => [ 'garantiaTexto', 'garantía texto', 'garantia texto' ] ],
		'precio_anterior'         => [ 'label' => 'Precio Anterior', 'source' => 'meta', 'key' => 'precio_anterior', 'legacy_meta' => [ 'precio_anterior', 'precioAnterior' ], 'json_aliases' => [ 'precio anterior', 'precioAnterior' ] ],
		'extras'           => [ 'group' => 'GARANTÍA Y EXTRAS', 'label' => 'EXTRAS', 'source' => 'meta', 'key' => 'informacionExtra', 'legacy_meta' => [ 'informacionExtra', 'informacion_extra', 'informacionextra' ], 'json_aliases' => [ 'informacionExtra', 'información extra', 'informacion extra', 'extras' ] ],
	];

	private static $legacy_fallback_additional_fields = [
		'valvulas'                => [ 'group' => 'MOTOR Y TRANSMISIÓN', 'label' => 'Válvulas', 'source' => 'meta', 'key' => 'valvulas', 'legacy_meta' => [ 'valvulas', 'válvulas' ] ],
		'torque'                  => [ 'group' => 'MOTOR Y TRANSMISIÓN', 'label' => 'Torque', 'source' => 'meta', 'key' => 'torque', 'legacy_meta' => [ 'torque' ] ],
		'puertas'                 => [ 'group' => 'EXTERIOR', 'label' => 'Puertas', 'source' => 'meta', 'key' => 'puertas', 'legacy_meta' => [ 'puertas' ] ],
		'otros_exterior'          => [ 'group' => 'EXTERIOR', 'label' => 'Otros Exterior', 'source' => 'meta', 'key' => 'otros_exterior', 'legacy_meta' => [ 'otros_exterior', 'otrosExterior' ] ],
		'suspension'              => [ 'group' => 'EXTERIOR', 'label' => 'Suspensión', 'source' => 'meta', 'key' => 'suspension', 'legacy_meta' => [ 'suspension', 'suspensión' ] ],
		'capacidad_combustible'   => [ 'group' => 'DIMENSIONES Y CAPACIDADES', 'label' => 'Capacidad de Combustible', 'source' => 'meta', 'key' => 'capacidad_combustible', 'legacy_meta' => [ 'capacidad_combustible', 'capacidadCombustible', 'capacidad_de_combustible' ] ],
		'alto'                    => [ 'group' => 'DIMENSIONES Y CAPACIDADES', 'label' => 'Alto', 'source' => 'meta', 'key' => 'alto', 'legacy_meta' => [ 'alto' ] ],
		'ancho'                   => [ 'group' => 'DIMENSIONES Y CAPACIDADES', 'label' => 'Ancho', 'source' => 'meta', 'key' => 'ancho', 'legacy_meta' => [ 'ancho' ] ],
		'largo'                   => [ 'group' => 'DIMENSIONES Y CAPACIDADES', 'label' => 'Largo', 'source' => 'meta', 'key' => 'largo', 'legacy_meta' => [ 'largo' ] ],
		'seguridad_adicional'     => [ 'group' => 'SEGURIDAD', 'label' => 'Seguridad Adicional', 'source' => 'meta', 'key' => 'seguridad_adicional', 'legacy_meta' => [ 'seguridad_adicional', 'seguridadAdicional' ] ],
		'sensores'                => [ 'group' => 'SEGURIDAD', 'label' => 'Sensores', 'source' => 'meta', 'key' => 'sensores', 'legacy_meta' => [ 'sensores' ] ],
		'audio_entretenimiento'   => [ 'group' => 'INTERIOR', 'label' => 'Audio y Entretenimiento', 'source' => 'meta', 'key' => 'audio_entretenimiento', 'legacy_meta' => [ 'audio_entretenimiento', 'audioEntretenimiento', 'audio_y_entretenimiento' ] ],
		'bocinas'                 => [ 'group' => 'INTERIOR', 'label' => 'Bocinas', 'source' => 'meta', 'key' => 'bocinas', 'legacy_meta' => [ 'bocinas' ] ],
		'timon'                   => [ 'group' => 'INTERIOR', 'label' => 'Timón', 'source' => 'meta', 'key' => 'timon', 'legacy_meta' => [ 'timon', 'timón' ] ],
		'cerradura'               => [ 'group' => 'INTERIOR', 'label' => 'Cerradura', 'source' => 'meta', 'key' => 'cerradura', 'legacy_meta' => [ 'cerradura' ] ],
		'asientos'                => [ 'group' => 'INTERIOR', 'label' => 'Asientos', 'source' => 'meta', 'key' => 'asientos', 'legacy_meta' => [ 'asientos' ] ],
		'retrovisores'            => [ 'group' => 'INTERIOR', 'label' => 'Retrovisores', 'source' => 'meta', 'key' => 'retrovisores', 'legacy_meta' => [ 'retrovisores' ] ],
		'ignicion'                => [ 'group' => 'INTERIOR', 'label' => 'Ignición', 'source' => 'meta', 'key' => 'ignicion', 'legacy_meta' => [ 'ignicion', 'ignición' ] ],
		'conexion_carga'          => [ 'group' => 'INTERIOR', 'label' => 'Conexión y Carga', 'source' => 'meta', 'key' => 'conexion_carga', 'legacy_meta' => [ 'conexion_carga', 'conexionCarga', 'conexion_y_carga', 'conexión y carga' ] ],
		'garantia'                => [ 'group' => 'GARANTÍA Y EXTRAS', 'label' => 'GARANTÍA', 'source' => 'meta', 'key' => 'garantiaTexto', 'legacy_meta' => [ 'garantiaTexto', 'garantia_texto' ] ],
		'extras'           => [ 'group' => 'GARANTÍA Y EXTRAS', 'label' => 'EXTRAS', 'source' => 'meta', 'key' => 'informacionExtra', 'legacy_meta' => [ 'informacionExtra', 'informacion_extra', 'informacionextra' ] ],
	];

	public static function init() {
		add_shortcode( CPL_Plugin::COMPARE_SHORTCODE, [ __CLASS__, 'render_compare_shortcode' ] );
		add_shortcode( 'cpl_favorites_count', [ __CLASS__, 'render_favorites_count_shortcode' ] );
		add_shortcode( 'cpl_compare_count', [ __CLASS__, 'render_compare_count_shortcode' ] );
	}

	public static function render_favorites_count_shortcode() {
		return self::render_selection_count_shortcode( 'favorite' );
	}

	public static function render_compare_count_shortcode() {
		return self::render_selection_count_shortcode( 'compare' );
	}

	public static function render_compare_shortcode() {
		$should_defer = self::should_defer_compare_shortcode_render();
		$markup       = $should_defer ? '' : self::render_compare_shortcode_markup();

		return sprintf(
			'<div class="cpl-compare-shortcode-host" data-cpl-compare-host>%s</div>',
			$markup
		);
	}

	public static function render_compare_shortcode_markup() {
		$posts = self::get_compare_posts();

		if ( empty( $posts ) ) {
			return '';
		}

		$compare_data = self::build_compare_data( $posts );
		$vehicles     = $compare_data['vehicles'];
		$primary_catalog    = $compare_data['primary_catalog'];
		$additional_catalog = $compare_data['additional_catalog'];
		$has_additional     = ! empty( $additional_catalog );
		$compare_colspan    = count( $vehicles ) + 1;
		$preview_catalog    = array_slice( $additional_catalog, 0, 2 );

		ob_start();
		?>
		<div class="cpl-compare-shortcode" data-cpl-compare data-total-columns="<?php echo esc_attr( count( $vehicles ) ); ?>" data-cpl-compare-expanded="0">
			<div class="cpl-compare-shortcode__toolbar">
				<div class="cpl-compare-shortcode__controls">
					<button class="cpl-compare-shortcode__nav cpl-compare-shortcode__nav--prev" type="button" aria-label="Ver vehículo anterior">
						<span class="cpl-compare-shortcode__nav-icon" aria-hidden="true">
							<svg aria-hidden="true" class="e-font-icon-svg e-eicon-chevron-right" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M696 533C708 521 713 504 713 487 713 471 708 454 696 446L400 146C388 133 375 125 354 125 338 125 325 129 313 142 300 154 292 171 292 187 292 204 296 221 308 233L563 492 304 771C292 783 288 800 288 817 288 833 296 850 308 863 321 871 338 875 354 875 371 875 388 867 400 854L696 533Z"></path></svg>
						</span>
					</button>
					<button class="cpl-compare-shortcode__nav cpl-compare-shortcode__nav--next" type="button" aria-label="Ver siguiente vehículo">
						<span class="cpl-compare-shortcode__nav-icon" aria-hidden="true">
							<svg aria-hidden="true" class="e-font-icon-svg e-eicon-chevron-right" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M696 533C708 521 713 504 713 487 713 471 708 454 696 446L400 146C388 133 375 125 354 125 338 125 325 129 313 142 300 154 292 171 292 187 292 204 296 221 308 233L563 492 304 771C292 783 288 800 288 817 288 833 296 850 308 863 321 871 338 875 354 875 371 875 388 867 400 854L696 533Z"></path></svg>
						</span>
					</button>
				</div>
			</div>

			<div class="cpl-compare-shortcode__desktop">
				<div class="cpl-compare-shortcode__desktop-shell">
					<div class="cpl-compare-shortcode__header-wrap">
						<div class="cpl-compare-shortcode__header">
							<div class="cpl-compare-shortcode__header-label">
								<div class="cpl-compare-shortcode__header-label-inner">
									<button class="cpl-compare-shortcode__clear" type="button">
										LIMPIAR COMPARADOR
									</button>
								</div>
							</div>
							<div class="cpl-compare-shortcode__header-viewport">
								<div class="cpl-compare-shortcode__header-track">
									<?php foreach ( $vehicles as $vehicle ) : ?>
										<div class="cpl-compare-shortcode__header-vehicle">
											<?php echo self::render_vehicle_header( $vehicle ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="cpl-compare-shortcode__header-placeholder" aria-hidden="true"></div>
					<div class="cpl-compare-shortcode__scroll-wrap">
						<div class="cpl-compare-shortcode__scroll" tabindex="0">
							<table class="cpl-compare-shortcode__table cpl-compare-shortcode__table--body">
								<tbody>
									<?php foreach ( $primary_catalog as $row ) : ?>
										<tr>
											<th scope="row" class="cpl-compare-shortcode__label">
												<?php echo esc_html( $row['label'] ); ?>
											</th>
											<?php foreach ( $vehicles as $vehicle ) : ?>
												<td class="cpl-compare-shortcode__value">
													<?php echo esc_html( isset( $vehicle['values'][ $row['key'] ] ) ? $vehicle['values'][ $row['key'] ] : '' ); ?>
												</td>
											<?php endforeach; ?>
										</tr>
									<?php endforeach; ?>
									<?php if ( $has_additional ) : ?>
										<tr class="cpl-compare-shortcode__expand-row" data-cpl-compare-expand-row>
											<td class="cpl-compare-shortcode__expand-cell" colspan="<?php echo esc_attr( $compare_colspan ); ?>">
												<div class="cpl-compare-shortcode__preview" aria-hidden="true">
													<table class="cpl-compare-shortcode__table cpl-compare-shortcode__preview-table">
														<tbody>
															<?php foreach ( $preview_catalog as $row ) : ?>
																<?php if ( ! empty( $row['type'] ) && 'group' === $row['type'] ) : ?>
																	<tr class="cpl-compare-shortcode__group-row">
																		<th class="cpl-compare-shortcode__group-label" scope="row">
																			<?php echo esc_html( $row['label'] ); ?>
																		</th>
																		<td class="cpl-compare-shortcode__group-fill" colspan="<?php echo esc_attr( count( $vehicles ) ); ?>"></td>
																	</tr>
																	<?php continue; ?>
																<?php endif; ?>
																<tr>
																	<th scope="row" class="cpl-compare-shortcode__label">
																		<?php echo esc_html( $row['label'] ); ?>
																	</th>
																	<?php foreach ( $vehicles as $vehicle ) : ?>
																		<td class="cpl-compare-shortcode__value">
																			<?php echo esc_html( isset( $vehicle['values'][ $row['key'] ] ) ? $vehicle['values'][ $row['key'] ] : '' ); ?>
																		</td>
																	<?php endforeach; ?>
																</tr>
															<?php endforeach; ?>
														</tbody>
													</table>
												</div>
												<div class="cpl-compare-shortcode__expand-fade" aria-hidden="true"></div>
											</td>
										</tr>
										<?php foreach ( $additional_catalog as $row ) : ?>
											<?php if ( ! empty( $row['type'] ) && 'group' === $row['type'] ) : ?>
												<tr class="cpl-compare-shortcode__group-row cpl-compare-shortcode__additional-row" data-cpl-compare-additional hidden>
													<th class="cpl-compare-shortcode__group-label" scope="row">
														<?php echo esc_html( $row['label'] ); ?>
													</th>
													<td class="cpl-compare-shortcode__group-fill" colspan="<?php echo esc_attr( count( $vehicles ) ); ?>"></td>
												</tr>
												<?php continue; ?>
											<?php endif; ?>
											<tr class="cpl-compare-shortcode__additional-row" data-cpl-compare-additional hidden>
												<th scope="row" class="cpl-compare-shortcode__label">
													<?php echo esc_html( $row['label'] ); ?>
												</th>
												<?php foreach ( $vehicles as $vehicle ) : ?>
													<td class="cpl-compare-shortcode__value">
														<?php echo esc_html( isset( $vehicle['values'][ $row['key'] ] ) ? $vehicle['values'][ $row['key'] ] : '' ); ?>
													</td>
												<?php endforeach; ?>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>
					<?php if ( $has_additional ) : ?>
						<div class="cpl-compare-shortcode__desktop-cta cpl-compare-shortcode__desktop-cta--expand" data-cpl-compare-expand-row>
							<button class="cpl-compare-shortcode__expand-toggle" type="button" data-cpl-compare-toggle aria-expanded="false">
								VER TODAS LAS CARACTERÍSTICAS
							</button>
						</div>
						<div class="cpl-compare-shortcode__desktop-cta cpl-compare-shortcode__desktop-cta--collapse" data-cpl-compare-collapse-row hidden>
							<button class="cpl-compare-shortcode__expand-toggle" type="button" data-cpl-compare-toggle aria-expanded="true">
								VER MENOS CARACTERÍSTICAS
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="cpl-compare-shortcode__mobile">
				<div class="cpl-compare-shortcode__mobile-wrap">
					<div class="cpl-compare-shortcode__mobile-actions">
						<button class="cpl-compare-shortcode__clear cpl-compare-shortcode__clear--mobile" type="button">
							LIMPIAR COMPARADOR
						</button>
					</div>
					<div class="cpl-compare-shortcode__mobile-scroll" tabindex="0">
						<div class="cpl-compare-shortcode__mobile-track">
							<?php foreach ( $vehicles as $vehicle ) : ?>
								<div class="cpl-compare-shortcode__mobile-slide">
									<div class="cpl-compare-shortcode__mobile-card">
										<?php echo self::render_vehicle_header( $vehicle ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										<div class="cpl-compare-shortcode__mobile-specs">
											<?php foreach ( $primary_catalog as $row ) : ?>
												<div class="cpl-compare-shortcode__mobile-spec">
													<div class="cpl-compare-shortcode__mobile-label"><?php echo esc_html( $row['label'] ); ?></div>
													<div class="cpl-compare-shortcode__mobile-value"><?php echo esc_html( isset( $vehicle['values'][ $row['key'] ] ) ? $vehicle['values'][ $row['key'] ] : '' ); ?></div>
												</div>
											<?php endforeach; ?>
											<?php if ( $has_additional ) : ?>
												<div class="cpl-compare-shortcode__mobile-expand" data-cpl-compare-expand-row>
													<div class="cpl-compare-shortcode__mobile-preview" aria-hidden="true">
														<?php foreach ( $preview_catalog as $row ) : ?>
															<?php if ( ! empty( $row['type'] ) && 'group' === $row['type'] ) : ?>
																<div class="cpl-compare-shortcode__mobile-group">
																	<?php echo esc_html( $row['label'] ); ?>
																</div>
																<?php continue; ?>
															<?php endif; ?>
															<div class="cpl-compare-shortcode__mobile-spec">
																<div class="cpl-compare-shortcode__mobile-label"><?php echo esc_html( $row['label'] ); ?></div>
																<div class="cpl-compare-shortcode__mobile-value"><?php echo esc_html( isset( $vehicle['values'][ $row['key'] ] ) ? $vehicle['values'][ $row['key'] ] : '' ); ?></div>
															</div>
														<?php endforeach; ?>
													</div>
													<div class="cpl-compare-shortcode__expand-fade" aria-hidden="true"></div>
													<button class="cpl-compare-shortcode__expand-toggle" type="button" data-cpl-compare-toggle aria-expanded="false">
														VER TODAS LAS CARACTERÍSTICAS
													</button>
												</div>
												<?php foreach ( $additional_catalog as $row ) : ?>
													<?php if ( ! empty( $row['type'] ) && 'group' === $row['type'] ) : ?>
														<div class="cpl-compare-shortcode__mobile-group cpl-compare-shortcode__additional-row" data-cpl-compare-additional hidden>
															<?php echo esc_html( $row['label'] ); ?>
														</div>
														<?php continue; ?>
													<?php endif; ?>
													<div class="cpl-compare-shortcode__mobile-spec cpl-compare-shortcode__additional-row" data-cpl-compare-additional hidden>
														<div class="cpl-compare-shortcode__mobile-label"><?php echo esc_html( $row['label'] ); ?></div>
														<div class="cpl-compare-shortcode__mobile-value"><?php echo esc_html( isset( $vehicle['values'][ $row['key'] ] ) ? $vehicle['values'][ $row['key'] ] : '' ); ?></div>
													</div>
												<?php endforeach; ?>
												<div class="cpl-compare-shortcode__mobile-collapse" data-cpl-compare-collapse-row hidden>
													<button class="cpl-compare-shortcode__expand-toggle" type="button" data-cpl-compare-toggle aria-expanded="true">
														VER MENOS CARACTERÍSTICAS
													</button>
												</div>
											<?php endif; ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	private static function build_compare_data( $posts ) {
		$has_json          = self::posts_have_json_vehicle_data( $posts );
		$canonical_catalog = $has_json ? self::build_canonical_json_compare_catalog( $posts ) : self::build_legacy_fallback_compare_catalog();
		$vehicles          = [];

		foreach ( $posts as $post ) {
			$vehicles[] = self::normalize_compare_vehicle( $post, $canonical_catalog );
		}

		$has_badges = false;

		foreach ( $vehicles as $vehicle ) {
			if ( ! empty( $vehicle['header']['badges'] ) ) {
				$has_badges = true;
				break;
			}
		}

		foreach ( $vehicles as $index => $vehicle ) {
			$vehicles[ $index ]['header']['reserve_badge_space'] = $has_badges;
		}

		return [
			'vehicles'            => $vehicles,
			'primary_catalog'     => self::build_primary_compare_catalog( $vehicles, $has_json ),
			'additional_catalog'  => $has_json ? self::build_additional_compare_catalog( $vehicles ) : self::build_legacy_fallback_additional_catalog( $vehicles ),
		];
	}

	private static function posts_have_json_vehicle_data( $posts ) {
		foreach ( $posts as $post ) {
			if ( ! empty( self::get_json_vehicle_data( $post->ID ) ) ) {
				return true;
			}
		}

		return false;
	}

	private static function normalize_compare_vehicle( $post, $canonical_catalog = [] ) {
		$post_id  = $post->ID;
		$json     = self::get_json_vehicle_data( $post_id );
		$use_json = ! empty( $json );
		$vehicle  = [
			'post_id'  => $post_id,
			'source'   => $use_json ? 'json' : 'legacy',
			'header'   => self::get_normalized_vehicle_header( $post, $json, $use_json ),
			'features' => [],
			'values'   => [],
		];

		$vehicle['features'] = $use_json
			? self::get_json_compare_features( $post_id, $json )
			: self::get_legacy_compare_features( $post_id, $canonical_catalog );
		$vehicle['features'] = self::apply_compare_vehicle_rules( $vehicle['features'] );
		$vehicle['values']   = self::get_compare_values_by_key( $vehicle['features'] );

		return $vehicle;
	}

	private static function get_normalized_vehicle_header( $post, $json, $use_json ) {
		$post_id = $post->ID;
		$detail  = $use_json && ! empty( $json['detalle'] ) && is_array( $json['detalle'] ) ? $json['detalle'] : [];
		$name    = $use_json ? self::get_array_value_by_key( $detail, 'nombreWeb' ) : '';
		$price   = $use_json ? self::get_array_value_by_key( $detail, 'precio' ) : '';
		$previous_price = $use_json ? self::get_array_value_by_key( $detail, 'precioAnterior' ) : '';
		$is_offer = $use_json ? self::get_json_boolean_value( self::get_raw_array_value_by_key( $detail, 'oferta' ) ) : self::get_legacy_vehicle_offer_state( $post_id );
		$currency = $use_json ? self::get_array_value_by_key( $detail, 'moneda' ) : '';
		$code    = $use_json ? self::get_array_value_by_key( $detail, 'codigoLocal' ) : '';
		$image   = $use_json ? self::get_json_vehicle_image( $json ) : '';

		if ( '' === $name ) {
			$name = self::get_vehicle_display_name( $post_id );
		}

		if ( '' === $price ) {
			$price = self::get_legacy_vehicle_field_value( $post_id, self::$compare_price_field );
		}

		if ( '' === $previous_price ) {
			$previous_price = self::get_legacy_previous_price( $post_id );
		}

		if ( '' === $code ) {
			$code = self::get_legacy_vehicle_code( $post_id );
		}

		if ( '' === $image ) {
			$image = get_the_post_thumbnail( $post_id, 'large', [ 'class' => 'cpl-compare-shortcode__image', 'loading' => 'lazy' ] );
		}

		return [
			'name'  => $name,
			'price' => self::format_compare_header_price( self::normalize_compare_value( $price ), $post_id, $currency ),
			'previous_price' => $is_offer ? self::format_compare_header_price( self::normalize_compare_value( $previous_price ), $post_id, $currency ) : '',
			'code'  => self::format_vehicle_code( $code ),
			'image' => $image ? $image : '<div class="cpl-compare-shortcode__image-placeholder">Sin imagen</div>',
			'badges' => $use_json ? self::get_json_vehicle_badges( $detail ) : self::get_legacy_vehicle_badges( $post_id ),
		];
	}

	private static function get_json_vehicle_badges( $detail ) {
		$badges = [];

		foreach ( self::get_compare_badge_definitions() as $badge ) {
			if ( self::get_json_boolean_value( self::get_raw_array_value_by_key( $detail, $badge['detail_key'] ) ) ) {
				$badges[] = $badge;
			}
		}

		return $badges;
	}

	private static function get_legacy_vehicle_badges( $post_id ) {
		$badges = [];

		foreach ( self::get_compare_badge_definitions() as $badge ) {
			if ( self::get_legacy_vehicle_badge_state( $post_id, $badge ) ) {
				$badges[] = $badge;
			}
		}

		return $badges;
	}

	private static function get_legacy_vehicle_offer_state( $post_id ) {
		$badge = self::get_compare_badge_definition_by_detail_key( 'oferta' );

		return ! empty( $badge ) ? self::get_legacy_vehicle_badge_state( $post_id, $badge ) : false;
	}

	private static function get_legacy_previous_price( $post_id ) {
		foreach ( [ 'precio_anterior', 'precioAnterior' ] as $meta_key ) {
			$value = self::get_meta_value( $post_id, $meta_key );

			if ( '' !== self::normalize_compare_value( $value ) ) {
				return $value;
			}
		}

		return '';
	}

	private static function get_legacy_vehicle_badge_state( $post_id, $badge ) {
		$meta_keys = ! empty( $badge['legacy_meta'] ) && is_array( $badge['legacy_meta'] ) ? $badge['legacy_meta'] : [];

		foreach ( $meta_keys as $meta_key ) {
			$value = self::get_meta_value( $post_id, $meta_key );

			if ( self::is_legacy_badge_truthy_value( $value, $badge ) ) {
				return true;
			}
		}

		foreach ( [ 'caracteristica', 'caracteristicas' ] as $taxonomy ) {
			foreach ( self::get_taxonomy_values( $post_id, $taxonomy ) as $value ) {
				if ( self::legacy_badge_value_matches_label( $value, $badge ) ) {
					return true;
				}
			}
		}

		foreach ( [ 'caracteristica', 'caracteristicas', 'Caracteristicas', 'Características' ] as $meta_key ) {
			foreach ( self::get_meta_values( $post_id, $meta_key ) as $value ) {
				if ( self::legacy_badge_value_matches_label( $value, $badge ) ) {
					return true;
				}
			}
		}

		return false;
	}

	private static function is_legacy_badge_truthy_value( $value, $badge ) {
		if ( self::get_json_boolean_value( $value ) ) {
			return true;
		}

		$normalized = self::normalize_json_compare_key( $value );

		if ( in_array( $normalized, [ '0', 'false', 'no', 'n/a', 'null', '<na>' ], true ) ) {
			return false;
		}

		if ( in_array( $normalized, [ 'yes', 'on', 'activo', 'activa' ], true ) ) {
			return true;
		}

		return self::legacy_badge_value_matches_label( $value, $badge );
	}

	private static function legacy_badge_value_matches_label( $value, $badge ) {
		$normalized = self::normalize_compare_identifier( $value );
		$aliases    = ! empty( $badge['legacy_aliases'] ) && is_array( $badge['legacy_aliases'] ) ? $badge['legacy_aliases'] : [];

		foreach ( $aliases as $alias ) {
			if ( $normalized === self::normalize_compare_identifier( $alias ) ) {
				return true;
			}
		}

		return false;
	}

	private static function get_compare_badge_definitions() {
		return [
			[
				'detail_key' => 'recienteingreso',
				'label'      => 'NUEVO INGRESO',
				'file'       => 'nuevo-ingreso.png',
				'legacy_meta' => [ 'recienteingreso', 'recienteIngreso', 'reciente_ingreso', 'nuevo_ingreso', 'nuevoIngreso' ],
				'legacy_aliases' => [ 'recienteingreso', 'reciente ingreso', 'nuevo ingreso' ],
			],
			[
				'detail_key' => 'oferta',
				'label'      => 'OFERTA',
				'file'       => 'oferta.png',
				'legacy_meta' => [ 'oferta' ],
				'legacy_aliases' => [ 'oferta' ],
			],
			[
				'detail_key' => 'hibrido',
				'label'      => 'HÍBRIDO',
				'file'       => 'hibrido.png',
				'legacy_meta' => [ 'hibrido', 'híbrido' ],
				'legacy_aliases' => [ 'hibrido', 'híbrido' ],
			],
			[
				'detail_key' => 'blindado',
				'label'      => 'BLINDADO',
				'file'       => 'blindado.png',
				'legacy_meta' => [ 'blindado' ],
				'legacy_aliases' => [ 'blindado' ],
			],
		];
	}

	private static function get_compare_badge_definition_by_detail_key( $detail_key ) {
		foreach ( self::get_compare_badge_definitions() as $badge ) {
			if ( $detail_key === $badge['detail_key'] ) {
				return $badge;
			}
		}

		return [];
	}

	private static function format_vehicle_code( $code ) {
		$code = self::normalize_compare_value( $code );

		if ( '' === $code ) {
			return '';
		}

		return '#' . ltrim( $code, "# \t\n\r\0\x0B" );
	}

	private static function get_json_vehicle_image( $json ) {
		if ( empty( $json['fotografias'] ) || ! is_array( $json['fotografias'] ) ) {
			return '';
		}

		$selected = null;

		foreach ( $json['fotografias'] as $photo ) {
			$url = is_array( $photo ) ? self::get_json_photo_url( $photo ) : self::normalize_json_value_part( $photo );

			if ( '' === $url ) {
				continue;
			}

			if ( null === $selected ) {
				$selected = $url;
			}

			if ( is_array( $photo ) && self::get_json_boolean_value( self::get_raw_array_value_by_key( $photo, 'principal' ) ) ) {
				$selected = $url;
				break;
			}
		}

		if ( null === $selected ) {
			return '';
		}

		return sprintf(
			'<img class="cpl-compare-shortcode__image" src="%s" alt="" loading="lazy">',
			esc_url( $selected )
		);
	}

	private static function get_json_photo_url( $photo ) {
		foreach ( [ 'url', 'URL', 'foto', 'fotografia', 'imagen', 'src' ] as $key ) {
			$url = self::get_array_value_by_key( $photo, $key );

			if ( '' !== $url ) {
				return $url;
			}
		}

		return '';
	}

	private static function get_legacy_vehicle_code( $post_id ) {
		foreach ( [ 'codigoLocal', 'codigo_local', 'codigo', 'codigo_vehiculo', 'cod_vehiculo' ] as $meta_key ) {
			$value = self::get_meta_value( $post_id, $meta_key );

			if ( '' !== self::normalize_compare_value( $value ) ) {
				return $value;
			}
		}

		return '';
	}

	private static function get_json_compare_features( $post_id, $json ) {
		$features = [];
		$detail   = ! empty( $json['detalle'] ) && is_array( $json['detalle'] ) ? $json['detalle'] : [];

		foreach ( self::$priority_compare_keys as $key ) {
			if ( 'transmision' === $key ) {
				continue;
			}

			$field = isset( self::$compare_fields[ $key ] ) ? self::$compare_fields[ $key ] : null;

			if ( empty( $field['json_detail'] ) ) {
				continue;
			}

			$value = self::get_array_value_by_key( $detail, $field['json_detail'] );

			$features[] = self::normalize_compare_feature(
				$key,
				$field['label'],
				$value,
				'Detalle',
				$field,
				$post_id
			);
		}

		foreach ( $detail as $detail_key => $detail_value ) {
			if ( self::is_json_detail_header_key( $detail_key ) ) {
				continue;
			}

			if ( ! self::is_allowed_json_detail_compare_key( $detail_key ) ) {
				continue;
			}

			$key = self::get_compare_feature_key( $detail_key );

			if ( '' === $key ) {
				continue;
			}

			$field      = isset( self::$compare_fields[ $key ] ) ? self::$compare_fields[ $key ] : [ 'key' => $key ];
			$features[] = self::normalize_compare_feature(
				$key,
				self::format_compare_label_from_detail_key( $detail_key ),
				$detail_value,
				self::get_special_compare_group_label(),
				$field,
				$post_id
			);
		}

		if ( ! empty( $json['caracteristicas'] ) && is_array( $json['caracteristicas'] ) ) {
			foreach ( $json['caracteristicas'] as $group ) {
				$group_name = is_array( $group ) ? self::get_array_value_by_key( $group, 'grupo' ) : '';
				$details    = is_array( $group ) && ! empty( $group['detalle'] ) && is_array( $group['detalle'] ) ? $group['detalle'] : [];

				foreach ( $details as $item ) {
					if ( ! is_array( $item ) ) {
						continue;
					}

					$label = self::get_array_value_by_key( $item, 'nombre' );

					if ( self::is_json_detail_header_key( $label ) ) {
						continue;
					}

					$key   = self::get_compare_feature_key( $label );

					if ( '' === $key ) {
						continue;
					}

					if ( in_array( $key, [ 'garantia', 'moneda' ], true ) || self::is_excluded_compare_feature_key( $key ) ) {
						continue;
					}

					$field    = isset( self::$compare_fields[ $key ] ) ? self::$compare_fields[ $key ] : [ 'key' => $key ];
					$features[] = self::normalize_compare_feature(
						$key,
						$label,
						self::build_json_characteristic_value( $item ),
						$group_name,
						$field,
						$post_id
					);
				}
			}
		}

		return self::dedupe_compare_features( $features );
	}

	private static function is_allowed_json_detail_compare_key( $key ) {
		$feature_key = self::get_compare_feature_key( $key );
		$normalized  = self::normalize_compare_identifier( $key );

		return in_array( $feature_key, [ 'garantia', 'extras' ], true )
			&& 'garantia' !== $normalized;
	}

	private static function is_json_detail_header_key( $key ) {
		$normalized = self::normalize_compare_identifier( $key );

		return in_array(
			$normalized,
			[
				'nombre web',
				'precio',
				'codigo local',
				'fotografias',
				'fotos',
				'imagen',
				'imagenes',
				'fecha creacion',
				'nombre vendedor',
				'placa',
				'tonelaje',
				'rodado',
				'enganche',
				'monto cuota',
				'fecha ingreso empresa',
				'observacion',
				'tipo recepcion',
				'tipo vehiculo',
				'uso vehiculo',
				'observacion interna',
				'codigo principal',
				'activo',
				'oferta',
				'hibrido',
				'blindado',
				'recienteingreso',
				'reciente ingreso',
				'nuevo ingreso',
			],
			true
		);
	}

	private static function build_canonical_json_compare_catalog( $posts ) {
		$catalog = [];

		foreach ( self::$compare_fields as $key => $field ) {
			$catalog[ $key ] = [
				'group' => '',
				'key'   => $key,
				'label' => $field['label'],
				'field' => $field,
			];
		}

		foreach ( self::$canonical_json_additional_fields as $key => $field ) {
			if ( self::is_excluded_compare_feature_key( $key ) ) {
				continue;
			}

			$catalog[ $key ] = [
				'group' => ! empty( $field['group'] ) ? $field['group'] : '',
				'key'   => $key,
				'label' => $field['label'],
				'field' => $field,
			];
		}

		foreach ( $posts as $post ) {
			$json = self::get_json_vehicle_data( $post->ID );

			if ( empty( $json ) ) {
				continue;
			}

			foreach ( self::get_json_compare_features( $post->ID, $json ) as $feature ) {
				if ( empty( $feature['key'] ) || self::is_excluded_compare_feature_key( $feature['key'] ) || isset( $catalog[ $feature['key'] ] ) ) {
					continue;
				}

				$catalog[ $feature['key'] ] = [
					'group' => $feature['group'],
					'key'   => $feature['key'],
					'label' => $feature['label'],
					'field' => [ 'key' => $feature['key'] ],
				];
			}
		}

		return array_values( $catalog );
	}

	private static function build_legacy_fallback_compare_catalog() {
		$catalog = [];

		foreach ( self::$compare_fields as $key => $field ) {
			$catalog[ $key ] = [
				'group' => '',
				'key'   => $key,
				'label' => $field['label'],
				'field' => $field,
			];
		}

		foreach ( self::$legacy_fallback_additional_fields as $key => $field ) {
			if ( self::is_excluded_compare_feature_key( $key ) ) {
				continue;
			}

			$catalog[ $key ] = [
				'group' => ! empty( $field['group'] ) ? $field['group'] : '',
				'key'   => $key,
				'label' => $field['label'],
				'field' => $field,
			];
		}

		return array_values( $catalog );
	}

	private static function get_legacy_compare_features( $post_id, $canonical_catalog = [] ) {
		$features = [];

		foreach ( self::$compare_fields as $key => $field ) {
			$features[] = self::normalize_compare_feature(
				$key,
				$field['label'],
				self::get_legacy_vehicle_field_value( $post_id, $field ),
				'',
				$field,
				$post_id
			);
		}

		$primary_keys = self::get_primary_compare_keys();

		foreach ( $canonical_catalog as $feature ) {
			if ( empty( $feature['key'] ) || self::is_excluded_compare_feature_key( $feature['key'] ) || in_array( $feature['key'], $primary_keys, true ) ) {
				continue;
			}

			$field      = ! empty( $feature['field'] ) && is_array( $feature['field'] ) ? $feature['field'] : [ 'key' => $feature['key'] ];
			$features[] = self::normalize_compare_feature(
				$feature['key'],
				$feature['label'],
				self::get_legacy_canonical_feature_value( $post_id, $feature, $field ),
				! empty( $feature['group'] ) ? $feature['group'] : '',
				$field,
				$post_id
			);
		}

		return self::dedupe_compare_features( $features );
	}

	private static function get_legacy_canonical_feature_value( $post_id, $feature, $field ) {
		if ( ! empty( $feature['key'] ) && in_array( $feature['key'], [ 'combustible', 'velocidades' ], true ) ) {
			return '';
		}

		if ( isset( self::$compare_fields[ $feature['key'] ] ) ) {
			return self::get_legacy_vehicle_field_value( $post_id, self::$compare_fields[ $feature['key'] ] );
		}

		$meta_keys = [];

		if ( ! empty( $field['legacy_meta'] ) && is_array( $field['legacy_meta'] ) ) {
			$meta_keys = array_merge( $meta_keys, $field['legacy_meta'] );
		}

		if ( ! empty( $field['key'] ) ) {
			$meta_keys[] = $field['key'];
		}

		if ( ! empty( $feature['key'] ) ) {
			$meta_keys[] = $feature['key'];
			$meta_keys[] = str_replace( '_', '-', $feature['key'] );
		}

		if ( ! empty( $feature['label'] ) ) {
			$meta_keys[] = $feature['label'];
			$meta_keys[] = strtolower( remove_accents( str_replace( ' ', '_', $feature['label'] ) ) );
		}

		foreach ( array_unique( array_filter( $meta_keys ) ) as $meta_key ) {
			$value = self::get_meta_value( $post_id, $meta_key );

			if ( '' !== self::normalize_compare_value( $value ) ) {
				return $value;
			}
		}

		return '';
	}

	private static function normalize_compare_feature( $key, $label, $value, $group, $field, $post_id ) {
		$field['key'] = $key;
		$value        = self::format_compare_field_value( $value, $field, $post_id );

		return [
			'group' => self::normalize_compare_value( $group ),
			'key'   => $key,
			'label' => self::get_compare_feature_label( $key, $label ),
			'value' => $value,
		];
	}

	private static function dedupe_compare_features( $features ) {
		$deduped = [];

		foreach ( $features as $feature ) {
			if ( empty( $feature['key'] ) || isset( $deduped[ $feature['key'] ] ) ) {
				continue;
			}

			$deduped[ $feature['key'] ] = $feature;
		}

		return array_values( $deduped );
	}

	private static function apply_compare_vehicle_rules( $features ) {
		$class_value = '';

		foreach ( $features as $feature ) {
			if ( 'clase_vehiculo' === $feature['key'] ) {
				$class_value = $feature['value'];
				break;
			}
		}

		if ( 'nuevo' !== self::normalize_json_compare_key( $class_value ) ) {
			return $features;
		}

		foreach ( $features as $index => $feature ) {
			if ( 'kilometraje' === $feature['key'] ) {
				$features[ $index ]['value'] = '';
			}
		}

		return $features;
	}

	private static function get_compare_values_by_key( $features ) {
		$values = [];

		foreach ( $features as $feature ) {
			$values[ $feature['key'] ] = $feature['value'];
		}

		return $values;
	}

	private static function build_primary_compare_catalog( $vehicles, $use_dynamic_primary_labels = true ) {
		$catalog = [];

		$primary_fields = $use_dynamic_primary_labels ? self::get_primary_compare_fields() : self::get_legacy_fallback_primary_fields();

		foreach ( $primary_fields as $key => $field ) {
			$feature = self::find_compare_feature( $vehicles, $key );

			if ( $feature && self::compare_feature_has_value( $vehicles, $key ) ) {
				if ( ! empty( $field['label'] ) ) {
					$feature['label'] = $field['label'];
				}

				$catalog[ $key ] = $feature;
			}
		}

		return array_values( $catalog );
	}

	private static function get_primary_compare_fields() {
		return self::$compare_fields;
	}

	private static function get_legacy_fallback_primary_fields() {
		$fields = self::$compare_fields;
		unset( $fields['velocidades'] );

		$labels = [
			'clase_vehiculo'  => 'CLASE DE VEH' . "\xC3\x8D" . 'CULO',
			'modelo'          => 'MODELO',
			'tipo'            => 'TIPO',
			'transmision'     => 'TRANSMISIÓN',
			'kilometraje'     => 'KILOMETRAJE',
			'cc'              => 'C.C.',
			'pasajeros'       => 'PASAJEROS',
			'cilindros'       => 'CILINDROS',
			'color'           => 'COLOR',
			'caballos_fuerza' => 'CABALLOS DE FUERZA',
			'motor'           => 'MOTOR',
			'traccion'        => 'TRACCIÓN',
			'frenos'          => 'FRENOS',
			'iluminacion'     => 'ILUMINACIÓN',
			'ventanas'        => 'VENTANAS',
			'aros'            => 'AROS',
			'rin'             => 'LLANTAS',
			'bolsas_aire'     => 'BOLSAS DE AIRE',
			'ac'              => 'CLIMATIZACIÓN',
			'tapiceria'       => 'TAPICERÍA',
		];

		foreach ( $labels as $key => $label ) {
			if ( isset( $fields[ $key ] ) ) {
				$fields[ $key ]['label'] = $label;
			}
		}

		return $fields;
	}

	private static function build_legacy_fallback_additional_catalog( $vehicles ) {
		$catalog      = [];
		$primary_keys = array_keys( self::get_legacy_fallback_primary_fields() );

		foreach ( self::$legacy_fallback_additional_fields as $key => $field ) {
			if ( in_array( $key, $primary_keys, true ) || self::is_excluded_compare_feature_key( $key ) || ! self::compare_feature_has_value( $vehicles, $key ) ) {
				continue;
			}

			$feature = self::find_compare_feature( $vehicles, $key );

			if ( ! $feature ) {
				continue;
			}

			$feature['type']  = 'feature';
			$feature['label'] = $field['label'];
			$feature['group'] = ! empty( $field['group'] ) ? $field['group'] : '';

			if ( '' !== $feature['group'] ) {
				$last = end( $catalog );

				if ( empty( $last ) || empty( $last['type'] ) || 'group' !== $last['type'] || $last['label'] !== $feature['group'] ) {
					$catalog[] = [
						'type'  => 'group',
						'key'   => 'group_' . self::normalize_compare_identifier( $feature['group'] ),
						'label' => $feature['group'],
					];
				}
			}

			$catalog[] = $feature;
		}

		return $catalog;
	}

	private static function build_additional_compare_catalog( $vehicles ) {
		$features     = [];
		$groups       = [];
		$primary_keys  = self::get_primary_compare_keys();
		$feature_order = 0;

		foreach ( $vehicles as $vehicle ) {
			$vehicle_groups = [];

			foreach ( $vehicle['features'] as $feature ) {
				$key = $feature['key'];

				if ( in_array( $key, $primary_keys, true ) || self::is_excluded_compare_feature_key( $key ) || ! self::compare_feature_has_value( $vehicles, $key ) ) {
					continue;
				}

				if ( ! isset( $features[ $key ] ) ) {
					$feature              = self::get_catalog_feature_with_json_label( $vehicles, $feature );
					$feature['type']      = 'feature';
					$feature['order']     = $feature_order++;
					$feature['group_key'] = self::normalize_compare_identifier( $feature['group'] );
					$features[ $key ]     = $feature;
				}

				$group_key = $features[ $key ]['group_key'];

				if ( '' !== $group_key ) {
					if ( ! isset( $groups[ $group_key ] ) ) {
						$groups[ $group_key ] = [
							'key'      => $group_key,
							'label'    => $features[ $key ]['group'],
							'order'    => count( $groups ),
							'presence' => 0,
							'special'  => self::normalize_compare_identifier( self::get_special_compare_group_label() ) === $group_key,
						];
					}

					$vehicle_groups[ $group_key ] = true;
				}
			}

			foreach ( array_keys( $vehicle_groups ) as $group_key ) {
				$groups[ $group_key ]['presence']++;
			}
		}

		$grouped = [];
		$final   = [];

		foreach ( $features as $feature ) {
			if ( '' === $feature['group_key'] ) {
				$final[] = $feature;
				continue;
			}

			$grouped[ $feature['group_key'] ][] = $feature;
		}

		uasort(
			$groups,
			static function ( $a, $b ) {
				$a_is_special = ! empty( $a['special'] );
				$b_is_special = ! empty( $b['special'] );

				if ( $a_is_special !== $b_is_special ) {
					return $a_is_special ? 1 : -1;
				}

				$a_is_exclusive = 1 >= $a['presence'];
				$b_is_exclusive = 1 >= $b['presence'];

				if ( $a_is_exclusive !== $b_is_exclusive ) {
					return $a_is_exclusive ? 1 : -1;
				}

				return $a['order'] <=> $b['order'];
			}
		);

		$catalog = [];

		$special_catalog = [];

		foreach ( $groups as $group_key => $group ) {
			if ( empty( $grouped[ $group_key ] ) ) {
				continue;
			}

			$group_catalog = [
				'type'  => 'group',
				'key'   => 'group_' . $group_key,
				'label' => $group['label'],
			];

			$group_rows = [ $group_catalog ];

			foreach ( $grouped[ $group_key ] as $feature ) {
				$group_rows[] = $feature;
			}

			if ( ! empty( $group['special'] ) ) {
				$special_catalog = array_merge( $special_catalog, $group_rows );
				continue;
			}

			$catalog = array_merge( $catalog, $group_rows );
		}

		return array_merge( $catalog, $final, $special_catalog );
	}

	private static function get_primary_compare_keys() {
		return array_keys( self::$compare_fields );
	}

	private static function find_compare_feature( $vehicles, $key ) {
		foreach ( $vehicles as $vehicle ) {
			foreach ( $vehicle['features'] as $feature ) {
				if ( $key === $feature['key'] && '' !== $feature['value'] ) {
					return self::get_catalog_feature_with_json_label( $vehicles, $feature );
				}
			}
		}

		return null;
	}

	private static function get_catalog_feature_with_json_label( $vehicles, $fallback_feature ) {
		foreach ( $vehicles as $vehicle ) {
			if ( 'json' !== $vehicle['source'] ) {
				continue;
			}

			foreach ( $vehicle['features'] as $feature ) {
				if ( $fallback_feature['key'] === $feature['key'] ) {
					if ( '' !== $feature['value'] && '' !== $feature['label'] ) {
						$fallback_feature['label'] = $feature['label'];
					}
					if ( '' !== $feature['group'] ) {
						$fallback_feature['group'] = $feature['group'];
					}

					if ( '' !== $feature['group'] || ( '' !== $feature['value'] && '' !== $feature['label'] ) ) {
						return $fallback_feature;
					}
				}
			}
		}

		return $fallback_feature;
	}

	private static function get_special_compare_group_label() {
		return 'GARANTÍA Y EXTRAS';
	}

	private static function is_excluded_compare_feature_key( $key ) {
		return in_array(
			self::get_compare_feature_key( $key ),
			[
				'otros',
				'consumo_combustible',
				'vidrios_electricos',
				'precio_anterior',
				'precio_oferta',
				'tipo_oferta',
			],
			true
		);
	}

	private static function compare_feature_has_value( $vehicles, $key ) {
		foreach ( $vehicles as $vehicle ) {
			if ( ! empty( $vehicle['values'][ $key ] ) ) {
				return true;
			}
		}

		return false;
	}

	private static function get_compare_feature_label( $key, $label ) {
		if ( 'garantia' === $key || in_array( self::normalize_json_compare_key( $label ), [ 'garantiatexto', 'garantia texto', 'garantía texto', 'garantia' ], true ) ) {
			return 'GARANTÍA';
		}

		if ( in_array( $key, [ 'informacionextra', 'extras' ], true ) || in_array( self::normalize_json_compare_key( $label ), [ 'informacionextra', 'informacion extra', 'extras' ], true ) ) {
			return 'EXTRAS';
		}

		$label = self::normalize_compare_value( $label );

		return '' !== $label ? $label : self::format_compare_label_from_key( $key );
	}

	private static function normalize_compare_identifier( $value ) {
		$value = preg_replace( '/([a-z])([A-Z])/', '$1 $2', (string) $value );
		$value = str_replace( [ '_', '-' ], ' ', $value );

		return self::normalize_json_compare_key( $value );
	}

	private static function get_compare_feature_key( $label ) {
		$normalized = self::normalize_compare_identifier( $label );

		if ( '' === $normalized ) {
			return '';
		}

		$aliases = [
			'clase vehiculo'             => 'clase_vehiculo',
			'clase de vehiculo'          => 'clase_vehiculo',
			'modelo'                     => 'modelo',
			'ano'                        => 'modelo',
			'año'                        => 'modelo',
			'tipo'                       => 'tipo',
			'tipo vehiculo web'          => 'tipo',
			'tipo de vehiculo'           => 'tipo',
			'transmision'                => 'transmision',
			'kilometraje'                => 'kilometraje',
			'pasajeros'                  => 'pasajeros',
			'cilindros'                  => 'cilindros',
			'color'                      => 'color',
			'c.c.'                       => 'cc',
			'c.c'                        => 'cc',
			'cc'                         => 'cc',
			'cilindrada'                 => 'cc',
			'valvulas'                   => 'valvulas',
			'precio anterior'            => 'precio_anterior',
			'precioanterior'             => 'precio_anterior',
			'garantia texto'             => 'garantia',
			'garantiatexto'              => 'garantia',
			'garantia'                   => 'garantia',
			'moneda'                     => 'moneda',
			'activo'                     => 'activo',
			'combustible'                => 'combustible',
			'capacidad de combustible'   => 'capacidad_combustible',
			'capacidad combustible'      => 'capacidad_combustible',
			'capacidadcombustible'       => 'capacidad_combustible',
			'alto'                       => 'alto',
			'ancho'                      => 'ancho',
			'largo'                      => 'largo',
			'distancia entre ejes'       => 'distancia_ejes',
			'distancia ejes'             => 'distancia_ejes',
			'distanciaentreejes'         => 'distancia_ejes',
			'peso'                       => 'peso',
			'consumo de combustible'     => 'consumo_combustible',
			'consumo combustible'        => 'consumo_combustible',
			'consumocombustible'         => 'consumo_combustible',
			'informacion extra'          => 'extras',
			'informacionextra'           => 'extras',
			'extras'           => 'extras',
			'caballos de fuerza'         => 'caballos_fuerza',
			'caballos fuerza'            => 'caballos_fuerza',
			'hp'                         => 'caballos_fuerza',
			'tipo de motor'              => 'motor',
			'motor'                      => 'motor',
			'caja de velocidades'        => 'velocidades',
			'velocidades'                => 'velocidades',
			'traccion'                   => 'traccion',
			'freno'                      => 'frenos',
			'frenos'                     => 'frenos',
			'seguridad adicional'        => 'seguridad_adicional',
			'seguridadadicional'         => 'seguridad_adicional',
			'asistencia de manejo'       => 'asistencia_manejo',
			'asistencia manejo'          => 'asistencia_manejo',
			'asistenciamanejo'           => 'asistencia_manejo',
			'audio y entretenimiento'    => 'audio_entretenimiento',
			'audio entretenimiento'      => 'audio_entretenimiento',
			'audioentretenimiento'       => 'audio_entretenimiento',
			'bocinas'                    => 'bocinas',
			'iluminacion'                => 'iluminacion',
			'ventanas'                   => 'ventanas',
			'timon'                      => 'timon',
			'cerradura'                  => 'cerradura',
			'asientos'                   => 'asientos',
			'retrovisores'               => 'retrovisores',
			'suspension'                 => 'suspension',
			'suspensión'                 => 'suspension',
			'sensores'                   => 'sensores',
			'ignicion'                   => 'ignicion',
			'ignición'                   => 'ignicion',
			'conexion y carga'           => 'conexion_carga',
			'conexión y carga'           => 'conexion_carga',
			'conexion carga'             => 'conexion_carga',
			'conexioncarga'              => 'conexion_carga',
			'vidrios electricos'         => 'vidrios_electricos',
			'vidrios eléctricos'         => 'vidrios_electricos',
			'vidrioselectricos'          => 'vidrios_electricos',
			'otros'                      => 'otros',
			'aros'                       => 'aros',
			'rin'                        => 'rin',
			'llantas'                    => 'rin',
			'otros exterior'             => 'otros_exterior',
			'otrosexterior'              => 'otros_exterior',
			'bolsas de aire'             => 'bolsas_aire',
			'airbags'                    => 'bolsas_aire',
			'a/c'                        => 'ac',
			'ac'                         => 'ac',
			'climatizacion'              => 'ac',
			'tapiceria'                  => 'tapiceria',
		];

		if ( isset( $aliases[ $normalized ] ) ) {
			return $aliases[ $normalized ];
		}

		return str_replace( ' ', '_', preg_replace( '/[^a-z0-9 ]/', '', $normalized ) );
	}

	private static function format_compare_label_from_key( $key ) {
		return ucwords( str_replace( '_', ' ', (string) $key ) );
	}

	private static function format_compare_label_from_detail_key( $key ) {
		$key = preg_replace( '/([a-z])([A-Z])/', '$1 $2', (string) $key );
		$key = str_replace( [ '_', '-' ], ' ', $key );
		$key = preg_replace( '/\s+/', ' ', trim( $key ) );

		return ucwords( $key );
	}

	private static function should_defer_compare_shortcode_render() {
		return ! is_user_logged_in() && is_page( 'comparar' );
	}

	private static function get_compare_posts() {
		$session_key = CPL_Session::get_session_key();

		if ( empty( $session_key ) ) {
			return [];
		}

		$post_ids = CPL_Database::get_valid_post_ids_by_type( $session_key, 'compare', CPL_Plugin::MAX_COMPARE );

		if ( empty( $post_ids ) ) {
			return [];
		}

		$query = new WP_Query(
			[
				'post_type'           => 'vehiculo',
				'post__in'            => $post_ids,
				'orderby'             => 'post__in',
				'posts_per_page'      => count( $post_ids ),
				'post_status'         => 'publish',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			]
		);

		return $query->posts;
	}

	private static function render_selection_count_shortcode( $list_type ) {
		$session_key = CPL_Session::get_session_key();
		$count       = 0;
		$is_guest_pending = ! is_user_logged_in() && in_array( $list_type, [ 'compare', 'favorite' ], true );

		if ( ! empty( $session_key ) ) {
			$count = CPL_Database::get_valid_selection_count( $session_key, $list_type );
		}

		return sprintf(
			'<span class="cpl-selection-count" data-cpl-selection-count="%1$s"%3$s%4$s%5$s>%2$d</span>',
			esc_attr( $list_type ),
			absint( $count ),
			$count > 0 && ! $is_guest_pending ? '' : ' hidden',
			$count > 0 && ! $is_guest_pending ? '' : ' style="display:none;" aria-hidden="true"',
			$is_guest_pending ? ' data-cpl-authoritative-pending="1"' : ''
		);
	}

	private static function render_vehicle_header( $vehicle ) {
		$post_id = $vehicle['post_id'];
		$header  = $vehicle['header'];
		$image   = $header['image'];
		$title   = $header['name'];
		$price   = $header['price'];
		$previous_price = ! empty( $header['previous_price'] ) ? $header['previous_price'] : '';
		$code    = $header['code'];
		$badges  = ! empty( $header['badges'] ) && is_array( $header['badges'] ) ? $header['badges'] : [];
		$reserve_badge_space = ! empty( $header['reserve_badge_space'] );
		$link    = get_permalink( $post_id );

		ob_start();
		?>
		<div class="cpl-compare-shortcode__vehicle-card">
			<button
				class="cpl-toggle cpl-toggle-compare cpl-compare-shortcode__remove"
				type="button"
				data-post-id="<?php echo esc_attr( $post_id ); ?>"
				data-list-type="compare"
				aria-pressed="false"
				aria-checked="false"
			>
				<span class="cpl-compare__bullet" aria-hidden="true"></span>
				<span class="cpl-toggle__text">Eliminar de comparador</span>
			</button>
			<a class="cpl-compare-shortcode__vehicle-link" href="<?php echo esc_url( $link ); ?>">
				<div class="cpl-compare-shortcode__vehicle-name"><?php echo esc_html( $title ); ?></div>
				<div class="cpl-compare-shortcode__vehicle-price<?php echo '' !== $previous_price ? ' cpl-compare-shortcode__vehicle-price--offer' : ''; ?>">
					<?php if ( '' !== $previous_price ) : ?>
						<span class="cpl-compare-shortcode__vehicle-price-previous"><?php echo esc_html( $previous_price ); ?></span>
						<span class="cpl-compare-shortcode__vehicle-price-current"><?php echo esc_html( $price ); ?></span>
					<?php else : ?>
						<?php echo esc_html( $price ); ?>
					<?php endif; ?>
				</div>
				<?php if ( $reserve_badge_space ) : ?>
					<div class="cpl-compare-shortcode__vehicle-badges<?php echo empty( $badges ) ? ' cpl-compare-shortcode__vehicle-badges--empty' : ''; ?>"<?php echo empty( $badges ) ? ' aria-hidden="true"' : ' aria-label="Sellos del vehículo"'; ?>>
						<?php foreach ( $badges as $badge ) : ?>
							<img
								class="cpl-compare-shortcode__vehicle-badge"
								src="<?php echo esc_url( CPL_URL . 'assets/images/compare-badges/' . $badge['file'] ); ?>"
								alt="<?php echo esc_attr( $badge['label'] ); ?>"
								loading="lazy"
							>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<div class="cpl-compare-shortcode__image-wrap">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="cpl-compare-shortcode__vehicle-code"><?php echo esc_html( $code ); ?></div>
			</a>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	private static function get_vehicle_display_name( $post_id ) {
		$parts = [
			self::get_taxonomy_value( $post_id, 'marca' ),
			self::get_taxonomy_value( $post_id, 'linea' ),
			self::get_meta_value( $post_id, 'modelo' ),
		];

		$parts = array_filter(
			array_map( 'trim', $parts ),
			static function ( $value ) {
				return '' !== $value;
			}
		);

		return ! empty( $parts ) ? implode( ' ', $parts ) : '';
	}

	private static function get_legacy_vehicle_field_value( $post_id, $field ) {
		if ( ! empty( $field['key'] ) && 'caja_de_velocidades' === $field['key'] ) {
			return '';
		}

		$value = '';

		if ( 'taxonomy' === $field['source'] ) {
			$value = self::get_taxonomy_value( $post_id, $field['key'] );
		} elseif ( 'meta' === $field['source'] ) {
			$value = self::get_meta_value( $post_id, $field['key'] );
		} elseif ( 'custom' === $field['source'] && 'clase_vehiculo' === $field['key'] ) {
			$value = self::get_vehicle_class_value( $post_id );
		}

		if ( '' === $value && ! empty( $field['fallback_meta'] ) ) {
			$value = self::get_meta_value( $post_id, $field['fallback_meta'] );
		}

		if ( ! empty( $field['key'] ) && 'transmision' === $field['key'] ) {
			$value = self::normalize_legacy_transmission_value( $value );
		}

		if ( ! empty( $field['key'] ) && 'caja_de_velocidades' === $field['key'] && self::is_legacy_placeholder_value( $value ) ) {
			$value = '';
		}

		return $value;
	}

	private static function get_json_vehicle_data( $post_id ) {
		$raw = get_post_meta( $post_id, 'json_vehiculo', true );

		if ( empty( $raw ) ) {
			return [];
		}

		if ( is_array( $raw ) ) {
			return $raw;
		}

		if ( ! is_string( $raw ) ) {
			return [];
		}

		$decoded = json_decode( trim( $raw ), true );

		return is_array( $decoded ) ? $decoded : [];
	}

	private static function get_array_value_by_key( $data, $key ) {
		return self::normalize_json_value_part( self::get_raw_array_value_by_key( $data, $key ) );
	}

	private static function get_raw_array_value_by_key( $data, $key ) {
		$normalized_key = self::normalize_json_compare_key( $key );

		foreach ( $data as $item_key => $value ) {
			if ( self::normalize_json_compare_key( $item_key ) === $normalized_key ) {
				return $value;
			}
		}

		return '';
	}

	private static function build_json_characteristic_value( $detail ) {
		$parts = [];

		foreach ( [ 'cantidad', 'informacionAdicional', 'opciones' ] as $key ) {
			$value = self::get_array_value_by_key( $detail, $key );

			if ( '' !== $value ) {
				$parts[] = $value;
			}
		}

		return implode( ' ', array_unique( $parts ) );
	}

	private static function normalize_json_value_part( $value ) {
		if ( true === $value ) {
			return 'Sí';
		}

		if ( false === $value ) {
			return '';
		}

		if ( is_array( $value ) ) {
			$value = implode(
				', ',
				array_filter(
					array_map( [ __CLASS__, 'normalize_json_value_part' ], $value ),
					static function ( $item ) {
						return '' !== $item;
					}
				)
			);
		}

		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$value = trim( preg_replace( '/\s+/', ' ', (string) $value ) );

		if ( self::is_empty_compare_value( $value ) ) {
			return '';
		}

		return preg_replace( '/\s*,\s*/', ', ', $value );
	}

	private static function get_json_boolean_value( $value ) {
		if ( true === $value || 1 === $value || '1' === $value ) {
			return true;
		}

		if ( is_string( $value ) ) {
			$value = self::normalize_json_compare_key( $value );

			return in_array( $value, [ 'true', 'si', 'sí' ], true );
		}

		return false;
	}

	private static function normalize_json_compare_key( $value ) {
		$value = strtolower( remove_accents( trim( (string) $value ) ) );
		$value = preg_replace( '/\s+/', ' ', $value );

		return $value;
	}

	private static function normalize_legacy_transmission_value( $value ) {
		$normalized = self::normalize_json_compare_key( $value );

		if ( false !== strpos( $normalized, 'automatica' ) ) {
			return 'Automática';
		}

		if ( false !== strpos( $normalized, 'mecanica' ) ) {
			return 'Mecánica';
		}

		return $value;
	}

	private static function is_legacy_placeholder_value( $value ) {
		return 'seleccione una opcion' === self::normalize_json_compare_key( $value );
	}

	private static function is_empty_compare_value( $value ) {
		if ( null === $value || false === $value ) {
			return true;
		}

		if ( true === $value ) {
			return false;
		}

		if ( ! is_scalar( $value ) ) {
			return true;
		}

		$value      = trim( (string) $value );
		$normalized = self::normalize_json_compare_key( $value );

		return '' === $value
			|| ( is_numeric( $value ) && (float) $value <= 0 )
			|| '<na>' === $normalized
			|| 'n/a' === $normalized
			|| 'null' === $normalized
			|| self::is_legacy_placeholder_value( $value );
	}

	private static function get_taxonomy_value( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}

		$names = wp_list_pluck( $terms, 'name' );
		$names = array_filter(
			array_map( 'trim', $names ),
			static function ( $value ) {
				return '' !== $value;
			}
		);

		return ! empty( $names ) ? implode( ', ', $names ) : '';
	}

	private static function get_meta_value( $post_id, $meta_key ) {
		$value = get_post_meta( $post_id, $meta_key, true );

		if ( is_array( $value ) ) {
			$value = implode(
				', ',
				array_filter(
					array_map(
						static function ( $item ) {
							return is_scalar( $item ) ? trim( (string) $item ) : '';
						},
						$value
					),
					static function ( $item ) {
						return '' !== $item;
					}
				)
			);
		}

		return is_scalar( $value ) ? trim( (string) $value ) : '';
	}

	private static function get_vehicle_class_value( $post_id ) {
		$taxonomy_candidates = [ 'caracteristica', 'caracteristicas' ];
		$meta_candidates     = [ 'caracteristica', 'caracteristicas', 'Caracteristicas', 'Características' ];

		foreach ( $taxonomy_candidates as $taxonomy ) {
			foreach ( self::get_taxonomy_values( $post_id, $taxonomy ) as $value ) {
				$matched_value = self::match_vehicle_class_value( $value );

				if ( '' !== $matched_value ) {
					return $matched_value;
				}
			}
		}

		foreach ( $meta_candidates as $meta_key ) {
			foreach ( self::get_meta_values( $post_id, $meta_key ) as $value ) {
				$matched_value = self::match_vehicle_class_value( $value );

				if ( '' !== $matched_value ) {
					return $matched_value;
				}
			}
		}

		return '';
	}

	private static function get_taxonomy_values( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return [];
		}

		$names = wp_list_pluck( $terms, 'name' );

		return array_values(
			array_filter(
				array_map( 'trim', $names ),
				static function ( $value ) {
					return '' !== $value;
				}
			)
		);
	}

	private static function get_meta_values( $post_id, $meta_key ) {
		$raw_value = get_post_meta( $post_id, $meta_key, true );
		$values    = is_array( $raw_value ) ? $raw_value : [ $raw_value ];
		$results   = [];

		foreach ( $values as $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$parts = preg_split( '/\s*,\s*/', trim( (string) $value ) );

			foreach ( $parts as $part ) {
				$part = trim( (string) $part );

				if ( '' !== $part ) {
					$results[] = $part;
				}
			}
		}

		return array_values( array_unique( $results ) );
	}

	private static function match_vehicle_class_value( $value ) {
		$normalized = strtolower( remove_accents( trim( (string) $value ) ) );
		$normalized = preg_replace( '/\s+/', ' ', $normalized );

		if ( 'nuevo' === $normalized ) {
			return 'Nuevo';
		}

		if ( 'seminuevo' === $normalized || 'semi nuevo' === $normalized ) {
			return 'Seminuevo';
		}

		return '';
	}

	private static function normalize_compare_value( $value ) {
		if ( true === $value ) {
			return 'Sí';
		}

		if ( self::is_empty_compare_value( $value ) ) {
			return '';
		}

		$value = trim( (string) $value );

		return $value;
	}

	private static function format_compare_field_value( $value, $field, $post_id = 0 ) {
		$normalized = self::normalize_compare_value( $value );

		if ( ! empty( $field['key'] ) && 'precio' === $field['key'] ) {
			return self::format_compare_price( $normalized, $post_id );
		}

		if ( ! empty( $field['key'] ) && 'precio_anterior' === $field['key'] ) {
			return self::format_compare_price( $normalized, $post_id );
		}

		if ( ! empty( $field['key'] ) && 'moneda' === $field['key'] ) {
			return self::format_compare_currency_value( $normalized );
		}

		if ( ! empty( $field['key'] ) && 'activo' === $field['key'] ) {
			return self::format_compare_active_value( $value );
		}

		if ( ! empty( $field['key'] ) && 'cc' === $field['key'] ) {
			return self::format_compare_integer_value( $normalized );
		}

		if ( ! empty( $field['key'] ) && 'kilometraje' === $field['key'] ) {
			return self::format_compare_mileage( $normalized );
		}

		return $normalized;
	}

	private static function format_compare_currency_value( $value ) {
		$value = self::normalize_json_compare_key( $value );

		if ( '1' === $value || 'usd' === $value || 'dolar' === $value || 'dolares' === $value ) {
			return '$';
		}

		if ( '2' === $value || 'gtq' === $value || 'quetzal' === $value || 'quetzales' === $value ) {
			return 'Q';
		}

		return '';
	}

	private static function format_compare_active_value( $value ) {
		if ( true === $value || 1 === $value ) {
			return 'Sí';
		}

		$normalized = self::normalize_json_compare_key( $value );

		return in_array( $normalized, [ '1', 'true', 'si', 'sí', 'activo' ], true ) ? 'Sí' : '';
	}

	private static function format_compare_mileage( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value || ! is_numeric( str_replace( ',', '', $value ) ) ) {
			return $value;
		}

		$number = (float) str_replace( ',', '', $value );

		if ( $number <= 0 ) {
			return '';
		}

		return number_format( (int) $number, 0, '.', ',' );
	}

	private static function format_compare_integer_value( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value || ! is_numeric( str_replace( ',', '', $value ) ) ) {
			return $value;
		}

		$number = (float) str_replace( ',', '', $value );

		if ( $number <= 0 || floor( $number ) !== $number ) {
			return $value;
		}

		return number_format( (int) $number, 0, '.', ',' );
	}

	private static function format_compare_price( $value, $post_id = 0 ) {
		$digits = preg_replace( '/[^\d]/', '', (string) $value );

		if ( '' === $digits ) {
			return $value;
		}

		return self::get_compare_currency_symbol( $post_id ) . number_format( (int) $digits, 0, '.', ',' );
	}

	private static function format_compare_header_price( $value, $post_id = 0, $currency = '' ) {
		$digits = preg_replace( '/[^\d]/', '', (string) $value );

		if ( '' === $digits ) {
			return $value;
		}

		$symbol = self::format_compare_currency_value( $currency );

		if ( '' === $symbol ) {
			$symbol = self::get_compare_currency_symbol( $post_id );
		}

		return $symbol . number_format( (int) $digits, 0, '.', ',' );
	}

	private static function get_compare_currency_symbol( $post_id ) {
		$currency = self::get_meta_value( $post_id, 'moneda' );

		if ( '' === $currency ) {
			$currency = self::get_meta_value( $post_id, 'Moneda' );
		}

		if ( '1' === $currency ) {
			return '$';
		}

		if ( '2' === $currency ) {
			return 'Q.';
		}

		return 'Q.';
	}
}
