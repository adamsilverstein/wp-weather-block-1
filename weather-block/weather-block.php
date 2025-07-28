<?php
/**
 * Plugin Name:       Weather Block
 * Description:       A WordPress block that displays current weather conditions for a specified location using OpenWeatherMap API.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       weather-block
 *
 * @package WeatherBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// For testing purposes, define the API key here.
// In a real plugin, this would be in wp-config.php or a settings page.
if ( ! defined( 'WEATHER_BLOCK_API_KEY' ) ) {
	define( 'WEATHER_BLOCK_API_KEY', 'your_provided_api_key_here' );
}

/**
 * Fetch weather data from OpenWeatherMap API with caching.
 *
 * @param string $location The city name to fetch weather for.
 * @param string $units    The unit system (metric or imperial).
 * @return array|WP_Error Weather data or error.
 */
function weather_block_get_weather_data( $location, $units = 'metric' ) {
	if ( empty( $location ) ) {
		return new WP_Error( 'invalid_location', __( 'Location is required.', 'weather-block' ) );
	}

	// Create cache key based on location and units.
	$cache_key = 'weather_block_' . md5( $location . '_' . $units );

	// Try to get cached data.
	$cached_data = get_transient( $cache_key );
	if ( false !== $cached_data ) {
		return $cached_data;
	}

	// Prepare API request.
	$api_key = WEATHER_BLOCK_API_KEY;
	if ( empty( $api_key ) || 'your_provided_api_key_here' === $api_key ) {
		return new WP_Error( 'missing_api_key', __( 'OpenWeatherMap API key is not configured.', 'weather-block' ) );
	}

	$api_url = add_query_arg(
		array(
			'q'     => sanitize_text_field( $location ),
			'appid' => $api_key,
			'units' => sanitize_text_field( $units ),
		),
		'https://api.openweathermap.org/data/2.5/weather'
	);

	// Make API request.
	$response = wp_remote_get(
		$api_url,
		array(
			'timeout' => 10,
			'headers' => array(
				'User-Agent' => 'WordPress Weather Block Plugin',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'Weather Block API Error: ' . $response->get_error_message() );
		return $response;
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		$error_message = sprintf(
			/* translators: %d: HTTP response code */
			__( 'API request failed with status code: %d', 'weather-block' ),
			$response_code
		);
		error_log( 'Weather Block API Error: ' . $error_message );
		return new WP_Error( 'api_error', $error_message );
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( ! $data || isset( $data['cod'] ) && '200' !== (string) $data['cod'] ) {
		$error_message = isset( $data['message'] ) ? $data['message'] : __( 'Invalid API response.', 'weather-block' );
		error_log( 'Weather Block API Error: ' . $error_message );
		return new WP_Error( 'api_error', $error_message );
	}

	// Process and sanitize the data.
	$weather_data = array(
		'city'        => sanitize_text_field( $data['name'] ),
		'country'     => sanitize_text_field( $data['sys']['country'] ),
		'temperature' => round( $data['main']['temp'] ),
		'description' => sanitize_text_field( $data['weather'][0]['description'] ),
		'icon'        => sanitize_text_field( $data['weather'][0]['icon'] ),
		'humidity'    => absint( $data['main']['humidity'] ),
		'units'       => $units,
	);

	// Cache for 15 minutes.
	set_transient( $cache_key, $weather_data, 15 * MINUTE_IN_SECONDS );

	return $weather_data;
}

/**
 * AJAX handler for fetching weather data.
 */
function weather_block_ajax_get_weather() {
	// Verify nonce for security.
	if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'weather_block_nonce' ) ) {
		wp_die( __( 'Security check failed.', 'weather-block' ), 403 );
	}

	$location = sanitize_text_field( $_POST['location'] ?? '' );
	$units    = sanitize_text_field( $_POST['units'] ?? 'metric' );

	$weather_data = weather_block_get_weather_data( $location, $units );

	if ( is_wp_error( $weather_data ) ) {
		wp_send_json_error( $weather_data->get_error_message() );
	} else {
		wp_send_json_success( $weather_data );
	}
}
add_action( 'wp_ajax_weather_block_get_weather', 'weather_block_ajax_get_weather' );
add_action( 'wp_ajax_nopriv_weather_block_get_weather', 'weather_block_ajax_get_weather' );

/**
 * Enqueue scripts and localize data for the block.
 */
function weather_block_enqueue_scripts() {
	wp_localize_script(
		'create-block-weather-block-editor-script',
		'weatherBlockData',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'weather_block_nonce' ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'weather_block_enqueue_scripts' );

/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function create_block_weather_block_block_init() {
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
				register_block_type(
			__DIR__ . "/build/{$block_type}",
			array(
				'render_callback' => 'weather_block_render_callback',
			)
		);
	}
}
add_action( 'init', 'create_block_weather_block_block_init' );

/**
 * Server-side render callback for the weather block.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML of the block.
 */
function weather_block_render_callback( $attributes ) {
	$location    = $attributes['location'] ?? '';
	$units       = $attributes['units'] ?? 'metric';
	$display_mode = $attributes['displayMode'] ?? 'auto';

	if ( empty( $location ) ) {
		return '<div class="wp-block-create-block-weather-block"><p>' . __( 'Please enter a location to display the weather.', 'weather-block' ) . '</p></div>';
	}

	$weather_data = weather_block_get_weather_data( $location, $units );

	if ( is_wp_error( $weather_data ) ) {
		return sprintf(
			'<div class="wp-block-create-block-weather-block"><p>%s: %s</p></div>',
			__( 'Error fetching weather data', 'weather-block' ),
			esc_html( $weather_data->get_error_message() )
		);
	}

	$icon_map = array(
		'01d' => '☀️',
		'01n' => '🌙',
		'02d' => '⛅',
		'02n' => '☁️',
		'03d' => '☁️',
		'03n' => '☁️',
		'04d' => '☁️',
		'04n' => '☁️',
		'09d' => '🌧️',
		'09n' => '🌧️',
		'10d' => '🌦️',
		'10n' => '🌧️',
		'11d' => '⛈️',
		'11n' => '⛈️',
		'13d' => '❄️',
		'13n' => '❄️',
		'50d' => '🌫️',
		'50n' => '🌫️',
	);
	$weather_icon = $icon_map[ $weather_data['icon'] ] ?? '🌤️';

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class'      => 'weather-block--' . esc_attr( $display_mode ),
			'data-display-mode' => esc_attr( $display_mode ),
		)
	);

	ob_start();
	?>
	<div <?php echo $wrapper_attributes; ?>>
		<div class="weather-block__content">
			<div class="weather-block__header">
				<div class="weather-block__icon"><?php echo esc_html( $weather_icon ); ?></div>
				<div class="weather-block__location">
					<h3><?php echo esc_html( $weather_data['city'] ); ?><?php if ( ! empty( $weather_data['country'] ) ) : ?>, <?php echo esc_html( $weather_data['country'] ); ?><?php endif; ?></h3>
				</div>
			</div>
			<div class="weather-block__temperature">
				<?php echo esc_html( $weather_data['temperature'] ); ?>°<?php echo 'imperial' === $units ? 'F' : 'C'; ?>
			</div>
			<div class="weather-block__description">
				<?php echo esc_html( ucfirst( $weather_data['description'] ) ); ?>
			</div>
			<div class="weather-block__humidity">
				<?php esc_html_e( 'Humidity:', 'weather-block' ); ?> <?php echo esc_html( $weather_data['humidity'] ); ?>%
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
