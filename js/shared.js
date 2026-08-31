function escapeHtml(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function resizeIframe(obj) {
    function doResize() {
        try {
            var h = obj.contentWindow.document.documentElement.scrollHeight;
            if (h && h > 0) {
                obj.style.height = h + 'px';
            }
        } catch (e) {}
    }
    doResize();
    try {
        var imgs = obj.contentWindow.document.images;
        for (var i = 0; i < imgs.length; i++) {
            if (!imgs[i].complete) {
                imgs[i].addEventListener('load', doResize);
                imgs[i].addEventListener('error', doResize);
            }
        }
    } catch (e) {}
}

function switchLanguage(lang) {
    if (typeof setLanguage === 'function') {
        setLanguage(lang);
    }
    document.querySelectorAll('.lang-option').forEach(function(el) {
        el.classList.remove('active');
    });
    var active = document.querySelector('[data-lang="' + lang + '"]');
    if (active) active.classList.add('active');
    var langText = lang === 'la' ? 'ລາວ' : '中文';
    var el = document.getElementById('currentLangText');
    if (el) el.textContent = langText;
    localStorage.setItem('site_lang', lang);
}

document.addEventListener('DOMContentLoaded', function() {
    var wrapperEl = document.getElementById('wrapper');
    var sidebarBackdrop = document.getElementById('sidebarBackdrop');
    var sidebarToggleBtn = document.getElementById('sidebarToggle');

    if (wrapperEl && sidebarBackdrop && sidebarToggleBtn) {
        function isDesktop() {
            return window.innerWidth >= 992;
        }

        function closeSidebar() {
            wrapperEl.classList.remove('sidebar-open');
            sidebarBackdrop.classList.remove('show');
        }

        sidebarToggleBtn.addEventListener('click', function() {
            if (isDesktop()) {
                wrapperEl.classList.toggle('sidebar-hidden-desktop');
            } else {
                var isOpen = wrapperEl.classList.toggle('sidebar-open');
                sidebarBackdrop.classList.toggle('show', isOpen);
            }
        });

        sidebarBackdrop.addEventListener('click', closeSidebar);

        document.querySelectorAll('#sidebar-wrapper .list-group-item').forEach(function(item) {
            item.addEventListener('click', function() {
                if (!isDesktop()) {
                    closeSidebar();
                }
            });
        });

        window.addEventListener('resize', function() {
            if (isDesktop()) {
                closeSidebar();
            } else {
                wrapperEl.classList.remove('sidebar-hidden-desktop');
            }
        });
    }

    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        $('.cust_id').select2({
            placeholder: "ເລືອກລາຍການ",
            allowClear: true,
            width: '100%'
        });
    }

    var currentLang = localStorage.getItem('site_lang') || 'la';
    switchLanguage(currentLang);
});
