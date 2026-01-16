document.addEventListener('DOMContentLoaded', function () {
    try {
        // 1. Check for reduced motion preference
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            console.log('Seasonal Animation: Reduced motion preferred. Effects disabled.');
            return;
        }

        const config = seasonalAnimationSettings; // Passed from PHP
        if (!config || !config.season) return;

        const container = document.createElement('div');
        container.id = 'seasonal-animation-container';

        // Apply Interaction & Positioning Settings
        container.style.zIndex = config.z_index || '9999';
        container.style.pointerEvents = config.interaction_mode || 'none';

        document.body.appendChild(container);

        console.log('Seasonal Animation Active: ' + config.season);

        // Default configuration
        let char = '❄';
        let color = 'white';
        let type = 'emoji';

        // 1. CHECK FOR DASHBOARD OVERRIDES
        // If the user set a custom emoji for this season in the settings, use it.
        const seasonKey = config.season;
        const customEmoji = config.emoji_map && config.emoji_map[seasonKey] ? config.emoji_map[seasonKey] : null;

        if (customEmoji) {
            char = customEmoji;
            color = 'inherit'; // Allow emoji color to show
            // We still need to set color/effects for specific seasons if they rely on it (like Summer overlay), 
            // but let's assume if they override emoji they want the emoji to be the main thing.
            // However, for Rain, we might need adjustments. Let's keep specific season logic for Fog/Type but override Char.
        }

        // 2. Determine the content logic (Standard Logic but checking override)
        if (config.particle_type === 'image' && config.particle_value) {
            type = 'image';
            char = config.particle_value;
        } else if (config.particle_type === 'emoji' && config.particle_value) {
            type = 'emoji';
            char = config.particle_value;
            color = 'inherit';
        } else {
            // Fallback to Seasons defaults
            if (config.season === 'winter') {
                if (!customEmoji) char = '❄';
                color = 'white';
                addFog(container, '255, 255, 255');
            } else if (config.season === 'autumn') {
                if (!customEmoji) char = '🍂';
                color = 'orange';
                addFog(container, '210, 105, 30');
            } else if (config.season === 'spring') {
                if (!customEmoji) char = '🌸';
                color = 'pink';
                addFog(container, '255, 182, 193');
            } else if (config.season === 'summer') {
                const overlay = document.createElement('div');
                overlay.className = 'season-summer-overlay';
                document.body.appendChild(overlay);
                if (!customEmoji) char = '✨';
                color = 'yellow';
                addFog(container, '255, 223, 0');
            } else if (config.season === 'halloween') {
                if (!customEmoji) char = '🎃';
                color = 'orange';
                addFog(container, '148, 0, 211');
            } else if (config.season === 'christmas') {
                if (!customEmoji) char = '🎅';
                color = 'red';
                addFog(container, '220, 220, 255');
            } else if (config.season === 'valentines') {
                if (!customEmoji) char = '❤️';
                color = 'red';
                addFog(container, '255, 105, 180');
            } else if (config.season === 'patrick') {
                if (!customEmoji) char = '🍀';
                color = 'green';
                addFog(container, '50, 205, 50');
            } else if (config.season === 'vesak') {
                if (!customEmoji) char = '☸️';
                color = 'gold';
                addFog(container, '255, 215, 0');
            } else if (config.season === 'poson') {
                if (!customEmoji) char = '🪷';
                color = 'white';
                addFog(container, '255, 255, 255');
            } else if (config.season === 'diwali') {
                if (!customEmoji) char = '🪔';
                color = 'orange';
                addFog(container, '255, 165, 0');
            } else if (config.season === 'easter') {
                if (!customEmoji) char = '🥚';
                color = 'violet';
                addFog(container, '238, 130, 238');
            } else if (config.season === 'black_friday') {
                if (!customEmoji) char = '🛍️';
                color = 'black';
            } else if (config.season === 'mothers_day') {
                if (!customEmoji) char = '💐';
                color = 'pink';
            } else if (config.season === 'newyear') {
                if (!customEmoji) char = '🎉';
                color = 'gold';
                addFog(container, '255, 215, 0');
            } else if (config.season === 'monsoon') {
                if (!customEmoji) {
                    char = '💧';
                    color = '#4fa3e0';
                } else {
                    color = 'inherit';
                }
                type = 'emoji';
                // Speed up animation for rain logic is handled nicely below now
                config.speed = 'fast';
            }
        }

        function addFog(container, rgbString) {
            // 0. Check User Preference
            if (config.enable_fog !== '1') {
                return;
            }

            // Default to white if no color provided
            if (!rgbString) rgbString = '255, 255, 255';

            // Prevent duplicate fog if already added
            if (container.querySelector('.seasonal-fog-layer')) return;

            const fog1 = document.createElement('div');
            fog1.className = 'seasonal-fog-layer';
            fog1.style.setProperty('--fog-color', rgbString);

            const fog2 = document.createElement('div');
            fog2.className = 'seasonal-fog-layer layer-2';
            fog2.style.setProperty('--fog-color', rgbString);

            container.appendChild(fog1);
            container.appendChild(fog2);
        }

        startParticleEffect(container, char, color, type, config.frequency || 30);

        function startParticleEffect(container, char, color, type, count = 30) {
            // Safety: Cap particle count to prevent browser crash
            let particleCount = parseInt(count);
            if (isNaN(particleCount)) particleCount = 30;
            if (particleCount > 150) particleCount = 150; // HARD LIMIT

            const fragment = document.createDocumentFragment();
            for (let i = 0; i < particleCount; i++) {
                createParticle(fragment, char, color, type, config.speed);
            }
            container.appendChild(fragment);
        }

        function createParticle(parent, content, color, type, speed) {
            let span;

            if (type === 'image') {
                span = document.createElement('img');
                span.src = content;
                span.className = 'seasonal-particle seasonal-particle-img';
            } else {
                span = document.createElement('span');
                span.textContent = content;
                span.className = 'seasonal-particle';
                span.style.color = color;
            }

            // Randomize Appearance
            const sizeVal = Math.random() * 20 + 10;
            const size = sizeVal + 'px';
            const startLeft = Math.random() * 100 + 'vw';
            const delay = Math.random() * -10 + 's';

            // --- NEW SPEED LOGIC ---
            let duration;

            // 1. Did the user set a specific speed in Dashboard?
            if (config.custom_speed && config.custom_speed !== '') {
                // Add a tiny bit of randomness (±10%)
                const base = parseFloat(config.custom_speed);
                // Safety: Minimum speed limit (0.5s) to prevent rapid flashing epilepsy risk
                const safeBase = base < 0.5 ? 0.5 : base;

                const randomVar = (Math.random() * safeBase * 0.2) - (safeBase * 0.1);
                duration = (safeBase + randomVar).toFixed(2) + 's';
            }
            // 2. Is it Rain Mode? (If user didn't set speed)
            else if (config.season === 'monsoon') {
                duration = (Math.random() * 0.5 + 1.5) + 's'; // Default fast rain
            }
            // 3. Default Snow/Leaves (Slow)
            else {
                duration = (Math.random() * 5 + 5) + 's'; // 5s - 10s
            }

            if (type === 'image') {
                span.style.width = size;
                span.style.height = 'auto';
            } else {
                span.style.fontSize = size;
            }

            // Apply rain styling (shape) but allow speed override
            if (config.season === 'monsoon' && !customEmoji) {
                span.classList.add('rain-mode');
                // Important: We need to override the CSS !important if we have a custom speed
                if (config.custom_speed) {
                    span.style.setProperty('animation-duration', duration, 'important');
                } else {
                    // Let CSS handle it or default logic
                    span.style.animationDuration = duration;
                }
            } else {
                span.style.animationDuration = duration;
            }

            span.style.left = startLeft;
            span.style.animationDelay = delay;

            parent.appendChild(span);
        }
    } catch (e) {
        // Safety: If animation fails, log valid error but do not break site
        console.warn('Seasonal Animation Error:', e);
    }
});
