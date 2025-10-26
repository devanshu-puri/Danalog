(() => {
    const MOBILE_REGEX = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;

    const isMobileDevice = () => {
        const userAgent = navigator.userAgent || '';
        return MOBILE_REGEX.test(userAgent);
    };

    const updateLinks = () => {
        const links = document.querySelectorAll('[data-danalog-whatsapp-link]');

        if (!links.length) {
            return;
        }

        const isMobile = isMobileDevice();

        links.forEach((link) => {
            const mobileUrl = link.getAttribute('data-mobile-url');
            const desktopUrl = link.getAttribute('data-desktop-url');
            const targetUrl = isMobile ? mobileUrl : desktopUrl;

            if (targetUrl) {
                link.setAttribute('href', targetUrl);
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateLinks);
    } else {
        updateLinks();
    }

    document.addEventListener('woocommerce_variation_has_changed', updateLinks);
    window.addEventListener('pageshow', updateLinks);
})();
