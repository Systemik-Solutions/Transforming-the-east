document.addEventListener('DOMContentLoaded', function () {
    const featuredCheckbox = document.getElementById('markAsFeatured');

    if (featuredCheckbox) {
        featuredCheckbox.addEventListener('change', async function () {
            const checkbox = this;
            const entityID = checkbox.dataset.entityId;
            const shouldAdd = checkbox.checked;

            if (!entityID) {
                window.alert('Missing entity id.');
                checkbox.checked = !shouldAdd;
                return;
            }

            if (!window.tteTheme?.ajaxUrl || !window.tteTheme?.featuredNonce) {
                console.error('TTE featured-record configuration is unavailable.');
                checkbox.checked = !shouldAdd;
                return;
            }

            const formData = new URLSearchParams({
                action: 'tte_update_featured_record',
                nonce: window.tteTheme.featuredNonce,
                action_type: shouldAdd ? 'add' : 'remove',
                entity_id: entityID,
            });

            checkbox.disabled = true;

            try {
                const response = await fetch(window.tteTheme.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: formData.toString(),
                });
                const responseText = await response.text();
                let payload = null;

                try {
                    payload = JSON.parse(responseText);
                } catch (error) {
                    // The response status and fallback message below handle invalid JSON.
                }

                if (!response.ok || payload?.success === false) {
                    throw new Error(
                        payload?.data?.message ||
                        payload?.message ||
                        'Request failed.',
                    );
                }
            } catch (error) {
                checkbox.checked = !shouldAdd;
                console.error('Could not update the featured record.', error);
                window.alert(
                    error.message ||
                    'Unexpected error occurred. Please refresh the page and try again.',
                );
            } finally {
                checkbox.disabled = false;
            }
        });
    }

    document.querySelectorAll('.tte-project .nav-tabs').forEach(function (tabList) {
        tabList.addEventListener('click', function (event) {
            const link = event.target.closest('a.nav-link[href^="#"]');
            if (!link) {
                return;
            }

            const panel = document.querySelector(link.getAttribute('href'));
            if (!panel) {
                return;
            }

            event.preventDefault();

            tabList.querySelectorAll('.nav-link').forEach(function (item) {
                item.classList.remove('active');
                item.setAttribute('aria-selected', 'false');
            });

            const tabContent = panel.closest('.tab-content');
            if (tabContent) {
                tabContent.querySelectorAll('.tab-pane').forEach(function (item) {
                    item.classList.remove('active', 'show');
                });
            }

            link.classList.add('active');
            link.setAttribute('aria-selected', 'true');
            panel.classList.add('active', 'show');

            // Preserve the Bootstrap tab event used by legacy plugin views.
            // These tabs are now controlled here rather than by Bootstrap JS.
            jQuery(link).trigger('shown.bs.tab');
        });
    });

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.tte-project .dropdown-toggle');
        const pluginRoot = event.target.closest('.tte-project');

        if (toggle) {
            event.preventDefault();
            const menuId = toggle.getAttribute('id');
            if (!menuId || !pluginRoot) {
                return;
            }

            const menu = pluginRoot.querySelector('.dropdown-menu[aria-labelledby="' + menuId + '"]');
            if (!menu) {
                return;
            }

            const isOpen = toggle.getAttribute('aria-expanded') === 'true';
            pluginRoot.querySelectorAll('.dropdown-toggle').forEach(function (item) {
                item.setAttribute('aria-expanded', 'false');
            });
            pluginRoot.querySelectorAll('.dropdown-menu').forEach(function (item) {
                item.style.display = 'none';
            });

            toggle.setAttribute('aria-expanded', String(!isOpen));
            menu.style.display = isOpen ? 'none' : 'block';
            return;
        }

        document.querySelectorAll('.tte-project .dropdown-toggle').forEach(function (item) {
            item.setAttribute('aria-expanded', 'false');
        });
        document.querySelectorAll('.tte-project .dropdown-menu').forEach(function (item) {
            item.style.display = 'none';
        });
    });
});
