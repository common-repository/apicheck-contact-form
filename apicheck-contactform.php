<?php

/**
 * ApiCheck Contact Form Address Validator
 *
 * @package       APICHECK
 * @author        ApiCheck.nl
 * @license       gplv2
 * @version       2.0.3
 *
 * @wordpress-plugin
 * Plugin Name:   ApiCheck Adres Aanvulling en Validatie voor Contact Form 7
 * Plugin URI:    https://apicheck.nl/wordpress-contact-form-plugin/
 * Description:   Met onze adres Validatie API weet je 100% zeker dat je klanten het juiste adres invullen. Op basis van het geselecteerde land maken wij het de klant zo makkelijk mogelijk gemaakt om een adres in te voeren!
 * Version:       2.0.3
 * Author:        ApiCheck
 * Author URI:    https://apicheck.nl/
 * Text Domain:   apicheck-contactform
 * Domain Path:   /languages/
 * License:       GPLv3
 * License URI:   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with ApiCheck Address Validator. If not, see <https://www.gnu.org/licenses/gpl-3.0.html/>.
 */

// Prevent direct access to the script.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants for easy reference.
define('APICHECKCONTACTFORM_NAME', 'ApiCheck | Automatische Adres Aanvulling voor Contact Form 7');
define('APICHECKCONTACTFORM_SHORT_NAME', 'ApiCheck_Contact_Form');
define('APICHECKCONTACTFORM_VERSION', '2.0.3');
define('APICHECKCONTACTFORM_PLUGIN_FILE', __FILE__);
define('APICHECKCONTACTFORM_PLUGIN_BASE', plugin_basename(APICHECKCONTACTFORM_PLUGIN_FILE));
define('APICHECKCONTACTFORM_PLUGIN_DIR', plugin_dir_path(APICHECKCONTACTFORM_PLUGIN_FILE));
define('APICHECKCONTACTFORM_PLUGIN_URL', plugin_dir_url(APICHECKCONTACTFORM_PLUGIN_FILE));

define('APICHECKCONTACTFORM_SUPPORTED_COUNTRIES', array(
    'NL' => __('Nederland', 'apicheck-contactform'),
    'LU' => __('Luxembourg', 'apicheck-contactform'),
    'BE' => __('België', 'apicheck-contactform'),
    'FR' => __('Frankrijk', 'apicheck-contactform'),
    'DE' => __('Duitsland', 'apicheck-contactform'),
    'CZ' => __('Tsjechië', 'apicheck-contactform'),
    'FI' => __('Finland', 'apicheck-contactform'),
    'IT' => __('Italië', 'apicheck-contactform'),
    'NO' => __('Noorwegen', 'apicheck-contactform'),
    'PL' => __('Polen', 'apicheck-contactform'),
    'PT' => __('Portugal', 'apicheck-contactform'),
    'RO' => __('Roemenië', 'apicheck-contactform'),
    'ES' => __('Spanje', 'apicheck-contactform'),
    'CH' => __('Zwitserland', 'apicheck-contactform'),
    'AT' => __('Oostenrijk', 'apicheck-contactform'),
    'DK' => __('Denemarken', 'apicheck-contactform'),
    'GB' => __('Verenigd Koninkrijk', 'apicheck-contactform'),
    'SE' => __('Zweden', 'apicheck-contactform'),
));

define('APICHECKCONTACTFORM_LOOKUP_COUNTRIES', ['NL', 'LU',]);
define('APICHECKCONTACTFORM_SEARCH_COUNTRIES', ['BE', 'FR', 'DE', 'CZ', 'FI', 'IT', 'NO', 'PL', 'PT', 'RO', 'ES', 'CH', 'AT', 'DK', 'GB', 'SE']);

/**
 * The ApiCheckContactForm class sets up the plugin and enforces dependencies.
 */
class ApiCheckContactForm
{
    private static $_lookup_action = 'apicheckcontactform_lookup_call';
    private static $_search_action = 'apicheckcontactform_search_call';
    private static $_email_validation_action = 'apicheckcontactform_email_validation_call';

