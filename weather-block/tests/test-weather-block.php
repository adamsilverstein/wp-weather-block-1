<?php
/**
 * Class WeatherBlockTest
 *
 * @package Weather_Block
 */

/**
 * Sample test case.
 */
class WeatherBlockTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

	/**
	 * Test that the weather block is registered.
	 */
	function test_block_is_registered() {
		$this->assertTrue( block_type_exists( 'create-block/weather-block' ) );
	}

	/**
	 * Test that the weather data function returns a WP_Error for an empty location.
	 */
	function test_get_weather_data_empty_location() {
		$result = weather_block_get_weather_data( '' );
		$this->assertWPError( $result );
		$this->assertEquals( 'invalid_location', $result->get_error_code() );
	}

	/**
	 * Test that the weather data function returns a WP_Error for a missing API key.
	 */
	function test_get_weather_data_missing_api_key() {
		// Temporarily set the API key to an invalid value.
		define( 'WEATHER_BLOCK_API_KEY_TEMP', WEATHER_BLOCK_API_KEY );
		define( 'WEATHER_BLOCK_API_KEY', 'your_provided_api_key_here' );

		$result = weather_block_get_weather_data( 'London' );
		$this->assertWPError( $result );
		$this->assertEquals( 'missing_api_key', $result->get_error_code() );

		// Restore the original API key.
		define( 'WEATHER_BLOCK_API_KEY', WEATHER_BLOCK_API_KEY_TEMP );
	}
}
