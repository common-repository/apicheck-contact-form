<?php

class ApiCheckContactFormDisplay
{
	public function __construct()
	{
		add_action('wpcf7_init', array($this, 'apicheck_address_tag_generator'));
		add_action('admin_init', array($this, 'apicheck_add_address_tag_generator_menu'));
		add_action('wpcf7_validate_apicheckaddressfields*',  array($this, 'apicheck_conact_form_address_validation_filter'), 10, 2);
		add_action('wpcf7_validate_apicheckaddressfields',  array($this, 'apicheck_conact_form_address_validation_filter'), 10, 2);
	}

	public function apicheck_address_tag_generator()
	{
		wpcf7_add_form_tag(array('apicheckaddressfields', 'apicheckaddressfields*'), array($this, 'GWAA_wpcf7_cfpl_products_shortcode_handler'), true);
	}

	public function GWAA_wpcf7_cfpl_products_shortcode_handler($tag)
	{
		if (empty($tag->name)) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error($tag->name);
		$class = wpcf7_form_controls_class($tag->type, 'apicheckaddressfields');

		if ($validation_error) {
			$class .= ' wpcf7-not-valid';
		}

		$atts = array();
		$atts['id']	= "full_address_field";
		$atts['tabindex'] = $tag->get_option('tabindex', 'int', true);

		if ($tag->has_option('readonly')) {
			$atts['readonly'] = 'readonly';
		}

		if ($tag->is_required()) {
			$atts['aria-required'] = 'true';
		}

		if ($tag->is_required()) {
			$required = 'required';
		} else $required = '';

		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

		if ($tag->has_option('placeholder')) {
			$place = $tag->get_option('placeholder', '[-0-9a-zA-Z_\s]+', true);
			$place = str_replace("_", " ", $place);
			$atts['placeholder'] = $place;
		}

		$atts['type']	= 'text';
		$atts['name']	= $tag->name;
		$atts = wpcf7_format_atts($atts);
		$this->fields[$tag->name]   = $tag->values;
		$this->names[]  = $tag->name;
		ob_start();
?>
		<div id="apicheckaddressfields" class="container wpcf7-form-control-wrap <?php echo esc_attr($tag->name) ?>">
			<div class="noaddressfoundmessage" id="noAddressFoundMessage" style="display: none; color: red;"></div>
			<textarea hidden <?php echo esc_attr($atts); ?>></textarea>

			<div class="form-group">
				<label for="countryDropdown" data-i18n="country_select"><?php _e('Selecteer een land', 'apicheck-contactform'); ?></label>
				<div class="custom-select" id="countryDropdown">
					<div class="select-selected" id="selectedCountry">
						<span class="fi fi-nl"></span> Nederland
					</div>
					<ul class="select-items">
						<?php
						$enabled_countries = get_option('apicheckcontactform_enabled_countries', array());

						$supported_countries = APICHECKCONTACTFORM_SUPPORTED_COUNTRIES;

						foreach ($enabled_countries as $country_code) {
							if (isset($supported_countries[$country_code])) {
								$country_name = $supported_countries[$country_code];
								$lowercase_country_code = strtolower($country_code);
								echo "<li data-value='$lowercase_country_code'>";
								echo "<span class='fi fi-$lowercase_country_code' data-country-name='" . esc_html($country_name) . "'></span> " . esc_html($country_name);
								echo '</li>';
							}
						}
						?>
						<li data-value="other"><span></span> <span>Anders...</span></li>
					</ul>
				</div>
			</div>

			<div style="display:hidden;" class="form-group inline-group" id="country_field">
				<div>
					<label for="country_field" data-i18n="label.country">Land</label>
					<input id="country_input_field" type="text" name="<?php echo esc_attr($tag->name); ?>_country">
				</div>
			</div>

			<!-- Address Search -->
			<div class="form-group" id="autocompleteField">
				<label data-i18n="label.searchForAddress" for="search">Adres zoeken</label>
				<input id="search" autocomplete="off" type="text" data-i18n-placeholder="placeholder.search" placeholder="Zoek op straat, postcode of plaats..." />
				<span class="spinner" id="loadingSpinner" style="display: none;"></span>
				<div id="results"></div>

				<button type="button" id="clearSearchButton" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="30" viewBox="0 0 26 26">
						<path d="M 21.734375 19.640625 L 19.636719 21.734375 C 19.253906 22.121094 18.628906 22.121094 18.242188 21.734375 L 13 16.496094 L 7.761719 21.734375 C 7.375 22.121094 6.746094 22.121094 6.363281 21.734375 L 4.265625 19.640625 C 3.878906 19.253906 3.878906 18.628906 4.265625 18.242188 L 9.503906 13 L 4.265625 7.761719 C 3.882813 7.371094 3.882813 6.742188 4.265625 6.363281 L 6.363281 4.265625 C 6.746094 3.878906 7.375 3.878906 7.761719 4.265625 L 13 9.507813 L 18.242188 4.265625 C 18.628906 3.878906 19.257813 3.878906 19.636719 4.265625 L 21.734375 6.359375 C 22.121094 6.746094 22.121094 7.375 21.738281 7.761719 L 16.496094 13 L 21.734375 18.242188 C 22.121094 18.628906 22.121094 19.253906 21.734375 19.640625 Z"></path>
					</svg></button>
				<a href="#" id="manualEntry">Handmatig invoeren</a>
			</div>

			<div id="addressFields">
				<div class="noaddressfoundmessage" id="noAddressFoundMessage" style="display: none; color: red;"></div>

				<div class="form-group inline-group">
					<div>
						<label for="postalcode_field" data-i18n="label.postalcode">Postcode</label>
						<input id="postalcode_field" autocomplete="off" type="text" placeholder="" <?php echo $required; ?> name="<?php echo esc_attr($tag->name); ?>_postalcode" required>
					</div>

					<div>
						<label for="housenumber_field" data-i18n="label.housenumber">Huisnummer</label>
						<input id="housenumber_field" autocomplete="off" type="text" placeholder="" <?php echo $required; ?> name="<?php echo esc_attr($tag->name); ?>_housenumber" required>
					</div>

					<div>
						<label for="housenumberAddition_field" data-i18n="label.housenumberAddition">Toevoeging</label>
						<input id="housenumberAddition_field" autocomplete="off" type="text" placeholder="" name="<?php echo esc_attr($tag->name); ?>_housenumberAddition">
					</div>
				</div>

				<div class="form-group inline-group">
					<div>
						<label for="street_field" data-i18n="label.street">Straat</label>
						<input id="street_field" autocomplete="off" type="text" placeholder="" <?php echo $required; ?> name="<?php echo esc_attr($tag->name); ?>_street" required>
					</div>

					<div>
						<label for="city_field" data-i18n="label.city">Plaats</label>
						<input id="city_field" autocomplete="off" type="text" placeholder="" <?php echo $required; ?> name="<?php echo esc_attr($tag->name); ?>_city" required>
					</div>
				</div>
			</div>
		</div>

	<?php
		$html = ob_get_clean();
		return $html;
	}

