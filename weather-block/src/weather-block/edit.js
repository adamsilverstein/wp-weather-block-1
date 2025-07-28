/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

/**
 * WordPress components for the editor controls.
 */
import {
	PanelBody,
	TextControl,
	ToggleControl,
	RadioControl,
	Button,
	Spinner,
	Notice,
} from '@wordpress/components';

/**
 * React hooks.
 */
import { useState, useEffect } from '@wordpress/element';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * Weather icon mapping.
 */
const getWeatherIcon = (iconCode) => {
	const iconMap = {
		'01d': '☀️', // clear sky day
		'01n': '🌙', // clear sky night
		'02d': '⛅', // few clouds day
		'02n': '☁️', // few clouds night
		'03d': '☁️', // scattered clouds
		'03n': '☁️',
		'04d': '☁️', // broken clouds
		'04n': '☁️',
		'09d': '🌧️', // shower rain
		'09n': '🌧️',
		'10d': '🌦️', // rain day
		'10n': '🌧️', // rain night
		'11d': '⛈️', // thunderstorm
		'11n': '⛈️',
		'13d': '❄️', // snow
		'13n': '❄️',
		'50d': '🌫️', // mist
		'50n': '🌫️',
	};
	return iconMap[iconCode] || '🌤️';
};

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object} props Block props.
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { location, units, displayMode, weatherData } = attributes;
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);

	/**
	 * Fetch weather data from the server.
	 */
	const fetchWeatherData = async () => {
		if (!location.trim()) {
			setError(__('Please enter a location.', 'weather-block'));
			return;
		}

		setIsLoading(true);
		setError(null);

		try {
			const formData = new FormData();
			formData.append('action', 'weather_block_get_weather');
			formData.append('location', location);
			formData.append('units', units);
			formData.append('nonce', weatherBlockData.nonce);

			const response = await fetch(weatherBlockData.ajaxUrl, {
				method: 'POST',
				body: formData,
			});

			const result = await response.json();

			if (result.success) {
				setAttributes({ weatherData: result.data });
				setError(null);
			} else {
				setError(result.data || __('Failed to fetch weather data.', 'weather-block'));
				setAttributes({ weatherData: null });
			}
		} catch (err) {
			setError(__('Network error. Please try again.', 'weather-block'));
			setAttributes({ weatherData: null });
		} finally {
			setIsLoading(false);
		}
	};

	/**
	 * Auto-fetch weather data when location or units change.
	 */
	useEffect(() => {
		if (location.trim() && !isLoading) {
			const timeoutId = setTimeout(() => {
				fetchWeatherData();
			}, 500); // Debounce API calls

			return () => clearTimeout(timeoutId);
		}
	}, [location, units]);

	const blockProps = useBlockProps({
		className: `weather-block weather-block--${displayMode}`,
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Weather Settings', 'weather-block')} initialOpen={true}>
					<TextControl
						label={__('Location', 'weather-block')}
						value={location}
						onChange={(value) => setAttributes({ location: value })}
						placeholder={__('Enter city name (e.g., New York)', 'weather-block')}
						help={__('Enter the name of the city for which you want to display weather information.', 'weather-block')}
					/>

					<ToggleControl
						label={__('Temperature Units', 'weather-block')}
						checked={units === 'imperial'}
						onChange={(checked) => setAttributes({ units: checked ? 'imperial' : 'metric' })}
						help={units === 'imperial' ? __('Fahrenheit (°F)', 'weather-block') : __('Celsius (°C)', 'weather-block')}
					/>

					<RadioControl
						label={__('Display Mode', 'weather-block')}
						selected={displayMode}
						options={[
							{ label: __('Light', 'weather-block'), value: 'light' },
							{ label: __('Dark', 'weather-block'), value: 'dark' },
							{ label: __('Auto (System Preference)', 'weather-block'), value: 'auto' },
						]}
						onChange={(value) => setAttributes({ displayMode: value })}
						help={__('Choose the color scheme for the weather block.', 'weather-block')}
					/>

					{location.trim() && (
						<Button
							isPrimary
							onClick={fetchWeatherData}
							isBusy={isLoading}
							disabled={isLoading}
						>
							{isLoading ? __('Loading...', 'weather-block') : __('Refresh Weather', 'weather-block')}
						</Button>
					)}
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{!location.trim() ? (
					<div className="weather-block__placeholder">
						<div className="weather-block__icon">🌤️</div>
						<h3>{__('Weather Block', 'weather-block')}</h3>
						<p>{__('Enter a location in the block settings to display weather information.', 'weather-block')}</p>
					</div>
				) : isLoading ? (
					<div className="weather-block__loading">
						<Spinner />
						<p>{__('Loading weather data...', 'weather-block')}</p>
					</div>
				) : error ? (
					<div className="weather-block__error">
						<Notice status="error" isDismissible={false}>
							{error}
						</Notice>
					</div>
				) : weatherData ? (
					<div className="weather-block__content">
						<div className="weather-block__header">
							<div className="weather-block__icon">
								{getWeatherIcon(weatherData.icon)}
							</div>
							<div className="weather-block__location">
								<h3>{weatherData.city}{weatherData.country && `, ${weatherData.country}`}</h3>
							</div>
						</div>
						<div className="weather-block__temperature">
							{weatherData.temperature}°{units === 'imperial' ? 'F' : 'C'}
						</div>
						<div className="weather-block__description">
							{weatherData.description.charAt(0).toUpperCase() + weatherData.description.slice(1)}
						</div>
						<div className="weather-block__humidity">
							{__('Humidity:', 'weather-block')} {weatherData.humidity}%
						</div>
					</div>
				) : (
					<div className="weather-block__placeholder">
						<div className="weather-block__icon">🌤️</div>
						<p>{__('Click "Refresh Weather" to load data for', 'weather-block')} {location}</p>
					</div>
				)}
			</div>
		</>
	);
}
