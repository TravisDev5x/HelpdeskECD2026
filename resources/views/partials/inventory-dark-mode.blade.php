{{-- Estilos modo oscuro para módulo Inventario V2. Incluir en vistas que extiendan admin.layout --}}
<style>
    .table-vcenter td,
    .table-vcenter th { vertical-align: middle; }
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
    .custom-control-label::before { border-color: #adb5bd; }
    .custom-control-input:checked~.custom-control-label::before {
        border-color: #007bff;
        background-color: #007bff;
    }
    body.dark-mode .modal-content {
        background-color: #343a40;
        color: #fff;
        border: 1px solid #4b545c;
    }
    body.dark-mode .modal-header { border-bottom-color: #4b545c; }
    body.dark-mode .modal-footer {
        border-top-color: #4b545c;
        background-color: #343a40;
    }
    body.dark-mode .close { color: #fff !important; opacity: 0.8; }
    body.dark-mode .form-control,
    body.dark-mode .custom-select {
        background-color: #3a4047;
        border: 1px solid #6c757d;
        color: #fff;
    }
    body.dark-mode .form-control:focus { border-color: #3f6791; }
    body.dark-mode .input-group-text {
        background-color: #3f474e;
        border-color: #6c757d;
        color: #fff;
    }
    body.dark-mode .table-bordered td,
    body.dark-mode .table-bordered th { border-color: #4b545c !important; }
    body.dark-mode .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    body.dark-mode .thead-dark th {
        background-color: #23272b;
        border-color: #383f45;
    }
    body.dark-mode .bg-light {
        background-color: #454d55 !important;
        color: #fff;
    }
    body.dark-mode .card { background-color: #343a40; }
    body.dark-mode .table-warning {
        background-color: #584d28 !important;
        color: #fff;
    }
    body.dark-mode .table-warning td { border-color: #6c5f35 !important; }
    body.dark-mode .timeline-item {
        background-color: #3a4047;
        color: #fff;
        border-color: #4b545c;
    }
    body.dark-mode .timeline-header {
        border-bottom-color: #4b545c;
        color: #ced4da;
    }
    body.dark-mode .list-group-item {
        background-color: #3a4047;
        border-color: #4b545c;
        color: #fff;
    }
    body.dark-mode .list-group-item.active {
        background-color: #3f6791;
        border-color: #3f6791;
    }
    .bg-suspended { background-color: #fff5f5; color: #dc3545; }
    body.dark-mode .bg-suspended { background-color: #442a2a; color: #ff6b6b; }
</style>
