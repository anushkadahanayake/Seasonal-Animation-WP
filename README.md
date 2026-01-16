# Seasonal Animation WordPress Plugin

A lightweight, crash-protected WordPress plugin that adds seasonal effects (Snow, Leaves, Flowers, etc.) to your site.

## 🚀 Quick Start (Installation)

Since this is a **WordPress Plugin**, you cannot run it with `npm start`. It must be installed on a WordPress site.

1. **Zip the Folder**: Compress the entire `Seasonal Animation WP` folder into a `.zip` file.
2. **Upload to WordPress**:
   - Go to your WordPress Dashboard.
   - Navigate to **Plugins** > **Add New**.
   - Click **Upload Plugin**.
   - Select your `.zip` file and click **Install Now**.
3. **Activate**: Click **Activate Plugin** after installation.

## ⚙️ Configuration

1. Go to **Settings** > **Seasonal Animation**.
2. **Season Mode**: 
   - Choose `Automatic` to let the plugin decide based on the date.
   - Choose a specific season to force an effect (e.g., `Force Winter`).
3. **Status**:
   - `Preview Mode` (Default): Effects are only visible to **Admins**.
   - `Live`: Effects are visible to **everyone**.
   
> **Note:** If you use a caching plugin (like WP Rocket, Autoptimize, or Cloudflare), please **clear your cache** after saving changes to ensure visitors see the new effects immediately.

## 🛡️ Crash Protection / Safe Mode

If the plugin ever causes your site to break, use the **Kill Switch** to disable it instantly without logging in.

- Add `?seasonal_safe_mode=1` to any URL on your site.
- Example: `https://your-site.com/?seasonal_safe_mode=1`

## 🔎 Previewing Without Logging In

To test the effect in Incognito mode (as a visitor) without going Live:

- Add `?seasonal_preview=1` to your URL.
- Example: `https://your-site.com/?seasonal_preview=1`
