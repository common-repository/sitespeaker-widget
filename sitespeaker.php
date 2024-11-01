<?php
/**
 * Plugin Name: SiteNarrator Widget
 * Plugin URI: http://readaloudwidget.com
 * Description: Speechify your website with the SiteNarrator Text-to-Speech widget
 * Version: 1.9
 * Author: LSD Software
 * Author URI: http://lsdsoftware.com
 */
function sitespeaker_widget($content) {
	if (is_singular('post')) {
		$options = get_option('sitespeaker_settings');
		if ($options['mode'] == 'auto') return $options['code'] . $content;
	}
	return $content;
}

function sitespeaker_activate() {
	add_option('sitespeaker_settings', array(
		'mode' => 'auto',
		'api_key' => 'demo',
		'lang' => 'en-US',
		'voice' => 'free',
		'code' => getEmbedCode('demo', 'en-US', 'free')
	));
}

function sitespeaker_menu() {
	add_options_page('SiteNarrator Widget', 'SiteNarrator Widget', 'manage_options', 'sitespeaker_settings_page', 'sitespeaker_settings');
}

function sitespeaker_settings() {
?>
<div class="wrap">
	<h2>SiteNarrator Widget</h2>
	<form action="options.php" method="post">
		<?php settings_fields('sitespeaker_settings'); ?>
		<?php do_settings_sections('sitespeaker_settings_page'); ?>
		<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>
</div>
<?php
}

function sitespeaker_admin() {
	register_setting( 'sitespeaker_settings', 'sitespeaker_settings', 'sitespeaker_settings_validate' );

	add_settings_section('sitespeaker_status_section', 'Status', 'sitespeaker_status_section_text', 'sitespeaker_settings_page');
	add_settings_field('sitespeaker_mode', 'Mode', 'sitespeaker_settings_mode', 'sitespeaker_settings_page', 'sitespeaker_status_section');

	add_settings_section('sitespeaker_main_settings', 'Configure Widget', 'sitespeaker_main_settings_section_text', 'sitespeaker_settings_page');
	add_settings_field('sitespeaker_key', 'API key', 'sitespeaker_settings_key', 'sitespeaker_settings_page', 'sitespeaker_main_settings');
	add_settings_field('sitespeaker_lang', 'Language', 'sitespeaker_settings_lang', 'sitespeaker_settings_page', 'sitespeaker_main_settings');
	add_settings_field('sitespeaker_voice', 'Voice', 'sitespeaker_settings_voice', 'sitespeaker_settings_page', 'sitespeaker_main_settings');
	add_settings_field('sitespeaker_code', 'Widget Code', 'sitespeaker_settings_code', 'sitespeaker_settings_page', 'sitespeaker_main_settings');
}

function sitespeaker_status_section_text() {
?>
<p style="max-width: 60em; border: 1px dashed lightgray; padding: 1em;">
	Select how you would like to embed the widget. Remember to click 'Save' for changes to take effect.
</p>
<?php
}

function sitespeaker_settings_mode() {
	$options = get_option('sitespeaker_settings');
	$mode = isset($options['mode']) ? $options['mode'] : 'off';
?>
<p>
	<label><input type='radio' name='sitespeaker_settings[mode]' value='off' <?php checked($mode == 'off') ?>> Disabled</label>
</p>
<p>
	<label><input type='radio' name='sitespeaker_settings[mode]' value='auto' <?php checked($mode == 'auto') ?>> I want the widget automatically added to my posts</label>
</p>
<p>
	<label><input type='radio' name='sitespeaker_settings[mode]' value='manual' <?php checked($mode == 'manual') ?>> I will insert the widget myself (use the SiteNarrator block or the embed code)</label>
</p>
<?php
}

function sitespeaker_main_settings_section_text() {
?>
<p style="max-width: 60em; border: 1px dashed lightgray; padding: 1em;">
	The 'demo' API key is not intended for production use.
	Please <a target="_blank" href="https://dashboard.sitenarrator.com/?p=SignUp">sign up</a> for a SiteNarrator account to get your own API key.
	Once you logs in to the <a target="_blank" href="https://dashboard.sitenarrator.com/?p=Login">dashboard</a>, you can find your API key in the User Profile.
	<u>Note</u>: your site's domain must be whitelisted in the dashboard for the API key to work.
</p>
<?php
}

function sitespeaker_settings_key() {
	$options = get_option('sitespeaker_settings');
	$api_key = isset($options['api_key']) ? $options['api_key'] : 'demo';
	echo "<input id='sitespeaker_key' name='sitespeaker_settings[api_key]' size='32' type='text' value='{$api_key}' />";
}


function sitespeaker_settings_lang() {
	$options = get_option('sitespeaker_settings');
	echo "<select id='sitespeaker_lang' name='sitespeaker_settings[lang]' data-value='{$options['lang']}'></select>";
}

function sitespeaker_settings_voice() {
	$options = get_option('sitespeaker_settings');
	echo "<select id='sitespeaker_voice' name='sitespeaker_settings[voice]' data-value='{$options['voice']}'></select> " .
		"<button id='sitespeaker_test' type='button' style='vertical-align: middle;'>Test</button>";
}

function sitespeaker_settings_code() {
	$options = get_option('sitespeaker_settings');
	$code = htmlspecialchars($options['code']);
	echo "<textarea id='sitespeaker_code' name='sitespeaker_settings[code]' cols='60' rows='8' placeholder='Please select all required parameters to see embed code'>{$code}</textarea>";
}

function sitespeaker_settings_validate($input) {
	$mode = trim($input['mode']);
	$key = trim($input['api_key']);
	$lang = trim($input['lang']);
	$voice = trim($input['voice']);
	$code = trim($input['code']);

	if (!preg_match('/^\w+$/', $key)) $key = '';
	if (!preg_match('/^[\w-]+$/', $lang)) $lang = '';
	if (!preg_match('/^[\w -]+$/', $voice)) $voice = '';

	return array(
		'mode' => $mode,
		'api_key' => $key,
		'lang' => $lang,
		'voice' => $voice,
		'code' => $code,
	);
}

register_activation_hook(__FILE__, 'sitespeaker_activate');
add_filter('the_content', 'sitespeaker_widget');
add_action('admin_menu', 'sitespeaker_menu');
add_action('admin_init', 'sitespeaker_admin');
add_action('admin_enqueue_scripts', 'sitespeaker_admin_scripts');
add_action('init', 'sitespeaker_register_block');

function sitespeaker_admin_scripts($page) {
	if ($page == 'settings_page_sitespeaker_settings_page') {
		wp_enqueue_script('main', plugin_dir_url(__FILE__) . 'main.js', array('jquery'), '1.4.6', true);
	}
}

function sitespeaker_register_block() {
	register_block_type(__DIR__ . '/block');
}

function getEmbedCode($key, $lang, $voice) {
	$response = wp_remote_get('https://assets.readaloudwidget.com/embed/code.html');
	if (is_wp_error($response)) {
		return NULL;
	}
	else {
		$body = wp_remote_retrieve_body($response);
		$body = str_replace('${key}', $key, $body);
		$body = str_replace('${lang}', $lang, $body);
		$body = str_replace('${voice}', $voice, $body);
		return $body;
	}
}
