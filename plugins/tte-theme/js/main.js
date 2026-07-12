document.addEventListener('DOMContentLoaded', function () {
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
