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
        updatePermissionAccordion();
    });
</script>
