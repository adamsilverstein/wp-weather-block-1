# WordPress Weather Block Plugin

A custom WordPress Gutenberg block that displays current weather conditions for a user-specified location, fetched from the OpenWeatherMap API.

## ✨ Features

*   **Live Weather Data:** Fetches and displays real-time weather information.
*   **Customizable Location:** Easily set any city in the world.
*   **Unit Selection:** Switch between Celsius (°C) and Fahrenheit (°F).
*   **Display Modes:** Choose between Light, Dark, and Auto (respects system preference) modes.
*   **Server-Side Caching:** API responses are cached for 15 minutes to improve performance and reduce API calls.
*   **Dynamic & Accessible:** Renders on the server for SEO and accessibility, with a clean, modern, and accessible design.

## 🛠️ Setup and Installation

### Prerequisites

*   WordPress 6.7 or higher
*   PHP 7.4 or higher
*   Node.js and npm
*   Composer

### Installation Steps

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/wp-weather-block.git
    cd wp-weather-block/weather-block
    ```

2.  **Install PHP and JavaScript dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Get an OpenWeatherMap API Key:**
    *   Sign up for a free account at [OpenWeatherMap](https://openweathermap.org/appid).
    *   Generate an API key.

4.  **Configure the API Key:**
    Open `weather-block.php` and replace the placeholder with your actual API key:
    ```php
    // For testing purposes, define the API key here.
    // In a real plugin, this would be in wp-config.php or a settings page.
    if ( ! defined( 'WEATHER_BLOCK_API_KEY' ) ) {
        define( 'WEATHER_BLOCK_API_KEY', 'your_provided_api_key_here' );
    }
    ```
    For production, it is highly recommended to define this constant in your `wp-config.php` file instead.

5.  **Build the assets:**
    ```bash
    npm run build
    ```

6.  **Activate the plugin:**
    *   Copy the `weather-block` directory to your WordPress `wp-content/plugins` directory.
    *   Activate the "Weather Block" plugin from the WordPress admin dashboard.

## 🚀 Usage

1.  Open the WordPress block editor for a post or page.
2.  Click the `+` icon to add a new block.
3.  Search for "Weather Block" and add it to your content.
4.  Use the settings in the editor sidebar to configure the block:
    *   **Location:** Enter a city name (e.g., "London").
    *   **Units:** Toggle between Celsius and Fahrenheit.
    *   **Display Mode:** Choose Light, Dark, or Auto.
5.  The weather information will be displayed in the editor and on the frontend.

## 🧪 Running Tests

This plugin includes a full suite of tests.

*   **PHPUnit (PHP Tests):**
    ```bash
    npm run test:php
    ```

*   **Jest (JavaScript Tests):**
    ```bash
    npm run test:js
    ```

*   **Playwright (Visual Regression & E2E Tests):**
    ```bash
    npm run test:e2e
    ```

## 📦 Build Process

*   **Development:**
    ```bash
    npm start
    ```
    This command starts a development server and watches for file changes.

*   **Production Build:**
    ```bash
    npm run build
    ```
    This command builds and optimizes the assets for production.

*   **Create a Zip file for distribution:**
    ```bash
    npm run plugin-zip
    ```
    This command bundles the plugin into a distributable `.zip` file in the root directory.