    /**
     * Constructor for the ApiCheckContactForm class.
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'checkPluginDependencies']);
        add_action('init', [$this, 'apicheckcontactform_start_from_here']);
        add_action('init', [$this, 'apicheckcontactform_load_textdomain']);
        add_action('wp_enqueue_scripts', [$this, 'apicheckcontactform_enqueue_script_front']);
    }

    /**
     * Adds necessary plugin files and initializes display components if prerequisites are met.
     */
    public function apicheckcontactform_start_from_here()
    {
        require_once APICHECKCONTACTFORM_PLUGIN_DIR . 'includes/api/apicheckcontactform_lookup_call.php';
        require_once APICHECKCONTACTFORM_PLUGIN_DIR . 'includes/api/apicheckcontactform_search_call.php';
        require_once APICHECKCONTACTFORM_PLUGIN_DIR . 'includes/api/apicheckcontactform_email_validation_call.php';

        require_once APICHECKCONTACTFORM_PLUGIN_DIR . 'includes/back/apicheckcontactform_options_page.php';

        // Only activate display when ContactForm7 is enabled, and the plugin setting is turned on.
        if (class_exists('WPCF7') && get_option('apicheckcontactform_enable_disabled') == 1) {
            require_once APICHECKCONTACTFORM_PLUGIN_DIR . 'includes/display/apicheckcontactform_display.php';
            new ApiCheckContactFormDisplay();
        }
    }

    public function apicheckcontactform_load_textdomain()
    {
        $domain = 'apicheck-contactform';
        load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Enqueues styles and scripts for the front-end.
     */
    public function apicheckcontactform_enqueue_script_front()
    {
        $plugin_url = plugins_url('', APICHECKCONTACTFORM_PLUGIN_FILE);

        // Enqueue styles.
        wp_enqueue_style('apicheckcontactform-style', "{$plugin_url}/assets/css/apicheck.css", [], APICHECKCONTACTFORM_VERSION, 'all');
        wp_enqueue_style('s', 'https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.14.0/css/flag-icons.min.css', array(), '6.14.0');

        // Enqueue TypeScript-compiled script.
        $script_args = include(APICHECKCONTACTFORM_PLUGIN_DIR . 'dist/scripts.asset.php');

        // Enqueue your main script along with the asset file as a dependency.
        wp_enqueue_script('apicheckcontactform-typescript', "{$plugin_url}/dist/scripts.js", $script_args['dependencies'], $script_args['version'], true);

        // Set script translations.
        wp_set_script_translations('apicheckcontactform-typescript', 'apicheck-contactform', plugin_dir_path(__FILE__) . 'dist/languages/');

        // Localize the script with necessary data.
        wp_localize_script('apicheckcontactform-typescript', 'apicheckcontactform_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'lookup_action' => self::$_lookup_action,
            'search_action' => self::$_search_action,
            'email_validation_action' => self::$_email_validation_action,
            'validate_number_addition' => get_option('apicheckcontactform_validate_number_addition'),
            'validate_email' => get_option('apicheckcontactform_validate_email'),
            'supported_countries' => APICHECKCONTACTFORM_SUPPORTED_COUNTRIES,
            'search_countries' => APICHECKCONTACTFORM_SEARCH_COUNTRIES,
            'lookup_countries' => APICHECKCONTACTFORM_LOOKUP_COUNTRIES,
        ]);
    }

    /**
     * Checks if the required Contact Form 7 plugin is active and displays an admin notice if not.
     */
    public function checkPluginDependencies()
    {
        if (get_option('apicheckcontactform_enable_disabled') == 1 && !is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
            add_action('admin_notices', [$this, 'showDependencyNotice']);
        }
    }

    /**
     * Displays an admin notice if Contact Form 7 is not active.
     */
    public function showDependencyNotice()
    {
        echo "<div class='notice notice-error is-dismissible'><p>ApiCheck: Contact Form 7 is niet actief. Installeer Contact Form 7 om deze plugin te kunnen gebruiken.</p></div>";
    }
}

// Initialize the plugin.
new ApiCheckContactForm();
