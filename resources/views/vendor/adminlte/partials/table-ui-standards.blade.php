<style>
    .dataTables_wrapper {
        width: 100%;
        color: #334155;
    }

    .dataTables_wrapper > .row {
        align-items: center;
        margin-right: 0;
        margin-left: 0;
    }

    .dataTables_wrapper > .row > [class*="col-"] {
        padding-right: 0;
        padding-left: 0;
    }

    .dataTables_wrapper > .row:first-child {
        gap: .5rem 0;
        margin-bottom: .55rem;
    }

    .dataTables_wrapper > .row:last-child {
        gap: .5rem 0;
        margin-top: .55rem;
        padding-bottom: .8rem;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        margin: 0;
        color: #64748b;
        font-size: .75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        height: 32px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background-color: #fff;
        color: #334155;
        font-size: .75rem;
    }

    .dataTables_wrapper .dataTables_length select {
        width: auto !important;
        min-width: 4.35rem;
        padding: .2rem .5rem;
        background-image: none !important;
        -webkit-appearance: menulist !important;
        appearance: auto !important;
    }

    .dataTables_wrapper .dataTables_filter input {
        width: clamp(160px, 20vw, 240px) !important;
        margin-left: 0 !important;
        padding: .3rem .6rem;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 0 !important;
        color: #64748b;
        font-size: .72rem;
    }

    .dataTables_wrapper .pagination {
        margin: 0;
    }

    .dataTables_wrapper .page-link {
        min-width: 32px;
        padding: .35rem .55rem;
        text-align: center;
        font-size: .72rem;
    }

    .dataTables_wrapper table.dataTable {
        margin: 0 !important;
        border-collapse: collapse !important;
        table-layout: auto;
    }

    .dataTables_wrapper table.dataTable thead th {
        padding: .5rem .6rem;
        border-top: 1px solid #e2e8f0;
        border-bottom: 2px solid #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .035em;
        line-height: 1.2;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .dataTables_wrapper table.dataTable tbody td {
        padding: .52rem .6rem;
        border-top: 0;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
        font-size: .76rem;
        line-height: 1.3;
        vertical-align: middle;
        overflow-wrap: normal;
        word-break: normal;
    }

    .dataTables_wrapper table.dataTable tbody tr:last-child td {
        border-bottom: 0;
    }

    .dataTables_wrapper table.dataTable .dataTables_empty {
        padding: 2rem .75rem !important;
        color: #64748b;
        font-size: .8rem;
        text-align: center;
    }

    .dataTables_wrapper table.dataTable .badge {
        font-size: .64rem;
        line-height: 1.2;
        vertical-align: middle;
    }

    .dataTables_scroll,
    .dataTables_scrollHead,
    .dataTables_scrollBody {
        width: 100%;
    }

    .dataTables_scrollBody {
        scrollbar-color: #cbd5e1 transparent;
        scrollbar-width: thin;
    }

    .dataTables_scrollBody::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        border-radius: 10px;
        background: #cbd5e1;
    }

    .simansa-cell-stack {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 2px;
    }

    .simansa-cell-stack > strong,
    .simansa-cell-stack > span,
    .simansa-cell-stack > small {
        display: block;
        max-width: 100%;
    }

    .simansa-cell-stack > small {
        color: #64748b;
        font-size: .66rem;
    }

    /* Pagination is intentionally neutral across SIMANSA. */
    .pagination .page-link,
    .dataTables_wrapper .page-link {
        color: #495057 !important;
        background-color: #fff !important;
        border-color: #dee2e6 !important;
        box-shadow: none !important;
        margin-left: -1px !important;
        border-radius: 0 !important;
    }

    .pagination .page-item.active .page-link,
    .dataTables_wrapper .page-item.active .page-link {
        color: #495057 !important;
        background-color: #e9ecef !important;
        border-color: #dee2e6 !important;
    }

    .pagination .page-item:first-child .page-link {
        margin-left: 0 !important;
        border-radius: .25rem 0 0 .25rem !important;
    }

    .pagination .page-item:last-child .page-link {
        border-radius: 0 .25rem .25rem 0 !important;
    }

    .pagination .page-item.disabled .page-link,
    .dataTables_wrapper .page-item.disabled .page-link {
        color: #adb5bd !important;
        background-color: #fff !important;
        border-color: #dee2e6 !important;
    }

    @media (max-width: 767.98px) {
        .dataTables_wrapper > .row:first-child,
        .dataTables_wrapper > .row:last-child {
            align-items: stretch;
        }

        .dataTables_wrapper > .row > [class*="col-"] {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            text-align: left !important;
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
        }

        .dataTables_wrapper .dataTables_paginate .pagination {
            justify-content: flex-start !important;
            flex-wrap: wrap;
        }

        .dataTables_wrapper table.dataTable thead th {
            padding: .45rem .5rem;
        }

        .dataTables_wrapper table.dataTable tbody td {
            padding: .48rem .5rem;
        }
    }
</style>
<script>
    (function ($) {
        'use strict';

        if (!$ || !$.fn.dataTable || window.SimansaDataTableUi) {
            return;
        }

        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                infoEmpty: '',
                emptyTable: 'Tidak ada data tersedia',
                zeroRecords: 'Tidak ada data yang sesuai',
                paginate: {
                    previous: 'Sebelumnya',
                    next: 'Selanjutnya',
                },
            },
        });

        let adjustTimer = null;
        const observedWrappers = new WeakSet();
        const resizeObserver = window.ResizeObserver
            ? new ResizeObserver(function () { scheduleAdjust(80); })
            : null;

        function adjustVisibleTables() {
            adjustTimer = null;
            const tables = $.fn.dataTable.tables({ visible: true, api: true });
            if (tables && tables.columns) {
                tables.columns.adjust();
            }
        }

        function scheduleAdjust(delay) {
            window.clearTimeout(adjustTimer);
            adjustTimer = window.setTimeout(adjustVisibleTables, delay || 40);
        }

        function observeWrapper(table) {
            if (!resizeObserver || !table) {
                return;
            }
            const wrapper = table.closest('.dataTables_wrapper');
            if (wrapper && !observedWrappers.has(wrapper)) {
                observedWrappers.add(wrapper);
                resizeObserver.observe(wrapper);
            }
        }

        $(document).on('init.dt.simansaTableUi', function (event, settings) {
            observeWrapper(settings?.nTable);
            scheduleAdjust(60);
        });

        $(document).on('draw.dt.simansaTableUi column-visibility.dt.simansaTableUi responsive-resize.dt.simansaTableUi', function () {
            scheduleAdjust(30);
        });

        $(document).on('shown.bs.tab.simansaTableUi shown.bs.collapse.simansaTableUi shown.bs.modal.simansaTableUi', function () {
            scheduleAdjust(60);
        });

        $(document).on('collapsed.lte.pushmenu.simansaTableUi shown.lte.pushmenu.simansaTableUi', function () {
            scheduleAdjust(320);
        });

        document.addEventListener('load', function (event) {
            if (event.target?.matches?.('table.dataTable img')) {
                scheduleAdjust(30);
            }
        }, true);

        $(window).on('load.simansaTableUi resize.simansaTableUi orientationchange.simansaTableUi', function () {
            scheduleAdjust(80);
        });

        window.SimansaDataTableUi = { adjust: scheduleAdjust };
    })(window.jQuery);
</script>
