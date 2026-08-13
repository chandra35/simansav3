<style>
    .simansa-dropdown-portal {
        position: fixed !important;
        z-index: 2050 !important;
        margin: 0 !important;
        transform: none !important;
        max-height: min(70vh, 520px);
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        box-shadow: 0 12px 32px rgba(15, 23, 42, .2);
    }
</style>
<script>
    (function ($) {
        'use strict';

        if (!$ || window.SimansaTableDropdownPortal) {
            return;
        }

        const portal = {
            host: null,
            menu: null,
            toggle: null,
            marker: null,
        };

        function isTableDropdown(host) {
            return host.closest('table').length > 0 && host.find('> .dropdown-menu').length > 0;
        }

        function placeMenu() {
            if (!portal.menu || !portal.toggle || !portal.menu.hasClass('show')) {
                return;
            }

            const buttonRect = portal.toggle[0].getBoundingClientRect();
            const menuWidth = portal.menu.outerWidth();
            const menuHeight = portal.menu.outerHeight();
            const gutter = 8;
            const viewportWidth = document.documentElement.clientWidth;
            const viewportHeight = document.documentElement.clientHeight;
            const spaceBelow = viewportHeight - buttonRect.bottom - gutter;
            const spaceAbove = buttonRect.top - gutter;
            const openAbove = spaceBelow < Math.min(menuHeight, 240) && spaceAbove > spaceBelow;

            let left = portal.menu.hasClass('dropdown-menu-right')
                ? buttonRect.right - menuWidth
                : buttonRect.left;
            left = Math.max(gutter, Math.min(left, viewportWidth - menuWidth - gutter));

            let top = openAbove
                ? buttonRect.top - menuHeight - 4
                : buttonRect.bottom + 4;
            top = Math.max(gutter, Math.min(top, viewportHeight - menuHeight - gutter));

            portal.menu.css({ top: `${top}px`, left: `${left}px`, right: 'auto', bottom: 'auto' });
        }

        function restoreMenu() {
            if (!portal.menu) {
                return;
            }

            if (portal.marker && portal.marker.parent().length) {
                portal.marker.after(portal.menu);
                portal.marker.remove();
            }

            portal.menu.removeClass('simansa-dropdown-portal').css({
                top: '', left: '', right: '', bottom: '', position: '', transform: '', minWidth: '',
            });
            portal.host = portal.menu = portal.toggle = portal.marker = null;
        }

        $(document).on('shown.bs.dropdown.simansaTablePortal', '.dropdown, .btn-group', function () {
            const host = $(this);
            if (!isTableDropdown(host)) {
                return;
            }

            restoreMenu();
            const menu = host.find('> .dropdown-menu').first();
            const toggle = host.find('> [data-toggle="dropdown"]').first();
            if (!menu.length || !toggle.length) {
                return;
            }

            const dropdown = toggle.data('bs.dropdown');
            if (dropdown && dropdown._popper) {
                dropdown._popper.destroy();
                dropdown._popper = null;
            }

            const marker = $('<span class="simansa-dropdown-portal-marker" hidden></span>');
            const originalMenuWidth = menu.outerWidth();
            menu.before(marker);
            menu.appendTo(document.body)
                .addClass('simansa-dropdown-portal')
                .css('min-width', `${Math.max(originalMenuWidth, 180)}px`);

            portal.host = host;
            portal.menu = menu;
            portal.toggle = toggle;
            portal.marker = marker;
            placeMenu();
        });

        $(document).on('hide.bs.dropdown.simansaTablePortal', '.dropdown, .btn-group', function () {
            if (portal.host && portal.host[0] === this) {
                restoreMenu();
            }
        });

        $(document).on('preDraw.dt.simansaTablePortal', function () {
            if (portal.host) {
                portal.toggle.dropdown('hide');
                restoreMenu();
            }
        });

        $(window).on('resize.simansaTablePortal scroll.simansaTablePortal', function () {
            placeMenu();
        });

        window.SimansaTableDropdownPortal = { restore: restoreMenu, reposition: placeMenu };
    })(window.jQuery);
</script>
