<?php
// This file is generated. Do not modify it manually.
return array(
	'weather-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/weather-block',
		'version' => '0.1.0',
		'title' => 'Weather Block',
		'category' => 'widgets',
		'icon' => 'cloud',
		'description' => 'Display current weather conditions for a specified location using OpenWeatherMap API.',
		'keywords' => array(
			'weather',
			'forecast',
			'temperature',
			'climate'
		),
		'attributes' => array(
			'location' => array(
				'type' => 'string',
				'default' => ''
			),
			'units' => array(
				'type' => 'string',
				'default' => 'metric',
				'enum' => array(
					'metric',
					'imperial'
				)
			),
			'displayMode' => array(
				'type' => 'string',
				'default' => 'auto',
				'enum' => array(
					'light',
					'dark',
					'auto'
				)
			),
			'weatherData' => array(
				'type' => 'object',
				'default' => null
			)
		),
		'example' => array(
			'attributes' => array(
				'location' => 'New York',
				'units' => 'metric',
				'displayMode' => 'light'
			)
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'left',
				'center',
				'right'
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'textdomain' => 'weather-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
