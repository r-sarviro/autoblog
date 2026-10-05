(function () {
    var root = document.documentElement;
    var storageKey = 'theme';

    function resolveTheme(value) {
        if (value === 'light' || value === 'dark') {
            return value;
        }

        return 'dark';
    }

    function getStoredTheme() {
        try {
            return localStorage.getItem(storageKey);
        } catch (e) {
            return null;
        }
    }

    function setStoredTheme(theme) {
        try {
            localStorage.setItem(storageKey, theme);
        } catch (e) {
            // Ignore private mode / blocked storage.
        }
    }

    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        updateToggle(theme);
    }

    function syncHeaderHeight() {
        var header = document.querySelector('.site-header');
        if (!header) {
            return;
        }

        root.style.setProperty('--header-height', header.getBoundingClientRect().height + 'px');
    }

    function updateToggle(theme) {
        var button = document.querySelector('[data-theme-toggle]');
        if (!button) {
            return;
        }

        var isDark = theme === 'dark';
        var toLight = document.body.getAttribute('data-theme-to-light') || 'Switch to light theme';
        var toDark = document.body.getAttribute('data-theme-to-dark') || 'Switch to dark theme';
        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.setAttribute('aria-label', isDark ? toLight : toDark);
        button.setAttribute('title', isDark ? toLight : toDark);
    }

    function initThemeToggle() {
        var current = resolveTheme(root.getAttribute('data-theme') || getStoredTheme());
        applyTheme(current);

        var button = document.querySelector('[data-theme-toggle]');
        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            setStoredTheme(next);
            applyTheme(next);
        });
    }

    function initNavToggle() {
        var toggle = document.querySelector('[data-nav-toggle]');
        var nav = document.querySelector('[data-nav]');
        if (!toggle || !nav) {
            return;
        }

        function setOpen(isOpen) {
            document.body.classList.toggle('nav-open', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            var openLabel = toggle.getAttribute('data-label-open') || 'Open menu';
            var closeLabel = toggle.getAttribute('data-label-close') || 'Close menu';
            toggle.setAttribute('aria-label', isOpen ? closeLabel : openLabel);
        }

        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            setOpen(!document.body.classList.contains('nav-open'));
        });

        nav.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        nav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                setOpen(false);
            });
        });

        document.addEventListener('click', function () {
            if (document.body.classList.contains('nav-open')) {
                setOpen(false);
            }
        });

        window.addEventListener('resize', function () {
            if (window.matchMedia('(min-width: 901px)').matches) {
                setOpen(false);
            }
        });
    }

    function initHeroImage() {
        document.querySelectorAll('[data-hero-image]').forEach(function (img) {
            var markLoaded = function () {
                img.classList.add('is-loaded');
            };

            if (img.complete && img.naturalWidth > 0) {
                markLoaded();
                return;
            }

            img.addEventListener('load', markLoaded);
            img.addEventListener('error', markLoaded);
        });
    }

    function initToTop() {
        var button = document.querySelector('[data-to-top]');
        if (!button) {
            return;
        }

        var toggle = function () {
            var show = window.scrollY > 320;
            button.hidden = !show;
            button.classList.toggle('is-visible', show);
        };

        button.addEventListener('click', function () {
            var reduce = false;
            try {
                reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            } catch (e) {}
            window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
        });

        window.addEventListener('scroll', toggle, { passive: true });
        toggle();
    }

    function init() {
        syncHeaderHeight();
        initThemeToggle();
        initNavToggle();
        initHeroImage();
        initToTop();
        window.addEventListener('resize', syncHeaderHeight);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
