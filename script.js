// Mobile nav toggle
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    // Confirm before delete/dangerous actions
    document.querySelectorAll('.confirm-action').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // Package tab selector on gig detail page
    var tabs = document.querySelectorAll('.package-tab');
    if (tabs.length) {
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('selected'); });
                tab.classList.add('selected');
                var packageId = tab.getAttribute('data-package-id');
                var input = document.getElementById('selected_package_id');
                if (input) input.value = packageId;

                var priceEl = document.getElementById('order-total-price');
                if (priceEl) priceEl.textContent = tab.getAttribute('data-price-display');
            });
        });
    }
});
