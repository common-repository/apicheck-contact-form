<?php

// Create custom plugin settings menu
add_action('admin_menu', 'apicheckcontactform_plugin_create_menu');

function apicheckcontactform_plugin_create_menu()
{
    // Create new top-level menu
    add_submenu_page('wpcf7', 'ApiCheck ContactForm 7 Instellingen', 'ApiCheck', 'manage_options', 'apicheckcontactform_submenu', 'apicheckcontactform_plugin_settings_page');

    // Call register settings function
    add_action('admin_init', 'register_apicheckcontactform_plugin_settings');

    // Enqueue the script on the settings page
    add_action('admin_enqueue_scripts', 'apicheck_contactform_enqueue_scripts');
}

function register_apicheckcontactform_plugin_settings()
{
    // Register our settings
    register_setting('apicheckcontactform-plugin-settings-group', 'apicheckcontactform_api_key');
    register_setting('apicheckcontactform-plugin-settings-group', 'apicheckcontactform_enable_disabled');
    register_setting('apicheckcontactform-plugin-settings-group', 'apicheckcontactform_validate_number_addition');
    register_setting('apicheckcontactform-plugin-settings-group', 'apicheckcontactform_validate_email');
    register_setting('apicheckcontactform-plugin-settings-group', 'apicheckcontactform_enabled_countries');
    register_setting('apicheckcontactform-plugin-settings-group', 'apicheckcontactform_all_countries_enabled');

    // Check if "Enable All Countries" is enabled
    $enable_all_countries = get_option('apicheckcontactform_all_countries_enabled', false);

    // If "Enable All Countries" is enabled, set all supported countries as enabled
    if ($enable_all_countries) {
        $supported_countries = array_keys(APICHECKCONTACTFORM_SUPPORTED_COUNTRIES);
        update_option('apicheckcontactform_enabled_countries', $supported_countries);
    }
}

function apicheck_contactform_enqueue_scripts()
{
    $plugin_url = plugins_url('', APICHECKCONTACTFORM_PLUGIN_FILE);
    wp_enqueue_script('apicheck-toggle-visibility', "{$plugin_url}/assets/js/back.js", array('jquery'), APICHECKCONTACTFORM_VERSION, true);
}

function apicheckcontactform_plugin_settings_page()
{
?>
    <div class="wrap" style="background: #fff; padding: 10px 20px;">
        <img height="60px" src="<?php echo (APICHECKCONTACTFORM_PLUGIN_URL) ?>/assets/images/logo.png" alt="ApiCheck Logo">

        <h1><?php _e('ApiCheck voor Contact Form 7', 'apicheck-contactform'); ?></h1>
        <hr>
        <p>
            <?php _e('Met onze adres Validatie API weet je 100% zeker dat je klanten het juiste adres en e-mail adres invullen.', 'apicheck-contactform'); ?>
            <br>
            <?php _e('Op basis van het geselecteerde land maken wij het de klant zo makkelijk mogelijk gemaakt om een adres in te voeren.', 'apicheck-contactform'); ?>
            <br>
            <?php _e('Voor Nederland is een postcode, huisnummer en eventueel toevoeging al voldoende voor een volledig, en compleet adres.', 'apicheck-contactform'); ?>
        </p>

        <?php if (isset($_GET['settings-updated'])) {
            echo "<div class='updated'><p>";
            _e('De instellingen zijn opgeslagen.', 'apicheck-contactform');
            echo "</p></div>";
        } ?>

        <form method="post" action="options.php">
            <table class="form-table">
                <h2 class="title"><?php _e('Algemeen', 'apicheck-contactform'); ?></h2>

                <?php _e('Er zijn geen resultaten gevonden. Probeer een andere zoekopdracht', 'apicheck-contactform'); ?>

                <?php _e('Via het <a target="_blank" href="https://app.apicheck.nl/dashboard/">Dashboard</a> is het mogelijk om een API-key aan te maken. Deze key is in het onderstaand formulier nodig.', 'apicheck-contactform'); ?>
                <br>
                <?php _e('Meer hulp nodig? Neem <a target="_blank" href="https://apicheck.nl/faqs/">contact</a> op, of bekijk de WordPress plugin <a target="_blank" href="https://apicheck.nl/contact-form-plugin-documentatie/">documentatie</a>. ', 'apicheck-contactform'); ?>

                <tr valign="top">
                    <th scope="row"><?php _e('API-key', 'apicheck-contactform-postcode-checker'); ?></th>
                    <td>
                        <input class="regular-text" type="text" name="apicheckcontactform_api_key" value="<?php echo esc_attr(get_option('apicheckcontactform_api_key')); ?>" placeholder="<?php _e('Vul je ApiCheck api-key in', 'apicheck-contactform'); ?>" />
                    </td>
                </tr>
            </table>

            <hr>
            <?php settings_fields('apicheckcontactform-plugin-settings-group'); ?>
            <?php do_settings_sections('apicheckcontactform-plugin-settings-group'); ?>

            <table class="form-table">
                <h2 class="title">Adres validatie</h2>
                <p>Vul automatisch adressen in zodat je zeker weet dat klanten het juiste adres invoeren. <br>Hiermee voorkom je bestellingen naar verkeerde, of niet bestaande adressen.</p>
                <tr valign="top">
                    <th scope="row"><?php _e('Schakel adres aanvulling en validatie in', 'apicheck-contactform'); ?></th>
                    <td><input type="checkbox" name="apicheckcontactform_enable_disabled" value="1" <?php checked(1, get_option('apicheckcontactform_enable_disabled'), true); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Valideer ook huisnummer-toevoeging', 'apicheck-contactform'); ?></th>
                    <td><input type="checkbox" name="apicheckcontactform_validate_number_addition" value="1" <?php checked(1, get_option('apicheckcontactform_validate_number_addition'), true); ?> /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Selecteer de landen die je wil ondersteunen', 'apicheck-contactform'); ?></th>
                    <td>
                        <?php
                        $selected_countries = get_option('apicheckcontactform_enabled_countries', array());
                        ?>
                        <select name="apicheckcontactform_enabled_countries[]" multiple="multiple" style="min-height: 150px;" id="country-selection">
                            <?php
                            foreach (APICHECKCONTACTFORM_SUPPORTED_COUNTRIES as $country_code => $country_name) {
                                $selected = in_array($country_code, $selected_countries) ? 'selected="selected"' : '';
                                echo '<option value="' . $country_code . '" ' . $selected . '>' . $country_code . ' - ' . $country_name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Alle landen inschakelen', 'apicheck-contactform'); ?></th>
                    <td>
                        <input type="checkbox" name="apicheckcontactform_all_countries_enabled" id="enable-all-countries" value="1" <?php checked(1, get_option('apicheckcontactform_all_countries_enabled'), true); ?> />
                    </td>
                </tr>
            </table>

            <hr>

            <table class="form-table">
                <h2 class="title"><?php _e('E-mail validatie', 'apicheck-contactform'); ?></h2>
                <p><?php _e('Controleer op fouten in het ingevoerde email adres.', 'apicheck-contactform'); ?> <br><?php _e('Hiermee voorkom je dat belangrijke order-updates niet bij de klant terecht komen.', 'apicheck-contactform'); ?></p>
                <tr valign="top">
                    <th scope="row"><?php _e('Schakel e-mail validatie in', 'apicheck-contactform'); ?></th>
                    <td><input type="checkbox" name="apicheckcontactform_validate_email" value="1" <?php checked(1, get_option('apicheckcontactform_validate_email'), true); ?> /></td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
<?php } ?>