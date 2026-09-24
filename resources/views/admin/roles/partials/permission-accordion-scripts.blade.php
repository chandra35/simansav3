<script>
    function updatePermissionAccordion() {
        $('.simansa-role-permission-row').each(function () {
            const checked = $(this).find('.permission-checkbox').is(':checked');
            $(this).toggleClass('is-checked', checked);
        });

        $('[data-permission-group-count]').each(function () {
            const group = $(this).data('permission-group-count');
            const boxes = $('.permission-' + group);
            $(this).text(boxes.filter(':checked').length + '/' + boxes.length + ' aktif');
        });

        $('[data-active-permission-count]').text($('.permission-checkbox:checked').length);
        $('[data-active-module-count]').text($('.simansa-role-permission-module').filter(function () {
            return $(this).find('.permission-checkbox:checked').length > 0;
        }).length);
    }

    function filterPermissionModules(term) {
        const query = (term || '').toLowerCase().trim();
        let visible = 0;
        $('.simansa-role-permission-module').each(function () {
            const match = !query || String($(this).data('permission-module')).includes(query);
            $(this).toggleClass('is-filtered-out', !match);
            if (match) visible++;
        });
        $('[data-visible-module-count]').text(visible);
    }

    function checkAll() {
        $('.permission-checkbox').prop('checked', true);
        updatePermissionAccordion();
    }

    function uncheckAll() {
        $('.permission-checkbox').prop('checked', false);
        updatePermissionAccordion();
    }

    $(function () {
        $(document).on('click', '[data-permission-group]', function () {
            const boxes = $('.permission-' + $(this).data('permission-group'));
            boxes.prop('checked', boxes.length !== boxes.filter(':checked').length).trigger('change');
        });
        $(document).on('change', '.permission-checkbox', updatePermissionAccordion);
        $(document).on('input', '#permissionSearch', function () { filterPermissionModules(this.value); });
        $(document).on('click', '#clearPermissionSearch', function () {
            $('#permissionSearch').val('').trigger('input').focus();
        });
        updatePermissionAccordion();
    });
</script>
