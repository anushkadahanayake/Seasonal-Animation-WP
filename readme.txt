=== Seasonal Animation WP ===
Contributors: anushkadahanayake
Tags: seasonal, animation, snow, christmas, effects, weather, tropical
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later

Add lightweight, crash-protected seasonal effects (Snow, Rain, Leaves) to your website. Includes automatic scheduling and Tropical/Monsoon mode.

== Description ==

Bring your website to life with automatic seasonal animations. Unlike other heavy plugins, **Seasonal Animation WP** is built for performance first. It uses lightweight CSS animations (GPU accelerated) and includes a "Crash Protection" kill-switch.

**✨ Key Features**
* **Automatic Scheduling:** Detects Christmas, Halloween, Valentine's Day, and more automatically.
* **🌍 Tropical Mode:** Perfect for international sites! Switches "Winter Snow" to "Monsoon Rain" for tropical regions (like Sri Lanka, Singapore, Brazil).
* **🚀 Performance First:** Zero bloat. Animations stop automatically on mobile devices (optional) and respect "Reduced Motion" user settings.
* **🛡️ Crash Protection:** Site acting up? Add `?seasonal_safe_mode=1` to your URL to instantly disable the plugin without logging in.

**Effects Included:**
* ❄️ **Winter:** Soft falling snow with optional fog.
* 🍂 **Autumn:** Drifting orange leaves.
* 🌸 **Spring:** Floating pink petals.
* 💧 **Monsoon:** Fast-falling rain (Tropical Mode).
* 🎃 **Halloween:** Spooky atmosphere with bats/pumpkins.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/seasonal-animation` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Settings > Seasonal Animation** to configure your preferences.

== Frequently Asked Questions ==

= Does this slow down my site? =
No. We use `translate3d` CSS animations which run on the GPU (Graphics Card), not the CPU. This means your site logic remains fast.

= How do I turn it off if I can't access Admin? =
If you ever get stuck, simply visit `yoursite.com/?seasonal_safe_mode=1` to disable the plugin logic temporarily.

== Screenshots ==

1. **Admin Settings** - Easy configuration with "Tropical Mode" and Scheduling.
2. **Winter Effect** - Snow with optional fog atmosphere.
3. **Monsoon Effect** - Rain simulation for tropical climates.

== Changelog ==

= 1.0.0 =
* Initial release.
* Added "Tropical Mode" for island nations.
* Added "Safe Mode" kill-switch.
