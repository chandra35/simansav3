{{-- Admin Action Buttons Style - Include this in your admin layout or specific pages --}}
<style>
/* ============================================
   Action Buttons - Refined Elegant Style
   ============================================ */

/* Base Action Button Group */
.action-btns {
    display: inline-flex;
    flex-wrap: nowrap;
    gap: 4px;
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
    padding: 0.3rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 6px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    line-height: 1;
    vertical-align: middle;
    border: none;
}

.action-btns .btn i {
    font-size: 0.8rem;
}

.action-btns .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.12);
}

.action-btns .btn:active {
    transform: translateY(0);
}

/* View Button - Soft blue */
.action-btns .btn-action-view {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}
.action-btns .btn-action-view:hover {
    background: #3b82f6;
    color: white;
}

/* Edit Button - Soft amber */
.action-btns .btn-action-edit {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}
.action-btns .btn-action-edit:hover {
    background: #f59e0b;
    color: white;
}

/* Delete Button - Soft red */
.action-btns .btn-action-delete {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
.action-btns .btn-action-delete:hover {
    background: #ef4444;
    color: white;
}

/* Success/Activate Button - Soft green */
.action-btns .btn-action-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}
.action-btns .btn-action-success:hover {
    background: #10b981;
    color: white;
}

/* Secondary/Neutral Button - Soft gray */
.action-btns .btn-action-secondary {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}
.action-btns .btn-action-secondary:hover {
    background: #64748b;
    color: white;
}

/* Primary Button - Soft indigo */
.action-btns .btn-action-primary {
    background: rgba(79, 70, 229, 0.1);
    color: #4f46e5;
}
.action-btns .btn-action-primary:hover {
    background: #4f46e5;
    color: white;
}

/* Warning Button - Soft orange */
.action-btns .btn-action-warning {
    background: rgba(249, 115, 22, 0.1);
    color: #ea580c;
}
.action-btns .btn-action-warning:hover {
    background: #ea580c;
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
    color: #3b82f6;
}
.action-btns .btn-action-outline.btn-outline-info:hover {
    background: #3b82f6;
    border-color: #3b82f6;
}
.action-btns .btn-action-outline.btn-outline-warning {
    color: #f59e0b;
}
.action-btns .btn-action-outline.btn-outline-warning:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}
.action-btns .btn-action-outline.btn-outline-danger {
    color: #ef4444;
}
.action-btns .btn-action-outline.btn-outline-danger:hover {
    background: #ef4444;
    border-color: #ef4444;
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