	public function apicheck_add_address_tag_generator_menu()
	{
		$tag_generator = WPCF7_TagGenerator::get_instance();
		$tag_generator->add('apicheckaddressfields', __('ApiCheck Adres', 'gwaa'), array($this, 'apicheck_wpcf7_tag_products_generator_menu'));
	}

	function apicheck_wpcf7_tag_products_generator_menu($contact_form, $args = '')
	{
		$args = wp_parse_args($args, array());
		$type = 'apicheckaddressfields';
	?>

		<div class="control-box">
			<fieldset>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php echo esc_html(__('Field type', 'contact-form-7')); ?></th>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><?php echo esc_html(__('Field type', 'contact-form-7')); ?></legend>
									<label><input type="checkbox" name="required" /> <?php echo esc_html(__('Required field', 'contact-form-7')); ?></label>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr($args['content'] . '-name'); ?>"><?php echo esc_html(__('Name', 'contact-form-7')); ?></label></th>
							<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr($args['content'] . '-name'); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr($args['content'] . '-id'); ?>"><?php echo esc_html(__('Id attribute', 'contact-form-7')); ?></label></th>
							<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr($args['content'] . '-id'); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr($args['content'] . '-class'); ?>"><?php echo esc_html(__('Class attribute', 'contact-form-7')); ?></label></th>
							<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr($args['content'] . '-class'); ?>" /></td>
						</tr>

					</tbody>
				</table>
			</fieldset>
		</div>
		<div class="insert-box">
			<input type="text" name="<?php echo esc_attr($type); ?>" class="tag code" readonly="readonly" onfocus="this.select()" />
			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr(__('Insert Tag', 'contact-form-7')); ?>" />
			</div>
			<br class="clear" />
			<p class="description mail-tag"><label for="<?php echo esc_attr($args['content'] . '-mailtag'); ?>"><?php echo sprintf(esc_html(__("To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7')), '<strong><span class="mail-tag"></span></strong>'); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr($args['content'] . '-mailtag'); ?>" /></label></p>
		</div>
<?php
	}

	public function apicheck_conact_form_address_validation_filter($result, $tag)
	{
		$tag = new WPCF7_Shortcode($tag);
		$name = $tag->name;

		$fields_to_check = [
			'_country' => 'Country',
			'_postalcode' => 'Postal Code',
			'_housenumber' => 'House Number',
			'_street' => 'Street',
			'_city' => 'City'
		];

		$errors = [];
		foreach ($fields_to_check as $field => $field_label) {
			$field_name = $name . $field;
			if ($tag->is_required() && (empty($_POST[$field_name]) || '0' === $_POST[$field_name])) {
				$errors[] = "Please fill in the " . $field_label . ".";
			}
		}

		if (!empty($errors)) {
			$errorMessage = implode(' ', $errors);
			$result->invalidate($tag, $errorMessage);
		}

		return $result;
	}
}
?>