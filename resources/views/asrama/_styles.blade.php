<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<style>
    :root {
        --asrama-navy: #091e34;
        --asrama-navy-light: #103c59;
        --asrama-teal: #0d9488;
        --asrama-teal-dark: #0f766e;
        --asrama-teal-light: #14b8a6;
        --asrama-gold: #f59e0b;
        --asrama-gold-dark: #d97706;
        --asrama-soft: #f0fdfa;
        --asrama-ink: #0f172a;
        --asrama-border: #e2e8f0;
    }

    /* Navbar clearance & Hero section */
    .content > .asrama-hero:first-child,
    .content-wrapper .asrama-hero:first-child,
    .asrama-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        padding: 1.6rem 2rem;
        margin-top: 0.85rem;
        margin-bottom: 1.5rem;
        border-radius: 20px;
        color: #ffffff;
        background: linear-gradient(135deg, var(--asrama-navy) 0%, var(--asrama-navy-light) 45%, var(--asrama-teal) 100%);
        position: relative;
        overflow: hidden;
        box-shadow: 0 14px 34px -8px rgba(9, 30, 52, 0.28), 0 4px 12px rgba(13, 148, 136, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .asrama-hero::before {
        content: "";
        position: absolute;
        top: -60%;
        right: -10%;
        width: 420px;
        height: 420px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .asrama-hero__content {
        position: relative;
        z-index: 1;
    }
    .asrama-hero__eyebrow {
        display: inline-flex;
        gap: .5rem;
        align-items: center;
        margin-bottom: .6rem;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #e0f2fe;
        background: rgba(255, 255, 255, 0.14);
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    .asrama-hero h2 {
        margin: 0 0 .4rem;
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
    }
    .asrama-hero p {
        max-width: 760px;
        margin: 0;
        color: rgba(240, 249, 255, 0.92);
        font-size: 0.95rem;
        line-height: 1.55;
    }
    .asrama-hero__action {
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }
    .asrama-hero__action .btn {
        border-radius: 12px;
        font-weight: 700;
        padding: 0.6rem 1.25rem;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        transition: all .2s ease;
    }
    .asrama-hero__action .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.22);
    }

    /* Panels & AdminLTE Cards */
    .asrama-panel {
        margin-bottom: 1.5rem;
        border: 1px solid var(--asrama-border);
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 8px 26px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        transition: all .2s ease;
    }
    .asrama-panel__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.45rem;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbfc;
    }
    .asrama-panel__header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 750;
        color: var(--asrama-ink);
        letter-spacing: -0.01em;
    }
    .asrama-panel__header p {
        margin: .25rem 0 0;
        color: #64748b;
        font-size: .88rem;
    }
    .asrama-panel__body {
        padding: 1.45rem;
    }

    /* Modernizing standard AdminLTE cards inside Asrama */
    .card.card-outline {
        border-radius: 18px !important;
        border-top-width: 4px !important;
        border-left: 1px solid var(--asrama-border) !important;
        border-right: 1px solid var(--asrama-border) !important;
        border-bottom: 1px solid var(--asrama-border) !important;
        box-shadow: 0 6px 22px rgba(15, 23, 42, 0.04) !important;
        overflow: hidden;
        transition: all .2s ease;
    }
    .card.card-outline .card-header {
        background: #fafbfc;
        border-bottom: 1px solid #edf2f7;
        padding: 1rem 1.35rem;
    }
    .card.card-outline .card-header .card-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--asrama-ink);
        letter-spacing: -0.01em;
    }
    .card.card-outline .card-body {
        padding: 1.35rem;
    }

    /* Stat Cards */
    .asrama-stat {
        height: 100%;
        padding: 1.25rem 1.35rem;
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid var(--asrama-border);
        border-left: 4px solid var(--asrama-teal);
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
        transition: all .25s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .asrama-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }
    .asrama-stat span {
        display: block;
        color: #64748b;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 700;
        margin-bottom: .25rem;
    }
    .asrama-stat strong {
        display: block;
        margin: .25rem 0 .4rem;
        font-size: 1.95rem;
        font-weight: 800;
        color: var(--asrama-ink);
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
    .asrama-stat small {
        color: #64748b;
        font-size: .82rem;
        line-height: 1.4;
    }
    .asrama-stat--primary { border-left-color: #3b82f6 !important; }
    .asrama-stat--success { border-left-color: #10b981 !important; }
    .asrama-stat--info { border-left-color: #06b6d4 !important; }
    .asrama-stat--warning { border-left-color: #f59e0b !important; }
    .asrama-stat--danger { border-left-color: #ef4444 !important; }

    /* Tables */
    .asrama-table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .asrama-table thead th {
        border-top: 0;
        border-bottom: 2px solid var(--asrama-border);
        background: #f8fafc;
        color: #475569;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .85rem 1rem;
        white-space: nowrap;
    }
    .asrama-table td {
        padding: .9rem 1rem;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .asrama-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .table-hover tbody tr:hover {
        background-color: #f8fafc;
    }
    .asrama-arab {
        font-family: "Amiri", "Traditional Arabic", "Noto Naskh Arabic", serif;
        font-size: 1.25em;
        line-height: 1.45;
        direction: rtl;
        color: #091e34;
    }

    /* Badges & Pills */
    .asrama-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .35rem .7rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .asrama-badge--active {
        color: #0f766e;
        background: #ccfbf1;
        border: 1px solid #99f6e4;
    }
    .asrama-badge--muted {
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .asrama-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .75rem;
        border-radius: 999px;
        color: #334155;
        background: #f1f5f9;
        font-size: .78rem;
        font-weight: 650;
        border: 1px solid #e2e8f0;
    }
    .asrama-icon-button {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Form Controls & Buttons */
    .asrama-form label,
    label.small.font-weight-bold {
        font-weight: 700;
        color: #334155;
        font-size: .84rem;
        margin-bottom: .4rem;
    }
    .asrama-form .form-control,
    .form-control {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        font-size: .9rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .asrama-form .form-control:focus,
    .form-control:focus {
        border-color: var(--asrama-teal);
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    .btn {
        border-radius: 10px;
        font-weight: 600;
        transition: all .2s ease;
    }
    .btn-info {
        background-color: var(--asrama-teal);
        border-color: var(--asrama-teal);
        color: #ffffff;
    }
    .btn-info:hover, .btn-info:focus {
        background-color: var(--asrama-teal-dark);
        border-color: var(--asrama-teal-dark);
        color: #ffffff;
    }
    .btn-outline-info {
        color: var(--asrama-teal);
        border-color: var(--asrama-teal);
    }
    .btn-outline-info:hover {
        background-color: var(--asrama-teal);
        border-color: var(--asrama-teal);
        color: #ffffff;
    }

    /* Room Cards */
    .asrama-room {
        position: relative;
        height: 100%;
        padding: 1.35rem;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid var(--asrama-border);
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
        transition: all .25s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .asrama-room:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.09);
    }
    .asrama-room--putra { border-top: 4px solid #3b82f6; }
    .asrama-room--putri { border-top: 4px solid #ec4899; }
    .asrama-room__capacity {
        height: 8px;
        border-radius: 999px;
        background: #f1f5f9;
        overflow: hidden;
    }
    .asrama-room__capacity span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--asrama-teal), var(--asrama-teal-light));
    }

    /* Modals */
    .asrama-modal .modal-content,
    .modal.fade .modal-content {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.25);
        overflow: hidden;
    }
    .asrama-modal .modal-header,
    .modal-header {
        padding: 1.25rem 1.5rem;
        color: #ffffff;
        border: 0;
        background: linear-gradient(135deg, var(--asrama-navy) 0%, var(--asrama-navy-light) 55%, var(--asrama-teal) 100%);
    }
    .asrama-modal .modal-title,
    .modal-title {
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.01em;
    }
    .asrama-modal .modal-header .close,
    .modal-header .close {
        color: #ffffff;
        text-shadow: none;
        opacity: .85;
        transition: opacity .2s;
    }
    .asrama-modal .modal-header .close:hover,
    .modal-header .close:hover {
        opacity: 1;
    }
    .asrama-modal .modal-body,
    .modal-body {
        padding: 1.5rem;
    }
    .asrama-modal .modal-footer,
    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
    }

    /* Helpers & Alerts */
    .asrama-alert { border: 0; border-radius: 14px; }
    .asrama-empty { padding: 2.5rem; text-align: center; color: #64748b; }
    .asrama-empty i { display: block; margin-bottom: .75rem; font-size: 2.2rem; color: #94a3b8; }
    .asrama-help {
        padding: .85rem 1.15rem;
        border-radius: 12px;
        color: #1e3a5f;
        background: #f0f9ff;
        border-left: 3px solid #0284c7;
        font-size: .88rem;
    }
    .asrama-choice {
        display: block;
        height: 100%;
        padding: 1rem;
        border: 1px solid var(--asrama-border);
        border-radius: 14px;
        background: #fafbfc;
        transition: all .2s ease;
    }
    .asrama-choice:hover {
        border-color: var(--asrama-teal);
        background: var(--asrama-soft);
    }
    .asrama-choice strong { display: block; color: var(--asrama-ink); }
    .asrama-choice small { color: #64748b; }

    /* Loading progress overlay */
    .asrama-progress {
        position: fixed;
        z-index: 2000;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(9, 30, 52, 0.65);
        backdrop-filter: blur(4px);
    }
    .asrama-progress.is-visible { display: flex; }
    .asrama-progress__box {
        width: min(390px, calc(100% - 36px));
        padding: 1.75rem;
        text-align: center;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.28);
    }
    .asrama-progress__icon {
        display: grid;
        width: 58px;
        height: 58px;
        margin: 0 auto 1rem;
        place-items: center;
        border-radius: 50%;
        color: #ffffff;
        background: linear-gradient(135deg, var(--asrama-teal), var(--asrama-navy));
        font-size: 1.45rem;
    }
    .asrama-progress .progress { height: 8px; border-radius: 999px; background: #e2e8f0; }
    .asrama-progress .progress-bar {
        width: 72%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--asrama-teal), var(--asrama-gold));
        animation: asrama-loading 1.25s ease-in-out infinite alternate;
    }

    /* Select2 integration */
    .select2-container { width: 100% !important; }
    .select2-container--bootstrap4 .select2-selection {
        min-height: 40px;
        border-color: #cbd5e1;
        border-radius: 10px;
    }
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: var(--asrama-teal);
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        border: 0;
        border-radius: 999px;
        color: #0f766e;
        background: #ccfbf1;
        padding: 2px 10px;
    }
    .select2-dropdown {
        border-color: #cbd5e1;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.14);
    }

    /* DataTables wrapper */
    .asrama-panel .dataTables_wrapper,
    .card .dataTables_wrapper {
        padding: 1.1rem 1.35rem 1.25rem;
    }
    .asrama-panel .dataTables_wrapper .row:first-child,
    .card .dataTables_wrapper .row:first-child {
        margin-bottom: .6rem;
    }
    .asrama-panel .dataTables_filter input,
    .card .dataTables_filter input {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        margin-left: .4rem;
        padding: 4px 8px;
    }
    .asrama-panel .dataTables_length select,
    .card .dataTables_length select {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        margin: 0 .35rem;
    }
    .asrama-panel .dataTables_info,
    .card .dataTables_info {
        color: #64748b;
        font-size: .85rem;
        padding-top:.85rem;
    }
    /* Modern Pagination (Laravel & DataTables) */
    .asrama-panel .pagination,
    .card .pagination,
    .pagination {
        margin: .5rem 0 0;
        gap: 3px;
        align-items: center;
    }
    .dataTables_paginate .pagination {
        margin: .4rem 0 0;
        justify-content: flex-end;
    }
    .asrama-panel .page-item .page-link,
    .card .page-item .page-link,
    .pagination .page-item .page-link,
    .dataTables_paginate .page-item .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 .6rem;
        margin: 0 1px;
        border: 1px solid #cbd5e1;
        border-radius: 8px !important;
        background-color: #ffffff;
        color: #334155;
        font-size: .82rem;
        font-weight: 600;
        line-height: 1;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: all .15s ease-in-out;
    }
    .asrama-panel .page-item:not(.active):not(.disabled) .page-link:hover,
    .card .page-item:not(.active):not(.disabled) .page-link:hover,
    .pagination .page-item:not(.active):not(.disabled) .page-link:hover,
    .dataTables_paginate .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #f0fdfa !important;
        border-color: #99f6e4 !important;
        color: #0d9488 !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(13, 148, 136, 0.16);
        z-index: 2;
    }
    .asrama-panel .page-item .page-link:focus,
    .card .page-item .page-link:focus,
    .pagination .page-item .page-link:focus,
    .dataTables_paginate .page-item .page-link:focus {
        outline: none;
        border-color: #14b8a6 !important;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.2) !important;
        z-index: 3;
    }
    .asrama-panel .page-item.active .page-link,
    .asrama-panel .page-item.active .page-link:hover,
    .asrama-panel .page-item.active .page-link:focus,
    .card .page-item.active .page-link,
    .card .page-item.active .page-link:hover,
    .card .page-item.active .page-link:focus,
    .pagination .page-item.active .page-link,
    .pagination .page-item.active .page-link:hover,
    .pagination .page-item.active .page-link:focus,
    .dataTables_paginate .page-item.active .page-link,
    .dataTables_paginate .page-item.active .page-link:hover,
    .dataTables_paginate .page-item.active .page-link:focus {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;
        border-color: #0f766e !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 3px 8px rgba(13, 148, 136, 0.35);
        z-index: 4;
        cursor: default;
        transform: none;
    }
    .asrama-panel .page-item.disabled .page-link,
    .asrama-panel .page-item.disabled .page-link:hover,
    .card .page-item.disabled .page-link,
    .card .page-item.disabled .page-link:hover,
    .pagination .page-item.disabled .page-link,
    .pagination .page-item.disabled .page-link:hover,
    .dataTables_paginate .page-item.disabled .page-link,
    .dataTables_paginate .page-item.disabled .page-link:hover {
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        color: #94a3b8 !important;
        box-shadow: none !important;
        cursor: not-allowed;
        opacity: 0.75;
        transform: none;
    }
    table.dataTable { border-collapse: collapse !important; }

    @keyframes asrama-loading {
        from { transform: translateX(-22%); }
        to { transform: translateX(38%); }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .content > .asrama-hero:first-child,
        .content-wrapper .asrama-hero:first-child,
        .asrama-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.35rem 1.25rem;
            margin-top: 0.5rem;
            margin-bottom: 1.2rem;
            gap: 1rem;
        }
        .asrama-hero h2 {
            font-size: 1.45rem;
        }
        .asrama-hero p {
            font-size: .88rem;
        }
        .asrama-hero__action {
            width: 100%;
        }
        .asrama-hero__action .btn,
        .asrama-hero__action .btn-group {
            width: 100%;
            display: flex;
        }
        .asrama-hero__action .btn-group .btn {
            flex: 1;
        }
        .asrama-panel__header {
            align-items: flex-start;
            flex-direction: column;
            padding: 1rem 1.15rem;
            gap: .75rem;
        }
        .asrama-panel__body {
            padding: 1.15rem;
        }
        .asrama-stat {
            padding: 1rem;
        }
        .asrama-stat strong {
            font-size: 1.6rem;
        }
    }
    @media (max-width: 576px) {
        .asrama-stat {
            margin-bottom: .75rem;
        }
        .pagination,
        .dataTables_paginate .pagination {
            flex-wrap: wrap;
            justify-content: center;
            gap: 2px;
        }
        .asrama-panel .page-item .page-link,
        .card .page-item .page-link,
        .pagination .page-item .page-link,
        .dataTables_paginate .page-item .page-link {
            min-width: 30px;
            height: 30px;
            padding: 0 .45rem;
            font-size: .76rem;
        }
    }
</style>
