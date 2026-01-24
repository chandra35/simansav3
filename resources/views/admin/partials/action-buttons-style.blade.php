{{-- Admin Action Buttons Style - Include this in your admin layout or specific pages --}}
<style>
/* ============================================
   Action Buttons - Unified Style
   ============================================ */

/* Base Action Button Group */
.action-btns {
    display: inline-flex;
    flex-wrap: nowrap;
    gap: 3px;
    align-items: center;
    justify-content: center;
}

/* Ensure forms inside action-btns are inline-flex too */
.action-btns form,
.action-btns .action-form {
    display: inline-flex;
    margin: 0;
    padding: 0;
}

.action-btns .btn {
    padding: 0.25rem 0.45rem;
    font-size: 0.75rem;
    border-radius: 4px;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    line-height: 1;
    vertical-align: middle;
}

.action-btns .btn i {
    font-size: 0.8rem;
}

.action-btns .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.action-btns .btn:active {
    transform: translateY(0);
}

/* View Button */
.action-btns .btn-action-view {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border: none;
    color: white;
}
.action-btns .btn-action-view:hover {
    background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
    color: white;
}

/* Edit Button */
.action-btns .btn-action-edit {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    border: none;
    color: #212529;
}
.action-btns .btn-action-edit:hover {
    background: linear-gradient(135deg, #e0a800 0%, #c69500 100%);
    color: #212529;
}

/* Delete Button */
.action-btns .btn-action-delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border: none;
    color: white;
}
.action-btns .btn-action-delete:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    color: white;
}

/* Success/Activate Button */
.action-btns .btn-action-success {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    border: none;
    color: white;
}
.action-btns .btn-action-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
    color: white;
}

/* Secondary/Neutral Button */
.action-btns .btn-action-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
    color: white;
}
.action-btns .btn-action-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
    color: white;
}

/* Primary Button */
.action-btns .btn-action-primary {
    background: linear-gradient(135deg, #007bff 0%, #0069d9 100%);
    border: none;
    color: white;
}
.action-btns .btn-action-primary:hover {
    background: linear-gradient(135deg, #0069d9 0%, #0056b3 100%);
    color: white;
}

/* Warning Button */
.action-btns .btn-action-warning {
    background: linear-gradient(135deg, #fd7e14 0%, #e06700 100%);
    border: none;
    color: white;
}
.action-btns .btn-action-warning:hover {
    background: linear-gradient(135deg, #e06700 0%, #cc5c00 100%);
    color: white;
}

/* Outline variants */
.action-btns .btn-action-outline {
    background: transparent;
    border: 1.5px solid currentColor;
}
.action-btns .btn-action-outline:hover {
    color: white;
}
.action-btns .btn-action-outline.btn-outline-info {
    color: #17a2b8;
}
.action-btns .btn-action-outline.btn-outline-info:hover {
    background: #17a2b8;
    border-color: #17a2b8;
}
.action-btns .btn-action-outline.btn-outline-warning {
    color: #ffc107;
}
.action-btns .btn-action-outline.btn-outline-warning:hover {
    background: #ffc107;
    border-color: #ffc107;
    color: #212529;
}
.action-btns .btn-action-outline.btn-outline-danger {
    color: #dc3545;
}
.action-btns .btn-action-outline.btn-outline-danger:hover {
    background: #dc3545;
    border-color: #dc3545;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-btns {
        gap: 2px;
    }
    .action-btns .btn {
        padding: 0.2rem 0.35rem;
        min-width: 26px;
        height: 26px;
    }
    .action-btns .btn i {
        font-size: 0.75rem;
    }
    /* Hide text on mobile, show only icons */
    .action-btns .btn .btn-text {
        display: none;
    }
}

/* Table action column */
.table td .action-btns {
    white-space: nowrap;
}

/* Tooltip enhancement */
.action-btns [data-toggle="tooltip"] {
    position: relative;
}

/* Status Toggle Buttons */
.btn-status-toggle {
    min-width: 70px;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.15s ease;
    height: 24px;
    line-height: 1;
}

.btn-status-toggle.active {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    border: none;
    color: white;
}

.btn-status-toggle.inactive {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
    color: white;
}

.btn-status-toggle:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Card Footer Action Buttons */
.card-footer .action-btns-full {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.card-footer .action-btns-full .btn {
    flex: 1;
    min-width: 100px;
}

@media (max-width: 576px) {
    .card-footer .action-btns-full {
        flex-direction: column;
    }
    .card-footer .action-btns-full .btn {
        width: 100%;
    }
}

/* Dropdown action menu for many actions */
.action-dropdown .dropdown-toggle {
    padding: 0.35rem 0.6rem;
    font-size: 0.8rem;
    border-radius: 4px;
}

.action-dropdown .dropdown-menu {
    min-width: 140px;
    padding: 0.25rem 0;
    font-size: 0.85rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: none;
    border-radius: 6px;
}

.action-dropdown .dropdown-item {
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-dropdown .dropdown-item i {
    width: 16px;
    text-align: center;
}

.action-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.action-dropdown .dropdown-item.text-danger:hover {
    background-color: #fff5f5;
}

.action-dropdown .dropdown-divider {
    margin: 0.25rem 0;
}

/* Delete confirmation button inside form */
form.d-inline .btn,
form.action-form .btn {
    vertical-align: middle;
}
</style>
