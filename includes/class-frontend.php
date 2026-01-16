<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Seasonal_Animation_Frontend {

	private $option_name = 'seasonal_animation_settings';

	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Determine the current season to display.
	 */
	public function get_current_season() {
		$options = get_option( $this->option_name );
		$forced  = isset( $options['active_season'] ) ? $options['active_season'] : 'auto';

		if ( 'auto' !== $forced ) {
			return $forced;
		}

		// Automatic detection
		$month = (int) date( 'n' );
		$day   = (int) date( 'j' );
		$year  = (int) date( 'Y' );

		// Get Buffer Settings
		$buffer_before = isset( $options['buffer_before'] ) ? (int) $options['buffer_before'] : 0;
		$buffer_after  = isset( $options['buffer_after'] ) ? (int) $options['buffer_after'] : 0;

		$today = new DateTime( 'now' );

		// Helper to check range
		$check_holiday = function( $month, $day, $range_before, $range_after ) use ( $today, $year ) {
			$target = new DateTime( "$year-$month-$day" );
			$start = clone $target;
			$start->modify( "-$range_before days" );
			$end = clone $target;
			$end->modify( "+$range_after days" );
			return ( $today >= $start && $today <= $end );
		};
		
		// Helper for dynamic dates (Y-m-d strings)
		$check_dynamic_date = function( $date_str, $range_before, $range_after ) use ( $today ) {
			$target = new DateTime( $date_str );
			$start = clone $target;
			$start->modify( "-$range_before days" );
			$end = clone $target;
			$end->modify( "+$range_after days" );
			return ( $today >= $start && $today <= $end );
		};

		// --- Floating Holidays (Now Editable) ---
		$dynamic_holidays = $this->get_dynamic_holiday_dates( $year );

		// Check new Asian/World Holidays
		if ( $check_dynamic_date( $dynamic_holidays['vesak'], $buffer_before, $buffer_after ) ) {
			return 'vesak';
		}
		if ( $check_dynamic_date( $dynamic_holidays['poson'], $buffer_before, $buffer_after ) ) {
			return 'poson';
		}
		if ( $check_dynamic_date( $dynamic_holidays['diwali'], $buffer_before, $buffer_after ) ) {
			return 'diwali';
		}
		if ( $check_dynamic_date( $dynamic_holidays['songkran'], $buffer_before, $buffer_after ) ) {
			return 'summer'; // Songkran is a water festival, "summer" fits well or custom later
		}
		if ( $check_dynamic_date( $dynamic_holidays['chinese_new_year'], $buffer_before, $buffer_after ) ) {
			return 'newyear'; // Re-use Fireworks/Gold or custom 'dragon' later
		}

		// Black Friday
		if ( $check_dynamic_date( $dynamic_holidays['black_friday'], $buffer_before, $buffer_after ) ) {
			return 'black_friday'; 
		}
		
		// Easter
		if ( $check_dynamic_date( $dynamic_holidays['easter'], $buffer_before, $buffer_after ) ) {
			return 'easter';
		}

		// Mother's Day
		if ( $check_dynamic_date( $dynamic_holidays['mothers_day'], $buffer_before, $buffer_after ) ) {
			return 'mothers_day';
		}


		// --- Fixed Holidays (Overridden by Calendar if set, otherwise fallback) ---
		// We can actually use the calendar dates for these too if we want full consistency.
		// For now, let's prioritize the Dynamic Calendar checks above.

		// 1. Halloween Check (using calendar date if we wanted, but let's stick to logic flow)
		// Actually, the user added 'halloween' and 'christmas' to the editable calendar.
		// So we should check those first.

		if ( $check_dynamic_date( $dynamic_holidays['halloween'], $buffer_before, $buffer_after ) ) {
			return 'halloween';
		}

		if ( $check_dynamic_date( $dynamic_holidays['christmas'], $buffer_before, $buffer_after ) ) {
			return 'christmas';
		}
		
		// 2. Valentine's (Feb 14) - Not in user's calendar list yet, keeping hardcoded
		if ( $check_holiday( 2, 14, $buffer_before, $buffer_after ) ) {
			return 'valentines';
		}

		// 3. St. Patrick's (Mar 17)
		if ( $check_holiday( 3, 17, $buffer_before, $buffer_after ) ) {
			return 'patrick';
		}

		// 4. New Year (Jan 1)
		if ( $check_holiday( 1, 1, $buffer_before + 1, $buffer_after ) ) {
			return 'newyear';
		}

		// --- Meteorological Seasons (Hemisphere Aware) ---
		
		$options = get_option( $this->option_name );
		// Check for 'climate_zone' first (New), fall back to 'hemisphere' (Old)
		$climate = isset( $options['climate_zone'] ) ? $options['climate_zone'] : ( isset( $options['hemisphere'] ) ? $options['hemisphere'] : 'northern' );

		// 1. TROPICAL MODE (Sri Lanka / SE Asia)
		if ( 'tropical' === $climate ) {
			// In Tropics: Dec-Feb is usually rainy or nice (Monsoon), not Winter.
			if ( $month == 12 || $month == 1 || $month == 2 ) {
				return 'monsoon'; // Triggers Rain Effect
			} elseif ( $month >= 5 && $month <= 9 ) {
				return 'summer'; // Yala Season (Hot)
			} else {
				return 'summer'; // Default to Sunny
			}
		}
		// 2. SOUTHERN HEMISPHERE (Australia)
		elseif ( 'southern' === $climate ) {
			if ( $month >= 9 && $month <= 11 ) {
				return 'spring';
			} elseif ( $month >= 3 && $month <= 5 ) {
				return 'autumn';
			} elseif ( $month >= 6 && $month <= 8 ) {
				return 'winter';
			} else {
				return 'summer'; // Dec/Jan is Summer
			}
		} 
		// 3. NORTHERN HEMISPHERE (US/UK/Europe)
		else { 
			if ( $month >= 3 && $month <= 5 ) {
				return 'spring';
			} elseif ( $month >= 6 && $month <= 8 ) {
				return 'summer';
			} elseif ( $month >= 9 && $month <= 11 ) {
				return 'autumn';
			} else {
				return 'winter';
			}
		}
	}


	private function get_dynamic_holiday_dates( $year ) {
		$options = get_option( $this->option_name );

		// Helper to get date safely
		$get_date = function($key, $default) use ($options) {
			return isset($options['date_' . $key]) && !empty($options['date_' . $key]) 
				? $options['date_' . $key] 
				: $default;
		};

		return array(
			// The User's "Editable" Dates (2026 Defaults provided as fallback)
			'chinese_new_year' => $get_date('chinese_new_year', "$year-02-17"),
			'vesak'            => $get_date('vesak',            "$year-05-01"),
			'poson'            => $get_date('poson',            "$year-06-29"),
			'songkran'         => $get_date('songkran',         "$year-04-13"),
			'diwali'           => $get_date('diwali',           "$year-11-08"),
			'halloween'        => $get_date('halloween',        "$year-10-31"),
			'christmas'        => $get_date('christmas',        "$year-12-25"),
			
			// Standard Calculated ones (if user didn't override, or handled differently)
			// Black Friday defaults to 4th Friday Nov if not set by user
			'black_friday'     => $get_date('black_friday',     date( 'Y-m-d', strtotime( "fourth friday of november $year" ) )),
			
			// Easter (PHP still calculates this best, user not editing it right now but could add later)
			'easter'           => date( 'Y-m-d', easter_date( $year ) ),
			
			// Mother's Day
			'mothers_day'  => date( 'Y-m-d', strtotime( "second sunday of may $year" ) ),
		);
	}

	/**
	 * Check if effects should be shown.
	 */
	public function should_show_effects() {
		// 1. Check Crash Protection / Safe Mode
		if ( isset( $_GET['seasonal_safe_mode'] ) ) {
			return false;
		}

		$options = get_option( $this->option_name );

		// 1.5 Master Force Stop
		if ( isset( $options['master_disable'] ) && '1' === $options['master_disable'] ) {
			return false;
		}

		// 2. Mobile Check
		if ( isset( $options['hide_mobile'] ) && '1' === $options['hide_mobile'] && wp_is_mobile() ) {
			return false;
		}

		// 3. Homepage Only Check
		if ( isset( $options['only_homepage'] ) && '1' === $options['only_homepage'] ) {
			if ( ! is_front_page() && ! is_home() ) {
				return false;
			}
		}

		// 4. WooCommerce Exclusions (Cart/Checkout)
		if ( isset( $options['exclude_woo'] ) && '1' === $options['exclude_woo'] ) {
			if ( function_exists( 'is_cart' ) && is_cart() ) {
				return false;
			}
			if ( function_exists( 'is_checkout' ) && is_checkout() ) {
				return false;
			}
		}

		// 5. Specific Page ID Exclusion
		if ( isset( $options['exclude_ids'] ) && ! empty( $options['exclude_ids'] ) ) {
			$current_id = get_the_ID();
			$excluded   = array_map( 'trim', explode( ',', $options['exclude_ids'] ) );
			if ( in_array( (string) $current_id, $excluded, true ) ) {
				return false;
			}
		}

		// 6. Preview Mode Logic (Existing)
		// Check Admin Preview Mode
		$is_preview_mode = isset( $options['preview_mode'] ) ? $options['preview_mode'] : '1'; // Default on

		// Allow override via URL constant
		if ( isset( $_GET['seasonal_preview'] ) ) {
			return true;
		}

		// If Live, show to everyone
		if ( '0' === $is_preview_mode ) {
			return true;
		}

		// If Preview Mode, show only to admins
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return false;
	}

	public function enqueue_assets() {
		if ( ! $this->should_show_effects() ) {
			return;
		}

		$season = $this->get_current_season();

		wp_enqueue_style(
			'seasonal-animation-style',
			SEASONAL_ANIMATION_URL . 'assets/css/seasonal-style.css',
			array(),
			SEASONAL_ANIMATION_VERSION
		);

		wp_enqueue_script(
			'seasonal-animation-script',
			SEASONAL_ANIMATION_URL . 'assets/js/seasonal-effects.js',
			array(), // Removed jquery dependency to be lightweight, will use Vanilla JS
			SEASONAL_ANIMATION_VERSION,
			true
		);

		// Pass variables to JS
		$options = get_option( $this->option_name );

		$final_type  = isset( $options['particle_type'] ) ? $options['particle_type'] : 'default';
		$final_value = isset( $options['custom_particle_value'] ) ? $options['custom_particle_value'] : '';

		// Scheduler Logic
		$start_date = isset( $options['schedule_start'] ) ? $options['schedule_start'] : '';
		$end_date   = isset( $options['schedule_end'] ) ? $options['schedule_end'] : '';
		$recur      = isset( $options['schedule_recur'] ) ? $options['schedule_recur'] : '0';

		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			
			if ( '1' === $recur ) {
				// Annual Recurrence (Ignore Year)
				// Format: m-d (e.g. 12-25)
				$start_md = date( 'm-d', strtotime( $start_date ) );
				$end_md   = date( 'm-d', strtotime( $end_date ) );
				$today_md = date( 'm-d' );

				$is_active = false;

				if ( $start_md <= $end_md ) {
					// Normal range (e.g. May 1 to May 5)
					if ( $today_md >= $start_md && $today_md <= $end_md ) {
						$is_active = true;
					}
				} else {
					// Wrapping range (e.g. Dec 25 to Jan 05)
					if ( $today_md >= $start_md || $today_md <= $end_md ) {
						$is_active = true;
					}
				}

				if ( $is_active ) {
					$final_type  = isset( $options['schedule_type'] ) ? $options['schedule_type'] : 'emoji';
					$final_value = isset( $options['schedule_value'] ) ? $options['schedule_value'] : '';
				}

			} else {
				// Specific Year Only (Strict)
				$today = date( 'Y-m-d' );
				if ( $today >= $start_date && $today <= $end_date ) {
					$final_type  = isset( $options['schedule_type'] ) ? $options['schedule_type'] : 'emoji';
					$final_value = isset( $options['schedule_value'] ) ? $options['schedule_value'] : '';
				}
			}
		}

		wp_localize_script(
			'seasonal-animation-script',
			'seasonalAnimationSettings',
			array(
				'season'         => $season,
				'particle_type'  => $final_type,
				'particle_value' => $final_value,
				'enable_fog'       => isset( $options['enable_fog'] ) ? $options['enable_fog'] : '0', // Default Off
				'frequency'        => isset( $options['particle_frequency'] ) ? $options['particle_frequency'] : '30',
				'interaction_mode' => isset( $options['interaction_mode'] ) ? $options['interaction_mode'] : 'none',
				'z_index'          => isset( $options['z_index'] ) ? $options['z_index'] : '9999',
				'custom_speed'     => isset( $options['animation_speed'] ) ? $options['animation_speed'] : '',
				'emoji_map'        => array(
					'winter'    => isset( $options['emoji_winter'] ) ? $options['emoji_winter'] : '',
					'summer'    => isset( $options['emoji_summer'] ) ? $options['emoji_summer'] : '',
					'autumn'    => isset( $options['emoji_autumn'] ) ? $options['emoji_autumn'] : '',
					'spring'    => isset( $options['emoji_spring'] ) ? $options['emoji_spring'] : '',
					'monsoon'   => isset( $options['emoji_monsoon'] ) ? $options['emoji_monsoon'] : '',
					'halloween' => isset( $options['emoji_halloween'] ) ? $options['emoji_halloween'] : '',
					'christmas' => isset( $options['emoji_christmas'] ) ? $options['emoji_christmas'] : '',
					'valentines'=> isset( $options['emoji_valentines'] ) ? $options['emoji_valentines'] : '',
				),
			)
		);
	}
}
