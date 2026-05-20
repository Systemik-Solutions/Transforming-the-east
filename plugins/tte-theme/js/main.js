jQuery( document ).ready(function($) {
    // Create carousels.
    $('.omaa-carousel').each(function () {
        const carousel = new Carousel($(this));
    });

    // Solve the scroll issue with the default tab handler.
    $('.nav-tabs a.nav-link').off('click').on('click', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        $(this).tab('show');
    });
});


document.addEventListener('DOMContentLoaded', function () {
  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('.dropdown-toggle');

    // If clicking a dropdown toggle
    if (toggle) {
      e.preventDefault();

      const menuId = toggle.getAttribute('id');
      if (!menuId) return;

      const menu = document.querySelector(
        `.dropdown-menu[aria-labelledby="${menuId}"]`
      );
      if (!menu) return;

      const isOpen = toggle.getAttribute('aria-expanded') === 'true';

      // Close all other dropdowns
      document.querySelectorAll('.dropdown-toggle').forEach(t => {
        t.setAttribute('aria-expanded', 'false');
      });
      document.querySelectorAll('.dropdown-menu').forEach(m => {
        m.style.display = 'none';
      });

      // Toggle current
      toggle.setAttribute('aria-expanded', String(!isOpen));
      menu.style.display = isOpen ? 'none' : 'block';
      return;
    }

    // Click outside → close all
    document.querySelectorAll('.dropdown-toggle').forEach(t => {
      t.setAttribute('aria-expanded', 'false');
    });
    document.querySelectorAll('.dropdown-menu').forEach(m => {
      m.style.display = 'none';
    });
  });
});
