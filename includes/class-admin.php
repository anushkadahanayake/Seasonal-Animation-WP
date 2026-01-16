<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Seasonal_Animation_Admin {

	private $option_name = 'seasonal_animation_settings';

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_init', array( $this, 'handle_reset_action' ) );
	}

	public function handle_reset_action() {
		if ( isset( $_POST['seasonal_reset'] ) && check_admin_referer( 'seasonal_reset_action', 'seasonal_reset_nonce' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			
			delete_option( $this->option_name );
			
			add_settings_error( 'seasonal_messages', 'seasonal_reset', 'Settings successfully reset to defaults.', 'updated' );
		}
	}

	public function enqueue_admin_assets( $hook ) {
		// Only load on our settings page
		if ( 'toplevel_page_seasonal-animation' !== $hook ) {
			return;
		}

		// Enqueue WordPress Media Uploader
		wp_enqueue_media();

		// Enqueue our custom uploader handler
		wp_enqueue_script( 
			'seasonal-admin-uploader', 
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin-uploader.js', 
			array( 'jquery' ), 
			'1.0.0', 
			true 
		);

		// Enqueue New Admin UI Assets
		wp_enqueue_style(
			'seasonal-admin-ui-css',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin-ui.css',
			array(),
			'1.1.0'
		);

		wp_enqueue_script(
			'seasonal-admin-ui-js',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin-ui.js',
			array( 'jquery' ),
			'1.1.0',
			true
		);
	}

	public function add_admin_menu() {
		add_menu_page(
			'Seasonal Animation',
			'Seasonal Animation',
			'manage_options',
			'seasonal-animation',
			array( $this, 'settings_page_html' ),
			'dashicons-calendar-alt', // Icon: Calendar for seasons
			90 // Position
		);
	}

	public function register_settings() {
		// Register a single setting group for all options
		register_setting( $this->option_name, $this->option_name );
		
		// Note: We are now manually rendering fields inside tabs in settings_page_html
		// so we don't strictly need add_settings_section/add_settings_field unless 
		// we want to stick to the WP Settings API strict rendering. 
		// However, to keep it clean and use our new "Card" layout, 
		// we will iterate through our own render methods inside the HTML.
	}

	public function settings_page_html() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Add status messages (e.g. settings saved)
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'seasonal_messages', 'seasonal_message', __( 'Settings Saved', 'seasonal-animation' ), 'updated' );
		}

		settings_errors( 'seasonal_messages' );
		?>
		<div class="wrap seasonal-admin-wrap">
			<div class="seasonal-header">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			</div>
			
			<!-- Crash Protection Banner -->
			<div class="crash-protection-card">
				<strong>🚀 Crash Protection:</strong> If this plugin ever breaks your site, append <code>?seasonal_safe_mode=1</code> to your URL to disable it safe-mode style.
			</div>

			<form action="options.php" method="post">
				<?php
				// Security fields for the registered setting
				settings_fields( $this->option_name );
				?>

				<!-- Tabs Navigation -->
				<ul class="seasonal-tabs-nav">
					<li><a href="#tab-general" class="seasonal-tab-link active"><span class="dashicons dashicons-admin-generic"></span> General</a></li>
					<li><a href="#tab-visuals" class="seasonal-tab-link"><span class="dashicons dashicons-art"></span> Visuals</a></li>
					<li><a href="#tab-emojis" class="seasonal-tab-link"><span class="dashicons dashicons-smiley"></span> Emojis</a></li>
					<li><a href="#tab-calendar" class="seasonal-tab-link"><span class="dashicons dashicons-calendar"></span> Calendar</a></li>
					<li><a href="#tab-scheduler" class="seasonal-tab-link"><span class="dashicons dashicons-clock"></span> Scheduler</a></li>
					<li><a href="#tab-visibility" class="seasonal-tab-link"><span class="dashicons dashicons-visibility"></span> Visibility</a></li>
				</ul>

				<!-- TAB 1: General -->
				<div id="tab-general" class="seasonal-tab-content active">
					<div class="seasonal-card">
						<h3>Main Configuration</h3>
						<table class="form-table">
							<tr>
								<th scope="row">Season Mode</th>
								<td><?php $this->render_season_select(); ?></td>
							</tr>
							<tr>
								<th scope="row">Status</th>
								<td><?php $this->render_preview_mode_checkbox(); ?></td>
							</tr>
							<tr>
								<th scope="row">Master Switch</th>
								<td><?php $this->render_master_disable(); ?></td>
							</tr>
						</table>
					</div>

					<div class="seasonal-card">
						<h3>Logic & Timing</h3>
						<table class="form-table">
							<tr>
								<th scope="row">Climate Zone</th>
								<td><?php $this->render_hemisphere(); ?></td>
							</tr>
							<tr>
								<th scope="row">Holiday Buffer</th>
								<td><?php $this->render_holiday_buffer(); ?></td>
							</tr>
						</table>
					</div>
				</div>

				<!-- TAB 2: Visuals -->
				<div id="tab-visuals" class="seasonal-tab-content">
					<div class="seasonal-card">
						<h3>Appearance</h3>
						<table class="form-table">
							<tr>
								<th scope="row">Particle Type</th>
								<td><?php $this->render_particle_type_select(); ?></td>
							</tr>
							<tr>
								<th scope="row">Custom Value</th>
								<td><?php $this->render_custom_particle_input(); ?></td>
							</tr>
							<tr>
								<th scope="row">Intensity / Frequency</th>
								<td><?php $this->render_frequency_select(); ?></td>
							</tr>
							<tr>
								<th scope="row">Fall Speed (Seconds)</th>
								<td><?php $this->render_speed_input(); ?></td>
							</tr>
							<tr>
								<th scope="row">Atmosphere</th>
								<td><?php $this->render_fog_checkbox(); ?></td>
							</tr>
						</table>
					</div>

					<div class="seasonal-card">
						<h3>Interaction</h3>
						<table class="form-table">
							<tr>
								<th scope="row">Interaction Mode</th>
								<td><?php $this->render_interaction_mode(); ?></td>
							</tr>
							<tr>
								<th scope="row">Z-Index Level</th>
								<td><?php $this->render_z_index(); ?></td>
							</tr>
						</table>
					</div>
				</div>

				<!-- TAB 3: Emojis (New) -->
				<div id="tab-emojis" class="seasonal-tab-content">
					<div class="seasonal-card">
						<h3>🎨 Customize Season Emojis</h3>
						<p class="description" style="margin-bottom:15px;">Don't like the default icons? Paste your own emojis here to override them globally.</p>
						<table class="form-table">
							<?php 
							$seasons_list = array(
								'winter'    => 'Winter (Default: ❄)',
								'summer'    => 'Summer (Default: ✨)',
								'autumn'    => 'Autumn (Default: 🍂)',
								'spring'    => 'Spring (Default: 🌸)',
								'monsoon'   => 'Monsoon/Rain (Default: 💧)',
								'halloween' => 'Halloween (Default: 🎃)',
								'christmas' => 'Christmas (Default: 🎅)',
								'valentines' => 'Valentines (Default: ❤️)',
							);
							foreach($seasons_list as $key => $label) {
								echo '<tr><th scope="row">' . esc_html($label) . '</th><td>';
								$this->render_season_emoji_input(array('id' => $key));
								echo '</td></tr>';
							}
							?>
						</table>
					</div>
				</div>

				<!-- TAB 4: Calendar -->
				<div id="tab-calendar" class="seasonal-tab-content">
					<div class="seasonal-card">
						<h3>📅 Global Holiday Calendar (Editable)</h3>
						<p class="description" style="margin-bottom:15px;">Set the specific dates for this year. If a holiday changes (like Vesak or Lunar New Year), update it here.</p>
						<table class="form-table">
							<?php 
							$holidays = array(
								'chinese_new_year' => 'Chinese New Year (Lunar)',
								'vesak'            => 'Vesak Poya (Buddha Day)',
								'poson'            => 'Poson Poya (Sri Lanka)',
								'songkran'         => 'Songkran (Thai New Year)',
								'diwali'           => 'Diwali (Festival of Lights)',
								'black_friday'     => 'Black Friday (Shopping)',
								'christmas'        => 'Christmas Day',
								'halloween'        => 'Halloween',
							);
							foreach($holidays as $key => $label) {
								echo '<tr><th scope="row">' . esc_html($label) . '</th><td>';
								$this->render_date_input(array('id' => $key));
								echo '</td></tr>';
							}
							?>
						</table>
					</div>
				</div>

				<!-- TAB 4: Scheduler -->
				<div id="tab-scheduler" class="seasonal-tab-content">
					<div class="seasonal-card">
						<h3>📅 Scheduler Override</h3>
						<p>Use this to force a specific animation for a specific date range (e.g., a Birthday or special promo).</p>
						<table class="form-table">
							<tr>
								<th scope="row">Start Date</th>
								<td><?php $this->render_schedule_start(); ?></td>
							</tr>
							<tr>
								<th scope="row">End Date</th>
								<td><?php $this->render_schedule_end(); ?></td>
							</tr>
							<tr>
								<th scope="row">Recurrence</th>
								<td><?php $this->render_schedule_recur(); ?></td>
							</tr>
							<tr>
								<th scope="row">Scheduled Icon/Image</th>
								<td><?php $this->render_schedule_particle(); ?></td>
							</tr>
						</table>
					</div>
				</div>

				<!-- TAB 5: Visibility -->
				<div id="tab-visibility" class="seasonal-tab-content">
					<div class="seasonal-card">
						<h3>👁️ Visibility Constraints</h3>
						<table class="form-table">
							<tr>
								<th scope="row">Mobile Devices</th>
								<td><?php $this->render_hide_mobile(); ?></td>
							</tr>
							<tr>
								<th scope="row">Homepage</th>
								<td><?php $this->render_only_homepage(); ?></td>
							</tr>
							<tr>
								<th scope="row">WooCommerce</th>
								<td><?php $this->render_exclude_woo(); ?></td>
							</tr>
							<tr>
								<th scope="row">Exclude Page IDs</th>
								<td><?php $this->render_exclude_ids(); ?></td>
							</tr>
						</table>
					</div>
				</div>

				<div style="margin-top: 20px;">
					<?php submit_button( 'Save Settings', 'primary large seasonal-save' ); ?>
				</div>

			</form>
			
			<div class="seasonal-danger-zone">
				<h3>⚠️ Danger Zone</h3>
				<form method="post" action="">
					<?php wp_nonce_field( 'seasonal_reset_action', 'seasonal_reset_nonce' ); ?>
					<input type="hidden" name="seasonal_reset" value="1">
					<p>
						<button type="submit" class="button" style="color: #b32d2e; border-color: #b32d2e;" onclick="return confirm('Are you sure? This will reset all Dates and Settings to default.');">
							Reset All Settings to Defaults
						</button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	// --- RENDER HELPERS (Modified for new Toggle UI where appropriate) ---

	public function render_season_select() {
		$options = get_option( $this->option_name );
		$current = isset( $options['active_season'] ) ? $options['active_season'] : 'auto';
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[active_season]">
			<option value="auto" <?php selected( $current, 'auto' ); ?>>Automatic (Detect by Date)</option>
			<option value="spring" <?php selected( $current, 'spring' ); ?>>Force Spring (March - May)</option>
			<option value="summer" <?php selected( $current, 'summer' ); ?>>Force Summer (June - August)</option>
			<option value="autumn" <?php selected( $current, 'autumn' ); ?>>Force Autumn (September - November)</option>
			<option value="winter" <?php selected( $current, 'winter' ); ?>>Force Winter (December - February)</option>
			<optgroup label="Holidays & Special Days">
				<option value="newyear" <?php selected( $current, 'newyear' ); ?>>New Year (Dec 31 - Jan 1)</option>
				<option value="valentines" <?php selected( $current, 'valentines' ); ?>>Valentine's Day (Feb 14)</option>
				<option value="patrick" <?php selected( $current, 'patrick' ); ?>>St. Patrick's Day (Mar 17)</option>
				<option value="halloween" <?php selected( $current, 'halloween' ); ?>>Halloween (Oct 31)</option>
				<option value="christmas" <?php selected( $current, 'christmas' ); ?>>Christmas (Dec 24 - 25)</option>
			</optgroup>
		</select>
		<p class="description">Select "Automatic" to let the plugin decide based on the server date.</p>
		<?php
	}

	public function render_particle_type_select() {
		$options = get_option( $this->option_name );
		$current = isset( $options['particle_type'] ) ? $options['particle_type'] : 'default';
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[particle_type]">
			<option value="default" <?php selected( $current, 'default' ); ?>>Default (Based on Season)</option>
			<option value="emoji" <?php selected( $current, 'emoji' ); ?>>Custom Emoji / Character</option>
			<option value="image" <?php selected( $current, 'image' ); ?>>Custom Image URL</option>
		</select>
		<?php
	}

	public function render_custom_particle_input() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['custom_particle_value'] ) ? $options['custom_particle_value'] : '';
		?>
		<div style="display:flex; gap:10px; align-items:center;">
			<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[custom_particle_value]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="Emoji or URL">
			<button class="button seasonal-upload-btn">Select Image</button>
		</div>
		<p class="description">
			If <strong>Custom Emoji</strong>: Paste a single emoji (e.g. 🎃).<br>
			If <strong>Custom Image</strong>: Click "Select Image". Recommend 30x30px transparent PNG.
		</p>
		<?php
	}

	public function render_fog_checkbox() {
		$options = get_option( $this->option_name );
		$current = isset( $options['enable_fog'] ) ? $options['enable_fog'] : '0';
		?>
		<label class="seasonal-toggle">
			<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[enable_fog]" value="0">
			<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[enable_fog]" value="1" <?php checked( $current, '1' ); ?>>
			<span class="seasonal-slider"></span>
		</label>
		<span style="margin-left: 10px; vertical-align: middle;">Enable Fog/Smoke Animation</span>
		<?php
	}

	public function render_frequency_select() {
		$options = get_option( $this->option_name );
		$current = isset( $options['particle_frequency'] ) ? $options['particle_frequency'] : '30';
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[particle_frequency]">
			<option value="10" <?php selected( $current, '10' ); ?>>Very Low (10 Particles)</option>
			<option value="20" <?php selected( $current, '20' ); ?>>Low (20 Particles)</option>
			<option value="30" <?php selected( $current, '30' ); ?>>Medium (30 Particles)</option>
			<option value="60" <?php selected( $current, '60' ); ?>>High (60 Particles)</option>
			<option value="100" <?php selected( $current, '100' ); ?>>Very High (100 Particles)</option>
		</select>
		<?php
	}

	public function render_master_disable() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['master_disable'] ) ? $options['master_disable'] : '0';
		?>
		<label class="seasonal-toggle">
			<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[master_disable]" value="1" <?php checked( $val, '1' ); ?>>
			<span class="seasonal-slider"></span>
		</label>
		<span style="margin-left: 10px; vertical-align: middle;"><strong>Temporarily Disable All Animations</strong> (Panic Switch)</span>
		<?php
	}

	public function render_hemisphere() {
		$options = get_option( $this->option_name );
		$current_zone = 'northern';
		if ( isset( $options['climate_zone'] ) ) {
			$current_zone = $options['climate_zone'];
		} elseif ( isset( $options['hemisphere'] ) ) {
			$current_zone = $options['hemisphere'];
		}
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[climate_zone]">
			<option value="northern" <?php selected( $current_zone, 'northern' ); ?>>Northern Hemisphere (US, UK - Snow in Dec)</option>
			<option value="southern" <?php selected( $current_zone, 'southern' ); ?>>Southern Hemisphere (Aus - Sun in Dec)</option>
			<option value="tropical" <?php selected( $current_zone, 'tropical' ); ?>>Tropical (Sri Lanka, SG - Rain/Sun)</option>
		</select>
		<?php
	}

	public function render_holiday_buffer() {
		$options = get_option( $this->option_name );
		$before  = isset( $options['buffer_before'] ) ? $options['buffer_before'] : '0';
		$after   = isset( $options['buffer_after'] ) ? $options['buffer_after'] : '0';
		?>
		<div style="display: flex; gap: 20px;">
			<label>Start <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[buffer_before]" value="<?php echo esc_attr( $before ); ?>" class="small-text" min="0" max="30"> days before.</label>
			<label>End <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[buffer_after]" value="<?php echo esc_attr( $after ); ?>" class="small-text" min="0" max="30"> days after.</label>
		</div>
		<p class="description">Extensions apply to automatic holidays.</p>
		<?php
	}

	public function render_schedule_start() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['schedule_start'] ) ? $options['schedule_start'] : '';
		echo '<input type="date" name="' . esc_attr( $this->option_name ) . '[schedule_start]" value="' . esc_attr( $val ) . '">';
	}

	public function render_schedule_end() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['schedule_end'] ) ? $options['schedule_end'] : '';
		echo '<input type="date" name="' . esc_attr( $this->option_name ) . '[schedule_end]" value="' . esc_attr( $val ) . '">';
	}

	public function render_schedule_recur() {
		$options = get_option( $this->option_name );
		$recur   = isset( $options['schedule_recur'] ) ? $options['schedule_recur'] : '0';
		?>
		<label class="seasonal-toggle">
			<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[schedule_recur]" value="1" <?php checked( $recur, '1' ); ?>>
			<span class="seasonal-slider"></span>
		</label>
		<span style="margin-left: 10px; vertical-align: middle;">Repeat every year (Ignore Year)</span>
		<?php
	}

	public function render_schedule_particle() {
		$options = get_option( $this->option_name );
		$type    = isset( $options['schedule_type'] ) ? $options['schedule_type'] : 'emoji';
		$val     = isset( $options['schedule_value'] ) ? $options['schedule_value'] : '';
		?>
		<div style="display:flex; gap:10px; align-items:center;">
			<select name="<?php echo esc_attr( $this->option_name ); ?>[schedule_type]">
				<option value="emoji" <?php selected( $type, 'emoji' ); ?>>Emoji</option>
				<option value="image" <?php selected( $type, 'image' ); ?>>Image URL</option>
			</select>
			<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[schedule_value]" value="<?php echo esc_attr( $val ); ?>" placeholder="🎃 or URL" class="regular-text">
			<button class="button seasonal-upload-btn">Select Image</button>
		</div>
		<?php
	}

	public function render_hide_mobile() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['hide_mobile'] ) ? $options['hide_mobile'] : '0';
		?>
		<label class="seasonal-toggle">
			<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[hide_mobile]" value="1" <?php checked( $val, '1' ); ?>>
			<span class="seasonal-slider"></span>
		</label>
		<span style="margin-left: 10px; vertical-align: middle;">Disable on Mobile Devices</span>
		<?php
	}

	public function render_only_homepage() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['only_homepage'] ) ? $options['only_homepage'] : '0';
		?>
		<label class="seasonal-toggle">
			<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[only_homepage]" value="1" <?php checked( $val, '1' ); ?>>
			<span class="seasonal-slider"></span>
		</label>
		<span style="margin-left: 10px; vertical-align: middle;">Show on Homepage ONLY</span>
		<?php
	}

	public function render_exclude_woo() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['exclude_woo'] ) ? $options['exclude_woo'] : '0';
		?>
		<label class="seasonal-toggle">
			<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[exclude_woo]" value="1" <?php checked( $val, '1' ); ?>>
			<span class="seasonal-slider"></span>
		</label>
		<span style="margin-left: 10px; vertical-align: middle;">Disable on Cart & Checkout pages</span>
		<?php
	}

	public function render_exclude_ids() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['exclude_ids'] ) ? $options['exclude_ids'] : '';
		echo '<input type="text" name="' . esc_attr( $this->option_name ) . '[exclude_ids]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="e.g. 5, 21, 104">';
		echo '<p class="description">Comma-separated list of Page/Post IDs to exclude.</p>';
	}

	public function render_interaction_mode() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['interaction_mode'] ) ? $options['interaction_mode'] : 'none';
		?>
		<select name="<?php echo esc_attr( $this->option_name ); ?>[interaction_mode]">
			<option value="none" <?php selected( $val, 'none' ); ?>>Pass-through Clicks (Recommended)</option>
			<option value="auto" <?php selected( $val, 'auto' ); ?>>Block Clicks (Interactive Particles)</option>
		</select>
		<?php
	}

	public function render_z_index() {
		$options = get_option( $this->option_name );
		$val     = isset( $options['z_index'] ) ? $options['z_index'] : '9999';
		echo '<input type="number" name="' . esc_attr( $this->option_name ) . '[z_index]" value="' . esc_attr( $val ) . '" class="small-text">';
	}

	public function render_calendar_info() {
		// Used in old callback, leaving empty or deprecated
	}

	public function render_date_input( $args ) {
		$options = get_option( $this->option_name );
		$id      = $args['id'];
		$key     = 'date_' . $id;
		
		// DEFAULT DATES (2026)
		$defaults = array(
			'chinese_new_year' => '2026-02-17',
			'vesak'            => '2026-05-01',
			'poson'            => '2026-06-29',
			'songkran'         => '2026-04-13',
			'diwali'           => '2026-11-08',
			'black_friday'     => '2026-11-27',
			'christmas'        => '2026-12-25',
			'halloween'        => '2026-10-31',
		);

		$value = isset( $options[ $key ] ) ? $options[ $key ] : $defaults[ $id ];
		echo '<input type="date" name="' . esc_attr( $this->option_name ) . '[' . $key . ']" value="' . esc_attr( $value ) . '">';
	}

	public function render_preview_mode_checkbox() {
		$options = get_option( $this->option_name );
		$current = isset( $options['preview_mode'] ) ? $options['preview_mode'] : 'preview';
		?>
		<label class="seasonal-toggle">
			<input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[preview_mode]" value="preview" <?php checked( $current, 'preview' ); ?>>
			<span style="margin-left:5px">Preview (Admins)</span>
		</label>
		&nbsp;&nbsp;
		<label class="seasonal-toggle">
			<input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[preview_mode]" value="live" <?php checked( $current, 'live' ); ?>>
			<span style="margin-left:5px">Live (Everyone)</span>
		</label>
		<?php
	}

	public function render_speed_input() {
		$options = get_option( $this->option_name );
		$val = isset( $options['animation_speed'] ) ? $options['animation_speed'] : ''; 
		?>
		<input type="number" step="0.1" name="<?php echo esc_attr( $this->option_name ); ?>[animation_speed]" value="<?php echo esc_attr( $val ); ?>" class="small-text" placeholder="e.g. 4.6"> seconds
		<p class="description">Lower = Faster. Higher = Slower. (Rain recommended: 1.5s - 4.0s)</p>
		<?php
	}

	public function render_emoji_section_info() {
		echo '<p>Don\'t like the default icons? Paste your own emojis here to override them globally.</p>';
	}

	public function render_season_emoji_input( $args ) {
		$options = get_option( $this->option_name );
		$key = 'emoji_' . $args['id'];
		$val = isset( $options[ $key ] ) ? $options[ $key ] : '';
		echo '<input type="text" name="' . esc_attr( $this->option_name ) . '[' . $key . ']" value="' . esc_attr( $val ) . '" class="regular-text" style="width: 50px; font-size: 1.5em;">';
	}
}
