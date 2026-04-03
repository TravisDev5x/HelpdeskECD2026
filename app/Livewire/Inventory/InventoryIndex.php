<?php

namespace App\Livewire\Inventory;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

// MODELOS
use App\Models\InvAsset;
use App\Models\InvCategory;
use App\Models\InvStatus;
use App\Models\InvLabel;
use App\Models\InvAssetImage;
use App\Models\InvMovement;
use App\Models\InvImportBatch;
use App\Models\InvImportRow;
use App\Models\InvComponent;
use App\Models\InvComponentMovement;
use App\Models\Company;
use App\Models\Sede;
use App\Models\Ubicacion;
use App\Models\User;
use App\Livewire\Inventory\Concerns\GuardsInventoryV2Filters;
use App\Support\InventoryV2FilterPermissions;
use App\Support\LogsInventoryAssignmentActivity;
use App\Support\Inventory\InventoryImportMatcher;

class InventoryIndex extends Component
{
    use WithPagination, WithFileUploads, GuardsInventoryV2Filters;

    protected $paginationTheme = 'bootstrap';

    // --- FILTROS Y BUSCADOR ---
    public $search = '';
    public $category_filter = '';
    public $status_filter = '';
    public $sede_filter = '';
    public $label_filter = '';
    public $export_date_field = 'created_at';
    public $export_date_from = '';
    public $export_date_to = '';
    /** Responsable actual (desde query o filtro; enlaza con la vista de asignaciones). */
    #[Url(except: '')]
    public $user_filter = '';
    public $perPage = 10;

    // --- IMPORTACIÓN EXCEL ---
    public $showImportModal = false;
    public $import_file;
    public $import_mode = 'upsert_by_serial';
    public $import_default_category_id = '';
    public $import_default_company_id = '';
    public $import_default_status_id = '';
    public bool $import_create_missing_users = false;
    public $import_preview = [];
    public $import_ready_rows = [];
    public $import_batch_id = null;
    public $import_summary = [
        'total' => 0,
        'valid' => 0,
        'errors' => 0,
        'warnings' => 0,
        'create' => 0,
        'update' => 0,
    ];

    // --- VARIABLES DE MODALES ---
    public $viewMode = false;
    public $showFormModal = false;
    public $isEditMode = false;
    public $showAssignModal = false;
    public $showBulkModal = false;

    public $selectedAsset = null;

    // --- VARIABLES CRUD INDIVIDUAL ---
    public $asset_id;
    public $name, $internal_tag, $serial, $category_id, $status_id, $label_id, $selected_sede_label_name, $cost, $notes;
    public $company_id, $sede_id, $ubicacion_id;
    public $brand, $model, $ip, $mac;
    public $condition = 'BUENO';
    public $warranty_expiry;
    public $purchase_date;

    // Listas dinámicas
    public $companies = [];
    public $sedes = [];
    public $ubicaciones = [];

    // --- VARIABLES ASIGNACIÓN ---
    public $assign_asset_id;
    public $assign_user_id;
    public $assign_notes;
    public $assign_date;
    public $assign_reason = '';
    public $assign_responsiva;
    public $users_list = [];

    /** Asignación masiva (varios activos → un responsable) */
    public $showBulkAssignModal = false;
    public $bulk_assign_user_id;
    public $bulk_assign_notes = '';
    public $bulk_assign_date;
    public $bulk_assign_reason = '';
    public $bulk_assign_responsiva;

    /** Devolución / desasignación masiva */
    public $showBulkCheckinModal = false;
    public $bulk_checkin_notes = '';
    public $bulk_checkin_date;
    public $bulk_checkin_reason = '';
    public $bulk_checkin_responsiva;

    // --- VARIABLES EDICIÓN MASIVA [NUEVAS] ---
    public $selected = [];
    public $selectAll = false;
    
    public $bulk_category_id = '';
    public $bulk_status_id = '';
    public $bulk_company_id = '';
    public $bulk_sede_id = '';
    public $bulk_ubicacion_id = '';
    
    //Variables fotos
    public $evidence_photos = [];
    public $asset_id_for_photos;
    public $showPhotoModal = false;
    public $stored_photos = [];



    
    // Lista especial para el modal masivo (para no mezclar con la edición individual)
    public $bulk_ubicaciones = [];

    // Modal de confirmación (reutilizable)
    public $showConfirmModal = false;
    public $confirmTitle = '';
    public $confirmMessage = '';
    public $confirmButtonText = 'Confirmar';
    public $confirmButtonClass = 'btn-danger';
    public $confirmAction = '';
    public $confirmTargetId = null;

    // --- DEVOLUCIÓN (CHECK-IN) ---
    public $showCheckinModal = false;
    public $checkin_asset_id;
    public $checkin_notes = '';
    public $checkin_date;
    public $checkin_reason = '';
    public $checkin_responsiva;

    // --- BAJA ---
    public $showBajaModal = false;
    public $baja_asset_id;
    public $baja_status_id;
    public $baja_notes = '';

    // --- DESPIECE (canibalización) ---
    public $showDespieceModal = false;
    public $despieceAsset = null;
    public $despieceComponents = [];
    public $selectedForExtraction = [];
    public $despieceNotes = '';

    // --- TRASLADO ---
    public $showTrasladoModal = false;
    public $traslado_asset_id;
    public $traslado_sede_id;
    public $traslado_ubicacion_id;
    public $traslado_ubicaciones = [];
    public $traslado_origen_sede = '';
    public $traslado_origen_ubicacion = '';
    public $traslado_date;
    public $traslado_reason = '';
    public $traslado_notes = '';

    // =================================================================
    //  CICLO DE VIDA
    // =================================================================
    public function mount()
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory'), 403);

        $this->companies = Company::select('id', 'name')->orderBy('name')->get();
        $this->sedes = Sede::orderBy('sede')->get(); // Cargamos todas las sedes

        $this->stripUnauthorizedInventoryFilters([
            'search' => 'search',
            'category' => 'category_filter',
            'status' => 'status_filter',
            'assignee' => 'user_filter',
            'sede' => 'sede_filter',
        ]);
    }

    public function updatingSearch()
    {
        if (! $this->userCanInventoryFilter('search')) {
            $this->search = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingCategoryFilter()
    {
        if (! $this->userCanInventoryFilter('category')) {
            $this->category_filter = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingStatusFilter()
    {
        if (! $this->userCanInventoryFilter('status')) {
            $this->status_filter = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingSedeFilter()
    {
        if (! $this->userCanInventoryFilter('sede')) {
            $this->sede_filter = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingUserFilter()
    {
        if (! $this->userCanInventoryFilter('assignee')) {
            $this->user_filter = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingLabelFilter()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingExportDateFrom()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingExportDateTo()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingExportDateField()
    {
        if (! in_array($this->export_date_field, ['created_at', 'purchase_date'], true)) {
            $this->export_date_field = 'created_at';
        }
        $this->resetPage();
        $this->resetSelection();
    }
    public function updatingPage() { $this->resetSelection(); }

    public function resetSelection()
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    // =================================================================
    //  LOGICA CASCADA (INDIVIDUAL)
    // =================================================================
    public function updatedSedeId($value)
    {
        if ($value) {
            $this->ubicaciones = Ubicacion::where('id_sede', $value)->get();
            $this->syncLabelFromSede((int) $value);
        } else {
            $this->ubicaciones = [];
            $this->label_id = null;
            $this->selected_sede_label_name = null;
        }
        $this->ubicacion_id = null;
    }

    public function updatedLabelId($value): void
    {
        $this->resetErrorBag('label_id');

        if (! $value) {
            if ($this->sede_id) {
                $this->syncLabelFromSede((int) $this->sede_id);
            } else {
                $this->selected_sede_label_name = null;
            }

            return;
        }

        $label = InvLabel::query()
            ->with('sede:id,sede')
            ->whereKey($value)
            ->where('is_active', true)
            ->first();

        if (! $label) {
            $this->label_id = null;
            $this->selected_sede_label_name = null;
            $this->addError('label_id', 'La etiqueta de sede seleccionada ya no está activa.');
            return;
        }

        $this->label_id = $label->id;
        $this->selected_sede_label_name = $label->name;

        if ((int) $this->sede_id !== (int) $label->sede_id) {
            $this->sede_id = (int) $label->sede_id;
            $this->ubicaciones = Ubicacion::where('id_sede', $this->sede_id)->get();
            $this->ubicacion_id = null;
        }
    }

    // =================================================================
    //  LOGICA CASCADA (MASIVA) - [NUEVO]
    // =================================================================
    public function updatedBulkSedeId($value)
    {
        // Esta función controla el dropdown del MODAL MASIVO
        if ($value) {
            $this->bulk_ubicaciones = Ubicacion::where('id_sede', $value)->get();
        } else {
            $this->bulk_ubicaciones = [];
        }
        $this->bulk_ubicacion_id = null;
    }

    // =================================================================
    //  LOGICA KARDEX
    // =================================================================
    public function viewAsset($id)
    {
        $this->selectedAsset = InvAsset::with([
            'movements.user',
            'movements.previousUser',
            'movements.admin',
            'category',
            'ubicacion',
            'status',
            'sede',
            'company',
        ])->find($id);
        $this->viewMode = true;
    }
    public function closeView() { $this->viewMode = false; $this->selectedAsset = null; }

    // =================================================================
    //  LOGICA CRUD (CREAR/EDITAR)
    // =================================================================
    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        
        $asset = InvAsset::find($id);
        $this->asset_id = $asset->id;
        
        $this->name = $asset->name;
        $this->internal_tag = $asset->internal_tag;
        $this->serial = $asset->serial;
        $this->category_id = $asset->category_id;
        $this->status_id = $asset->status_id;
        $this->label_id = $asset->label_id;
        $this->cost = $asset->cost;
        $this->notes = $asset->notes;

        $this->company_id = $asset->company_id;
        $this->sede_id = $asset->sede_id;
        $this->ubicacion_id = $asset->ubicacion_id;

        if ($this->sede_id) {
            $this->ubicaciones = Ubicacion::where('id_sede', $this->sede_id)->get();
            $this->syncLabelFromSede((int) $this->sede_id);
        }

        $this->brand = $asset->specs['marca'] ?? '';
        $this->model = $asset->specs['modelo'] ?? '';
        $this->ip = $asset->specs['ip'] ?? '';
        $this->mac = $asset->specs['mac'] ?? '';
        $this->condition = $asset->condition ?? 'BUENO';
        $this->warranty_expiry = $asset->warranty_expiry ? $asset->warranty_expiry->format('Y-m-d') : '';
        $this->purchase_date = $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '';

        $this->showFormModal = true;
    }

    public function store()
    {
        $this->normalizeAssetIdentityFields();
        $this->syncLabelFromSede($this->sede_id ? (int) $this->sede_id : null);

        // Si es alta y no se captura tag interno, se genera automáticamente.
        if (! $this->isEditMode && ! $this->internal_tag) {
            $this->internal_tag = $this->generateAutoInternalTag();
        }

        $rules = [
            'name' => 'required|min:3',
            'category_id' => 'required',
            'status_id' => 'required',
            'company_id' => 'required',
            'sede_id' => 'nullable',
            'ubicacion_id' => 'nullable',
            'label_id' => 'nullable|exists:inv_labels,id',
            'internal_tag' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('inv_assets', 'internal_tag')->ignore($this->asset_id),
            ],
            'serial' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('inv_assets', 'serial')->ignore($this->asset_id),
            ],
            'cost' => 'nullable|numeric',
            'condition' => 'nullable|in:NUEVO,BUENO,REGULAR,MALO,PARA_PIEZAS',
            'warranty_expiry' => 'nullable|date',
            'purchase_date' => 'nullable|date',
        ];
        $messages = [
            'internal_tag.unique' => 'La etiqueta interna ya está registrada en otro activo.',
            'serial.unique' => 'El tag/serie ya está registrado en otro activo.',
        ];

        if ($this->sede_id && ! $this->label_id) {
            $this->addError('label_id', 'La sede seleccionada no tiene una etiqueta activa configurada.');
            return;
        }

        if ($this->label_id && $this->sede_id) {
            $labelSedeId = (int) InvLabel::query()->whereKey($this->label_id)->value('sede_id');
            if ($labelSedeId > 0 && $labelSedeId !== (int) $this->sede_id) {
                $this->addError('label_id', 'La etiqueta de sede no corresponde a la sede seleccionada.');
                return;
            }
        }

        if ($this->internal_tag && $this->label_id) {
            $labelName = (string) InvLabel::query()->whereKey($this->label_id)->value('name');
            if ($labelName !== '' && $this->normalizeAssetIdentityValue($this->internal_tag) === $this->normalizeAssetIdentityValue($labelName)) {
                $this->addError('internal_tag', 'El tag interno no debe ser igual a la etiqueta de sede. Usa un folio único del activo.');
                return;
            }
        }

        $this->validate($rules, $messages);

        $specs = ['marca' => $this->brand, 'modelo' => $this->model, 'ip' => $this->ip, 'mac' => $this->mac];

        $data = [
            'name' => $this->name,
            'internal_tag' => $this->internal_tag,
            'serial' => $this->serial,
            'category_id' => $this->category_id,
            'status_id' => $this->status_id,
            'label_id' => $this->label_id,
            'cost' => $this->cost ?: 0,
            'notes' => $this->notes,
            'specs' => $specs,
            'company_id' => $this->company_id,
            'sede_id' => $this->sede_id,
            'ubicacion_id' => $this->ubicacion_id,
            'condition' => $this->condition ?: 'BUENO',
            'warranty_expiry' => $this->warranty_expiry ?: null,
            'purchase_date' => $this->purchase_date ?: null,
        ];

        try {
            if ($this->isEditMode) {
                InvAsset::find($this->asset_id)->update($data);
                session()->flash('message', 'Activo actualizado.');
            } else {
                $data['uuid'] = (string) Str::uuid();
                $data['condition'] = 'NUEVO';
                $newAsset = InvAsset::create($data);
                InvMovement::create(['asset_id' => $newAsset->id, 'type' => 'AUDIT', 'admin_id' => auth()->id() ?? 1, 'date' => now(), 'notes' => 'Alta inicial V2.']);
                session()->flash('message', 'Activo creado.');
            }
        } catch (QueryException $e) {
            if ($this->isDuplicateAssetIdentityException($e)) {
                return;
            }

            throw $e;
        }

        $this->closeFormModal();
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
        $this->resetInputFields();
    }

    // =================================================================
    //  IMPORTACIÓN EXCEL (PREVIEW + UPSERT POR SERIE)
    // =================================================================
    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->resetImportState();
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->resetImportState();
    }

    public function previewImport(): void
    {
        $this->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'import_default_category_id' => 'nullable|exists:inv_categories,id',
            'import_default_company_id' => 'nullable|exists:companies,id',
            'import_default_status_id' => 'nullable|exists:inv_statuses,id',
        ]);

        $this->import_preview = [];
        $this->import_ready_rows = [];
        $this->import_batch_id = null;
        $this->import_summary = ['total' => 0, 'valid' => 0, 'errors' => 0, 'warnings' => 0, 'create' => 0, 'update' => 0];

        $sheets = Excel::toArray([], $this->import_file);
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            $this->addError('import_file', 'El archivo no contiene filas de datos.');
            return;
        }

        /** @var InventoryImportMatcher $matcher */
        $matcher = app(InventoryImportMatcher::class);
        $headers = array_map(fn ($h) => $matcher->normalizeHeaderKey($h), $rows[0] ?? []);
        if (! in_array('serie', $headers, true) && ! in_array('serial', $headers, true)) {
            $this->addError('import_file', 'La hoja debe incluir columna Serie o Serial para el modo crear/actualizar por serie.');
            return;
        }

        $statusMap = $this->buildNameIdMap(InvStatus::query()->get(['id', 'name'])->all());
        $categoryMap = $this->buildNameIdMap(InvCategory::query()->get(['id', 'name'])->all());
        $companyMap = $this->buildNameIdMap(Company::query()->get(['id', 'name'])->all());
        $sedeMap = $this->buildNameIdMap(Sede::query()->get(['id', 'sede as name'])->all());
        $userLookup = $this->buildUserLookupMaps();

        $batch = InvImportBatch::create([
            'user_id' => Auth::id(),
            'file_name' => method_exists($this->import_file, 'getClientOriginalName') ? $this->import_file->getClientOriginalName() : null,
            'mode' => $this->import_mode,
            'defaults' => [
                'default_status_id' => $this->import_default_status_id ?: null,
                'default_category_id' => $this->import_default_category_id ?: null,
                'default_company_id' => $this->import_default_company_id ?: null,
                'create_missing_users' => $this->import_create_missing_users,
            ],
            'status' => 'previewed',
        ]);
        $this->import_batch_id = $batch->id;

        foreach (array_slice($rows, 1) as $index => $row) {
            $rowNumber = $index + 2;
            $assoc = $matcher->associateRowByHeaders($headers, $row);
            if ($matcher->rowIsEmpty($assoc)) {
                continue;
            }

            $this->import_summary['total']++;
            $result = $matcher->parseRow($assoc, [
                'status_map' => $statusMap,
                'category_map' => $categoryMap,
                'company_map' => $companyMap,
                'sede_map' => $sedeMap,
                'user_lookup' => $userLookup,
                'default_status_id' => $this->import_default_status_id ?: null,
                'default_category_id' => $this->import_default_category_id ?: null,
                'default_company_id' => $this->import_default_company_id ?: null,
                'create_missing_users' => $this->import_create_missing_users,
            ]);

            $parsed = $result['parsed'];
            $errors = $result['errors'];
            $warnings = $result['warnings'] ?? [];
            $action = $result['action'] ?? 'ACTUALIZAR';

            if (! empty($errors)) {
                $statusText = implode(' | ', $errors);
                $statusType = 'error';
            } elseif (! empty($warnings)) {
                $statusText = 'OK con advertencias: '.implode(' | ', $warnings);
                $statusType = 'warn';
                $this->import_summary['warnings']++;
            } else {
                $statusText = 'OK';
                $statusType = 'ok';
            }

            $preview = [
                'row' => $rowNumber,
                'serie' => $parsed['serial'] ?: '—',
                'nombre' => $parsed['name'] ?: '—',
                'accion' => $action,
                'estado' => $statusText,
                'tipo' => $statusType,
            ];
            $this->import_preview[] = $preview;

            InvImportRow::create([
                'batch_id' => $batch->id,
                'row_number' => $rowNumber,
                'payload' => $assoc,
                'parsed' => $parsed,
                'errors' => $errors,
                'warnings' => $warnings,
                'action' => $action,
                'status' => empty($errors) ? 'ready' : 'pending',
            ]);

            if (! empty($errors)) {
                $this->import_summary['errors']++;
                continue;
            }

            if ($action === 'CREAR') {
                $this->import_summary['create']++;
            } else {
                $this->import_summary['update']++;
            }
            $this->import_summary['valid']++;
            $this->import_ready_rows[] = $parsed;
        }

        if ($this->import_summary['total'] === 0) {
            $this->addError('import_file', 'No se detectaron filas útiles para importar.');
            $batch->update(['status' => 'empty', 'summary' => $this->import_summary]);
            return;
        }

        $batch->update([
            'summary' => $this->import_summary,
            'status' => $this->import_summary['errors'] > 0 ? 'needs_review' : 'ready',
        ]);
    }

    public function executeImport(): void
    {
        if (empty($this->import_ready_rows)) {
            $this->addError('import_file', 'Primero previsualiza un archivo válido con filas importables.');
            return;
        }

        $created = 0;
        $updated = 0;

        /** @var InventoryImportMatcher $matcher */
        $matcher = app(InventoryImportMatcher::class);
        $batchId = $this->import_batch_id ? (int) $this->import_batch_id : null;
        $rowStates = [];

        DB::transaction(function () use (&$created, &$updated, $matcher, $batchId, &$rowStates) {
            foreach ($this->import_ready_rows as $row) {
                $result = $matcher->upsertParsedRow($row, auth()->id(), [
                    'create_missing_users' => $this->import_create_missing_users,
                ]);
                if (($result['action'] ?? '') === 'ACTUALIZAR') {
                    $updated++;
                } else {
                    $created++;
                }

                $rowStates[(string) ($row['serial'] ?? '')] = (int) ($result['asset_id'] ?? 0);
            }

            if ($batchId) {
                $rows = InvImportRow::query()
                    ->where('batch_id', $batchId)
                    ->where('status', 'ready')
                    ->get();

                foreach ($rows as $r) {
                    $parsed = (array) ($r->parsed ?? []);
                    $serial = (string) ($parsed['serial'] ?? '');
                    $assetId = $rowStates[$serial] ?? null;
                    if (! $assetId) {
                        continue;
                    }
                    $r->update([
                        'status' => 'resolved',
                        'processed_asset_id' => $assetId,
                        'resolved_at' => now(),
                    ]);
                }

                InvImportBatch::whereKey($batchId)->update([
                    'status' => 'executed',
                    'summary' => array_merge($this->import_summary, [
                        'executed_created' => $created,
                        'executed_updated' => $updated,
                    ]),
                ]);
            }
        });

        session()->flash('message', "Importación completada: {$created} creados y {$updated} actualizados.");
        $this->closeImportModal();
    }

    private function resetInputFields()
    {
        $this->reset(['name', 'internal_tag', 'serial', 'category_id', 'status_id', 'label_id', 'selected_sede_label_name', 'cost', 'company_id', 'sede_id', 'ubicacion_id', 'notes', 'brand', 'model', 'ip', 'mac', 'asset_id', 'condition', 'warranty_expiry', 'purchase_date']);
        $this->condition = 'BUENO';
        $this->ubicaciones = [];
    }

    private function normalizeAssetIdentityFields(): void
    {
        $this->internal_tag = $this->normalizeAssetIdentityValue($this->internal_tag);
        $this->serial = $this->normalizeAssetIdentityValue($this->serial);
    }

    private function syncLabelFromSede(?int $sedeId): void
    {
        $this->resetErrorBag('label_id');

        if (! $sedeId) {
            $this->label_id = null;
            $this->selected_sede_label_name = null;
            return;
        }

        $currentLabel = $this->label_id
            ? InvLabel::query()->whereKey($this->label_id)->where('is_active', true)->first()
            : null;
        if ($currentLabel && (int) $currentLabel->sede_id === (int) $sedeId) {
            $this->selected_sede_label_name = $currentLabel->name;
            return;
        }

        $label = InvLabel::query()
            ->where('sede_id', $sedeId)
            ->where('is_active', true)
            ->first();

        if (! $label) {
            $this->label_id = null;
            $this->selected_sede_label_name = null;
            return;
        }

        $this->label_id = $label->id;
        $this->selected_sede_label_name = $label->name;
    }

    private function normalizeAssetIdentityValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = Str::upper(trim((string) $value));

        return $normalized === '' ? null : $normalized;
    }

    private function generateAutoInternalTag(): string
    {
        $sedeCode = $this->buildAssetTagSegment(
            (string) Sede::query()->whereKey($this->sede_id)->value('sede'),
            'SED'
        );
        $categoryCode = $this->buildAssetTagSegment(
            (string) InvCategory::query()->whereKey($this->category_id)->value('name'),
            'CAT'
        );

        $prefix = $sedeCode.'-'.$categoryCode;
        $existingTags = InvAsset::query()
            ->whereNotNull('internal_tag')
            ->where('internal_tag', 'like', $prefix.'-%')
            ->pluck('internal_tag');

        $maxSeq = 0;
        foreach ($existingTags as $tag) {
            if (preg_match('/^'.preg_quote($prefix, '/').'-(\d{4})$/', (string) $tag, $m)) {
                $maxSeq = max($maxSeq, (int) $m[1]);
            }
        }

        $seq = $maxSeq + 1;
        do {
            $candidate = $prefix.'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            $exists = InvAsset::query()->where('internal_tag', $candidate)->exists();
            $seq++;
        } while ($exists);

        return $candidate;
    }

    private function buildAssetTagSegment(?string $source, string $fallback): string
    {
        $normalized = Str::of((string) $source)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '')
            ->value();

        if ($normalized === '') {
            return $fallback;
        }

        return substr($normalized, 0, 3);
    }

    private function activeLabelsForForm()
    {
        return InvLabel::query()
            ->with('sede:id,sede')
            ->where('is_active', true)
            ->orderBy('sede_id')
            ->orderBy('name')
            ->get(['id', 'sede_id', 'name']);
    }

    private function isDuplicateAssetIdentityException(QueryException $e): bool
    {
        if ((string) $e->getCode() !== '23000') {
            return false;
        }

        $message = Str::lower($e->getMessage());
        $isInternalTagDuplicate = str_contains($message, 'internal_tag');
        $isSerialDuplicate = str_contains($message, 'serial');

        if (! $isInternalTagDuplicate && ! $isSerialDuplicate) {
            $this->addError('internal_tag', 'La etiqueta interna ya está registrada en otro activo.');
            $this->addError('serial', 'El tag/serie ya está registrado en otro activo.');
        } else {
            if ($isInternalTagDuplicate) {
                $this->addError('internal_tag', 'La etiqueta interna ya está registrada en otro activo.');
            }
            if ($isSerialDuplicate) {
                $this->addError('serial', 'El tag/serie ya está registrado en otro activo.');
            }
        }

        session()->flash('error', 'No se guardó el activo: etiqueta o tag/serie duplicado.');

        return true;
    }

    private function resetImportState(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->import_file = null;
        $this->import_mode = 'upsert_by_serial';
        $this->import_default_category_id = '';
        $this->import_default_company_id = '';
        $this->import_default_status_id = '';
        $this->import_create_missing_users = false;
        $this->import_preview = [];
        $this->import_ready_rows = [];
        $this->import_batch_id = null;
        $this->import_summary = ['total' => 0, 'valid' => 0, 'errors' => 0, 'warnings' => 0, 'create' => 0, 'update' => 0];
    }

    private function normalizeHeaderKey($value): string
    {
        $text = Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        return $text;
    }

    private function associateRowByHeaders(array $headers, array $row): array
    {
        $assoc = [];
        foreach ($headers as $idx => $header) {
            if ($header === '') {
                continue;
            }
            $assoc[$header] = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
        }

        return $assoc;
    }

    private function rowIsEmpty(array $assoc): bool
    {
        foreach ($assoc as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function pickValue(array $row, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $row) && trim((string) $row[$alias]) !== '') {
                return trim((string) $row[$alias]);
            }
        }

        return null;
    }

    private function buildNameIdMap(array $records): array
    {
        $map = [];
        foreach ($records as $record) {
            $name = (string) ($record->name ?? '');
            $key = $this->normalizeHeaderKey($name);
            if ($key !== '') {
                $map[$key] = (int) $record->id;
            }
        }

        return $map;
    }

    private function resolveCatalogId(?string $value, array $nameMap): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $key = $this->normalizeHeaderKey($value);

        return $nameMap[$key] ?? null;
    }

    private function parseImportRow(array $row, array $statusMap, array $categoryMap, array $companyMap, array $sedeMap, array $userLookup): array
    {
        $errors = [];
        $warnings = [];

        $serialRaw = $this->pickValue($row, ['serie', 'serial', 'numero_serie', 'no_serie']);
        $serial = $this->normalizeAssetIdentityValue($serialRaw);
        if (! $serial) {
            $errors[] = 'Serie vacía';
        }

        $asset = $serial ? InvAsset::query()->where('serial', $serial)->first() : null;
        $isCreate = ! $asset;

        $name = $this->pickValue($row, ['nombre', 'equipo', 'nombre_equipo']) ?? '';
        if ($isCreate && trim($name) === '') {
            $errors[] = 'Nombre requerido para crear';
        }

        $internalTag = $this->normalizeAssetIdentityValue($this->pickValue($row, ['etiqueta', 'tag', 'tag_interno', 'internal_tag']));
        $statusInput = $this->pickValue($row, ['status', 'estatus', 'estado']);
        $categoryInput = $this->pickValue($row, ['categoria', 'category']);
        $companyInput = $this->pickValue($row, ['empresa', 'compania', 'company']);
        $sedeInput = $this->pickValue($row, ['sede', 'sucursal']);
        $notes = $this->pickValue($row, ['observa', 'observaciones', 'notas', 'notes']);
        $owner = $this->pickValue($row, ['owner', 'dueno', 'propietario']);
        $medio = $this->pickValue($row, ['medio', 'canal']);
        $assignedPersonInput = $this->pickValue($row, ['persona_asignada', 'persona_responsable', 'responsable', 'empleado', 'no_empleado', 'usuario_responsable', 'email_responsable']);
        $assignedUserId = $this->resolveUserIdFromImport($assignedPersonInput, $userLookup);

        $statusId = $this->resolveCatalogId($statusInput, $statusMap) ?: ($this->import_default_status_id ? (int) $this->import_default_status_id : null);
        $categoryId = $this->resolveCatalogId($categoryInput, $categoryMap) ?: ($this->import_default_category_id ? (int) $this->import_default_category_id : null);
        $companyId = $this->resolveCatalogId($companyInput, $companyMap) ?: ($this->import_default_company_id ? (int) $this->import_default_company_id : null);
        $sedeId = $this->resolveCatalogId($sedeInput, $sedeMap);
        $labelId = $this->resolveActiveLabelIdFromSede($sedeId);

        if ($statusInput && ! $statusId) {
            $errors[] = 'Status no reconocido';
        }
        if ($categoryInput && ! $categoryId) {
            $errors[] = 'Categoría no reconocida';
        }
        if ($companyInput && ! $companyId) {
            $errors[] = 'Empresa no reconocida';
        }
        if ($sedeInput && ! $sedeId) {
            $errors[] = 'Sede no reconocida';
        }
        if ($sedeId && ! $labelId) {
            $errors[] = 'Sede sin etiqueta activa en catálogo';
        }
        if ($assignedPersonInput && ! $assignedUserId) {
            $warnings[] = 'Persona asignada no encontrada (se importa sin responsable)';
        }

        if ($isCreate) {
            if (! $categoryId) {
                $errors[] = 'Falta categoría (columna o valor por defecto)';
            }
            if (! $companyId) {
                $errors[] = 'Falta empresa (columna o valor por defecto)';
            }
            if (! $statusId) {
                $errors[] = 'Falta estatus (columna o valor por defecto)';
            }
        }

        $ip = $this->pickValue($row, ['ip']);
        $mac = $this->pickValue($row, ['mac']);
        $brand = $this->pickValue($row, ['marca', 'brand']);
        $model = $this->pickValue($row, ['modelo', 'model']);

        $cost = $this->parseMoney($this->pickValue($row, ['costo', 'coste', 'cost']));
        $purchaseDate = $this->parseImportDate($this->pickValue($row, ['fecha_ingreso', 'fecha_de_ingreso', 'fecha', 'purchase_date']));

        return [
            'asset_id' => $asset?->id,
            'action' => $isCreate ? 'CREAR' : 'ACTUALIZAR',
            'errors' => $errors,
            'warnings' => $warnings,
            'serial' => $serial,
            'name' => trim((string) $name),
            'internal_tag' => $internalTag,
            'status_id' => $statusId,
            'category_id' => $categoryId,
            'company_id' => $companyId,
            'sede_id' => $sedeId,
            'label_id' => $labelId,
            'assigned_user_id' => $assignedUserId,
            'notes' => $notes,
            'brand' => $brand,
            'model' => $model,
            'ip' => $ip,
            'mac' => $mac,
            'owner' => $owner,
            'medio' => $medio,
            'cost' => $cost,
            'purchase_date' => $purchaseDate,
        ];
    }

    private function normalizePersonKey(?string $value): string
    {
        return Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();
    }

    private function buildUserLookupMaps(): array
    {
        $maps = [
            'by_usuario' => [],
            'by_email' => [],
            'by_name' => [],
        ];

        $users = User::query()
            ->withTrashed()
            ->select('id', 'usuario', 'email', 'name', 'ap_paterno', 'ap_materno')
            ->get();

        foreach ($users as $user) {
            if ($user->usuario) {
                $maps['by_usuario'][$this->normalizePersonKey($user->usuario)] = (int) $user->id;
            }
            if ($user->email) {
                $maps['by_email'][$this->normalizePersonKey($user->email)] = (int) $user->id;
            }
            $fullName = trim(implode(' ', array_filter([$user->name, $user->ap_paterno, $user->ap_materno])));
            if ($fullName !== '') {
                $maps['by_name'][$this->normalizePersonKey($fullName)] = (int) $user->id;
            }
        }

        return $maps;
    }

    private function resolveUserIdFromImport(?string $value, array $lookup): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $key = $this->normalizePersonKey($value);
        if ($key === '') {
            return null;
        }

        return $lookup['by_usuario'][$key]
            ?? $lookup['by_email'][$key]
            ?? $lookup['by_name'][$key]
            ?? null;
    }

    private function parseMoney(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9.\-]/', '', str_replace(',', '.', (string) $value));
        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }

    private function parseImportDate(?string $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $ts = strtotime((string) $value);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d', $ts);
    }

    private function resolveActiveLabelIdFromSede(?int $sedeId): ?int
    {
        if (! $sedeId) {
            return null;
        }

        return InvLabel::query()
            ->where('sede_id', $sedeId)
            ->where('is_active', true)
            ->value('id');
    }

    private function mergeSpecsFromImport(array $current, array $row): array
    {
        $specs = $current;
        foreach (['brand' => 'marca', 'model' => 'modelo', 'ip' => 'ip', 'mac' => 'mac', 'owner' => 'owner', 'medio' => 'medio'] as $key => $specKey) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                $specs[$specKey] = $value;
            }
        }

        return $specs;
    }

    // =================================================================
    //  LOGICA ASIGNACIÓN
    // =================================================================
    public function openAssignment($id)
    {
        $this->resetAssignment();
        $this->assign_asset_id = $id;
        $this->assign_date = now()->format('Y-m-d');
        $this->users_list = User::select('id', 'name', 'ap_paterno', 'ap_materno')->orderBy('name')->get();
        $this->showAssignModal = true;
    }

    public function storeAssignment()
    {
        $this->validate([
            'assign_user_id' => 'required',
            'assign_date' => 'required|date',
            'assign_reason' => 'required|max:255',
            'assign_notes' => 'nullable|max:500',
            'assign_responsiva' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
        $asset = InvAsset::with('status')->find($this->assign_asset_id);
        if (!$asset) {
            $this->addError('assign_user_id', 'Activo no encontrado.');
            return;
        }
        if (!$asset->status || !$asset->status->assignable) {
            $this->addError('assign_user_id', 'Este activo no puede asignarse con su estado actual («' . ($asset->status->name ?? 'N/A') . '»). Cambie el estatus primero.');
            return;
        }

        $responsivaPath = $this->storeResponsiva($this->assign_responsiva);

        $statusAsignado = InvStatus::where('name', 'LIKE', '%ASIGNADO%')->first();
        $newStatusId = $statusAsignado ? $statusAsignado->id : $asset->status_id;

        $previousUserId = $asset->current_user_id;
        $asset->update(['current_user_id' => $this->assign_user_id, 'status_id' => $newStatusId]);
        $mov = InvMovement::create([
            'asset_id' => $asset->id,
            'type' => 'CHECKOUT',
            'user_id' => $this->assign_user_id,
            'previous_user_id' => $previousUserId,
            'admin_id' => auth()->id() ?? 1,
            'date' => $this->assign_date,
            'reason' => $this->assign_reason,
            'notes' => $this->assign_notes,
            'responsiva_path' => $responsivaPath,
        ]);
        LogsInventoryAssignmentActivity::record($mov, $asset);

        session()->flash('message', 'Equipo asignado.');
        $this->closeAssignment();
    }

    public function closeAssignment() { $this->showAssignModal = false; $this->resetAssignment(); }

    private function resetAssignment()
    {
        $this->assign_asset_id = null;
        $this->assign_user_id = null;
        $this->assign_notes = '';
        $this->assign_date = null;
        $this->assign_reason = '';
        $this->assign_responsiva = null;
    }

    private function storeResponsiva($file): ?string
    {
        if (!$file) {
            return null;
        }

        $name = uniqid('resp_') . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('inventory/responsivas', $name, 'public');
    }

    public function openBulkAssignment(): void
    {
        if (count($this->selected) < 1) {
            return;
        }
        $this->resetErrorBag();
        $this->bulk_assign_user_id = null;
        $this->bulk_assign_notes = '';
        $this->bulk_assign_date = now()->format('Y-m-d');
        $this->bulk_assign_reason = '';
        $this->bulk_assign_responsiva = null;
        $this->users_list = User::select('id', 'name', 'ap_paterno', 'ap_materno')->orderBy('name')->get();
        $this->showBulkAssignModal = true;
    }

    public function closeBulkAssignment(): void
    {
        $this->showBulkAssignModal = false;
        $this->bulk_assign_user_id = null;
        $this->bulk_assign_notes = '';
        $this->bulk_assign_date = null;
        $this->bulk_assign_reason = '';
        $this->bulk_assign_responsiva = null;
    }

    public function storeBulkAssignment(): void
    {
        $this->validate(
            [
                'bulk_assign_user_id' => 'required|exists:users,id',
                'bulk_assign_date' => 'required|date',
                'bulk_assign_reason' => 'required|max:255',
                'bulk_assign_notes' => 'nullable|max:500',
                'bulk_assign_responsiva' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ],
            [
                'bulk_assign_user_id.required' => 'Seleccione el usuario responsable.',
                'bulk_assign_date.required' => 'La fecha es obligatoria.',
                'bulk_assign_reason.required' => 'El motivo es obligatorio.',
            ]
        );

        $ids = collect($this->selected)->map(fn ($id) => (int) $id)->filter()->values()->all();
        if ($ids === []) {
            session()->flash('error', 'No hay activos seleccionados.');
            $this->closeBulkAssignment();

            return;
        }

        $responsivaPath = $this->storeResponsiva($this->bulk_assign_responsiva);

        $assets = InvAsset::with('status')->whereIn('id', $ids)->get();
        $statusAsignado = InvStatus::where('name', 'LIKE', '%ASIGNADO%')->first();
        $batchUuid = (string) Str::uuid();

        $ok = 0;
        $skipped = [];

        foreach ($assets as $asset) {
            if (! $asset->status || ! $asset->status->assignable) {
                $skipped[] = ($asset->internal_tag ?: '#'.$asset->id).' (estatus no asignable)';

                continue;
            }
            $newStatusId = $statusAsignado ? $statusAsignado->id : $asset->status_id;
            $previousUserId = $asset->current_user_id;
            $asset->update([
                'current_user_id' => $this->bulk_assign_user_id,
                'status_id' => $newStatusId,
            ]);
            $mov = InvMovement::create([
                'asset_id' => $asset->id,
                'type' => 'CHECKOUT',
                'user_id' => $this->bulk_assign_user_id,
                'previous_user_id' => $previousUserId,
                'admin_id' => auth()->id() ?? 1,
                'date' => $this->bulk_assign_date,
                'reason' => $this->bulk_assign_reason,
                'notes' => $this->bulk_assign_notes ?: 'Asignación masiva.',
                'responsiva_path' => $responsivaPath,
                'batch_uuid' => $batchUuid,
                'metadata' => ['operation' => 'bulk_checkout', 'selected_count' => count($ids)],
            ]);
            LogsInventoryAssignmentActivity::record($mov, $asset);
            $ok++;
        }

        if ($ok === 0) {
            session()->flash('error', 'Ningún activo pudo asignarse. Verifique que el estatus de cada uno permita asignación.');
            $this->closeBulkAssignment();

            return;
        }

        $msg = "Se asignaron {$ok} activo(s) al responsable seleccionado.";
        if (count($skipped) > 0) {
            $msg .= ' Omitidos: '.count($skipped).' (revisar estatus).';
        }
        session()->flash('message', $msg);
        $this->closeBulkAssignment();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function openBulkCheckin(): void
    {
        if (count($this->selected) < 1) {
            return;
        }
        $this->resetErrorBag();
        $this->bulk_checkin_notes = '';
        $this->bulk_checkin_date = now()->format('Y-m-d');
        $this->bulk_checkin_reason = '';
        $this->bulk_checkin_responsiva = null;
        $this->showBulkCheckinModal = true;
    }

    public function closeBulkCheckin(): void
    {
        $this->showBulkCheckinModal = false;
        $this->bulk_checkin_notes = '';
        $this->bulk_checkin_date = null;
        $this->bulk_checkin_reason = '';
        $this->bulk_checkin_responsiva = null;
    }

    public function storeBulkCheckin(): void
    {
        $this->validate([
            'bulk_checkin_date' => 'required|date',
            'bulk_checkin_reason' => 'required|max:255',
            'bulk_checkin_notes' => 'nullable|max:500',
            'bulk_checkin_responsiva' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $ids = collect($this->selected)->map(fn ($id) => (int) $id)->filter()->values()->all();
        if ($ids === []) {
            session()->flash('error', 'No hay activos seleccionados.');
            $this->closeBulkCheckin();

            return;
        }

        $responsivaPath = $this->storeResponsiva($this->bulk_checkin_responsiva);

        $statusDisponible = InvStatus::where('name', 'LIKE', '%DISPONIBLE%')->orWhere('name', 'LIKE', '%LIBRE%')->first();
        if (! $statusDisponible) {
            $statusDisponible = InvStatus::where('assignable', true)->first();
        }

        $assets = InvAsset::whereIn('id', $ids)->get();
        $batchUuid = (string) Str::uuid();
        $ok = 0;
        $skipped = [];

        foreach ($assets as $asset) {
            if (! $asset->current_user_id) {
                $skipped[] = ($asset->internal_tag ?: '#'.$asset->id).' (sin responsable)';

                continue;
            }
            $newStatusId = $statusDisponible ? $statusDisponible->id : $asset->status_id;
            $previousUserId = $asset->current_user_id;
            $asset->update([
                'current_user_id' => null,
                'status_id' => $newStatusId,
            ]);
            $mov = InvMovement::create([
                'asset_id' => $asset->id,
                'type' => 'CHECKIN',
                'user_id' => null,
                'previous_user_id' => $previousUserId,
                'admin_id' => auth()->id() ?? 1,
                'date' => $this->bulk_checkin_date,
                'reason' => $this->bulk_checkin_reason,
                'notes' => $this->bulk_checkin_notes ?: 'Devolución registrada.',
                'responsiva_path' => $responsivaPath,
                'batch_uuid' => $batchUuid,
                'metadata' => ['operation' => 'bulk_checkin', 'selected_count' => count($ids)],
            ]);
            LogsInventoryAssignmentActivity::record($mov, $asset);
            $ok++;
        }

        if ($ok === 0) {
            session()->flash('error', 'Ningún activo tenía responsable asignado; no se aplicó devolución masiva.');
            $this->closeBulkCheckin();

            return;
        }

        $msg = "Se desasignaron / devolvieron {$ok} activo(s).";
        if (count($skipped) > 0) {
            $msg .= ' Omitidos: '.count($skipped).'.';
        }
        session()->flash('message', $msg);
        $this->closeBulkCheckin();
        $this->selected = [];
        $this->selectAll = false;
    }

    // =================================================================
    //  DEVOLUCIÓN (CHECK-IN)
    // =================================================================
    public function openCheckinModal($id)
    {
        $this->checkin_asset_id = $id;
        $this->checkin_notes = '';
        $this->checkin_date = now()->format('Y-m-d');
        $this->checkin_reason = '';
        $this->checkin_responsiva = null;
        $this->showCheckinModal = true;
    }

    public function storeCheckin()
    {
        $this->validate([
            'checkin_date' => 'required|date',
            'checkin_reason' => 'required|max:255',
            'checkin_notes' => 'nullable|max:500',
            'checkin_responsiva' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $asset = InvAsset::find($this->checkin_asset_id);
        if (!$asset) {
            session()->flash('error', 'Activo no encontrado.');
            return;
        }
        $statusDisponible = InvStatus::where('name', 'LIKE', '%DISPONIBLE%')->orWhere('name', 'LIKE', '%LIBRE%')->first();
        if (!$statusDisponible) {
            $statusDisponible = InvStatus::where('assignable', true)->first();
        }
        $newStatusId = $statusDisponible ? $statusDisponible->id : $asset->status_id;

        $responsivaPath = $this->storeResponsiva($this->checkin_responsiva);

        $previousUserId = $asset->current_user_id;
        $asset->update(['current_user_id' => null, 'status_id' => $newStatusId]);
        $mov = InvMovement::create([
            'asset_id' => $asset->id,
            'type' => 'CHECKIN',
            'user_id' => null,
            'previous_user_id' => $previousUserId,
            'admin_id' => auth()->id() ?? 1,
            'date' => $this->checkin_date,
            'reason' => $this->checkin_reason,
            'notes' => $this->checkin_notes ?: 'Devolución registrada.',
            'responsiva_path' => $responsivaPath,
        ]);
        LogsInventoryAssignmentActivity::record($mov, $asset);
        session()->flash('message', 'Equipo devuelto correctamente.');
        $this->closeCheckinModal();
    }

    public function closeCheckinModal()
    {
        $this->showCheckinModal = false;
        $this->checkin_asset_id = null;
        $this->checkin_notes = '';
        $this->checkin_date = null;
        $this->checkin_reason = '';
        $this->checkin_responsiva = null;
    }

    // =================================================================
    //  BAJA / DESMANTELADO
    // =================================================================
    public function openBajaModal($id)
    {
        $this->baja_asset_id = $id;
        $this->baja_status_id = '';
        $this->baja_notes = '';
        $this->showBajaModal = true;
    }

    public function storeBaja()
    {
        $this->validate(['baja_status_id' => 'required|exists:inv_statuses,id'], ['baja_status_id.required' => 'Seleccione un estatus de baja.']);
        $asset = InvAsset::find($this->baja_asset_id);
        if (!$asset) {
            session()->flash('error', 'Activo no encontrado.');
            return;
        }
        $previousUserId = $asset->current_user_id;
        $asset->update(['status_id' => $this->baja_status_id, 'current_user_id' => null]);
        $mov = InvMovement::create([
            'asset_id' => $asset->id,
            'type' => 'BAJA',
            'user_id' => null,
            'previous_user_id' => $previousUserId,
            'admin_id' => auth()->id() ?? 1,
            'date' => now(),
            'notes' => $this->baja_notes ?: 'Baja registrada.',
        ]);
        LogsInventoryAssignmentActivity::record($mov, $asset);
        session()->flash('message', 'Activo dado de baja.');
        $this->showBajaModal = false;
        $this->baja_asset_id = null;
        $this->baja_status_id = '';
        $this->baja_notes = '';
    }

    public function closeBajaModal() { $this->showBajaModal = false; $this->baja_asset_id = null; $this->baja_status_id = ''; $this->baja_notes = ''; }

    // =================================================================
    //  TRASLADO
    // =================================================================
    public function openTrasladoModal($id)
    {
        $asset = InvAsset::with(['sede', 'ubicacion'])->find($id);
        if (!$asset) return;
        $this->traslado_asset_id = $id;
        $this->traslado_origen_sede = $asset->sede->sede ?? 'Sin sede';
        $this->traslado_origen_ubicacion = $asset->ubicacion->ubicacion ?? 'Sin ubicación';
        $this->traslado_sede_id = $asset->sede_id;
        $this->traslado_ubicacion_id = $asset->ubicacion_id;
        $this->traslado_ubicaciones = $asset->sede_id ? Ubicacion::where('id_sede', $asset->sede_id)->get() : [];
        $this->traslado_date = now()->format('Y-m-d');
        $this->traslado_reason = '';
        $this->traslado_notes = '';
        $this->showTrasladoModal = true;
    }

    public function updatedTrasladoSedeId($value)
    {
        $this->traslado_ubicaciones = $value ? Ubicacion::where('id_sede', $value)->get() : [];
        $this->traslado_ubicacion_id = null;
    }

    public function storeTraslado()
    {
        $this->validate([
            'traslado_sede_id' => 'nullable',
            'traslado_ubicacion_id' => 'nullable',
            'traslado_date' => 'required|date',
            'traslado_reason' => 'required|max:255',
            'traslado_notes' => 'nullable|max:500',
        ]);
        $asset = InvAsset::with(['sede', 'ubicacion'])->find($this->traslado_asset_id);
        if (!$asset) {
            session()->flash('error', 'Activo no encontrado.');
            return;
        }

        $origenSede = $asset->sede->sede ?? 'Sin sede';
        $origenUbi = $asset->ubicacion->ubicacion ?? 'Sin ubicación';
        $destinoSede = $this->traslado_sede_id ? (Sede::find($this->traslado_sede_id)->sede ?? '—') : 'Sin sede';
        $destinoUbi = $this->traslado_ubicacion_id ? (Ubicacion::find($this->traslado_ubicacion_id)->ubicacion ?? '—') : 'Sin ubicación';

        $asset->update([
            'sede_id' => $this->traslado_sede_id ?: null,
            'ubicacion_id' => $this->traslado_ubicacion_id ?: null,
        ]);
        InvMovement::create([
            'asset_id' => $asset->id,
            'type' => 'TRASLADO',
            'user_id' => null,
            'admin_id' => auth()->id() ?? 1,
            'date' => $this->traslado_date,
            'reason' => $this->traslado_reason,
            'notes' => "De: {$origenSede} / {$origenUbi} → A: {$destinoSede} / {$destinoUbi}" . ($this->traslado_notes ? ". {$this->traslado_notes}" : ''),
        ]);
        session()->flash('message', 'Traslado registrado.');
        $this->closeTrasladoModal();
    }

    public function closeTrasladoModal()
    {
        $this->showTrasladoModal = false;
        $this->traslado_asset_id = null;
        $this->traslado_sede_id = null;
        $this->traslado_ubicacion_id = null;
        $this->traslado_ubicaciones = [];
        $this->traslado_origen_sede = '';
        $this->traslado_origen_ubicacion = '';
        $this->traslado_date = null;
        $this->traslado_reason = '';
        $this->traslado_notes = '';
    }

    // =================================================================
    //  DESPIECE / CANIBALIZACIÓN
    // =================================================================
    public function openDespieceModal($assetId)
    {
        $this->despieceAsset = InvAsset::with([
            'components' => function ($q) {
                $q->where('status', '!=', 'SUSPENDIDO')
                  ->with('originAsset');
            }
        ])->findOrFail($assetId);

        $this->despieceComponents = $this->despieceAsset->components->toArray();
        $this->selectedForExtraction = [];
        $this->despieceNotes = '';
        $this->showDespieceModal = true;
    }

    public function toggleAllDespieceComponents()
    {
        $ids = collect($this->despieceComponents)->pluck('id')->map(function ($id) {
            return (string) $id;
        })->toArray();
        if (count($this->selectedForExtraction) === count($ids) && count($ids) > 0) {
            $this->selectedForExtraction = [];
        } else {
            $this->selectedForExtraction = $ids;
        }
    }

    public function confirmDespiece()
    {
        if (empty($this->selectedForExtraction)) {
            session()->flash('error',
                'Debes seleccionar al menos un componente para extraer.');
            return;
        }

        DB::transaction(function () {
            foreach ($this->selectedForExtraction as $componentId) {
                $component = InvComponent::findOrFail($componentId);

                // origin_asset_id es INMUTABLE: solo se asigna si está vacío
                $originAssetId = $component->origin_asset_id
                    ?? $this->despieceAsset->id;

                $component->update([
                    'asset_id'        => null,
                    'status'          => 'STOCK',
                    'origin_asset_id' => $originAssetId,
                ]);

                InvComponentMovement::create([
                    'component_id'    => $component->id,
                    'asset_id'        => null,
                    'origin_asset_id' => $this->despieceAsset->id,
                    'admin_id'        => auth()->id() ?? 1,
                    'type'            => 'EXTRACCION',
                    'date'            => now(),
                    'notes'           => $this->despieceNotes ?: null,
                ]);
            }

            InvMovement::create([
                'asset_id' => $this->despieceAsset->id,
                'user_id'  => null,
                'admin_id' => auth()->id() ?? 1,
                'type'     => 'DESPIECE',
                'date'     => now(),
                'notes'    => 'Despiece: '
                    . count($this->selectedForExtraction)
                    . ' componente(s) extraído(s).'
                    . ($this->despieceNotes ? ' ' . $this->despieceNotes : ''),
            ]);
        });

        $this->showDespieceModal = false;
        $this->despieceAsset = null;
        $this->despieceComponents = [];
        $this->selectedForExtraction = [];
        $this->despieceNotes = '';

        session()->flash('message', 'Despiece registrado correctamente.');
    }

    // =================================================================
    //  LOGICA EDICIÓN MASIVA (ACTUALIZADO)
    // =================================================================
    /**
     * Combo "Responsable": solo usuarios con al menos un activo asignado que cumpla
     * búsqueda, categoría, estatus y sede (sin filtrar por user_filter).
     */
    private function listFiltersEffective(): array
    {
        $u = Auth::user();

        return [
            'search' => (string) InventoryV2FilterPermissions::effectiveScalar($u, 'search', $this->search, ''),
            'category' => InventoryV2FilterPermissions::userMayUse($u, 'category') ? $this->category_filter : '',
            'status' => InventoryV2FilterPermissions::userMayUse($u, 'status') ? $this->status_filter : '',
            'sede' => InventoryV2FilterPermissions::userMayUse($u, 'sede') ? $this->sede_filter : '',
            'user' => InventoryV2FilterPermissions::userMayUse($u, 'assignee') ? $this->user_filter : '',
            'label' => in_array((string) $this->label_filter, ['missing', 'with'], true) ? (string) $this->label_filter : '',
        ];
    }

    private function assigneesForInventoryResponsibleFilter()
    {
        $f = $this->listFiltersEffective();

        $ids = InvAsset::query()
            ->whereNotNull('current_user_id')
            ->when($f['search'] !== '', function ($q) use ($f) {
                $term = '%'.$f['search'].'%';
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('internal_tag', 'like', $term)
                        ->orWhere('serial', 'like', $term);
                });
            })
            ->when($f['category'], fn ($q) => $q->where('category_id', $f['category']))
            ->when($f['status'], fn ($q) => $q->where('status_id', $f['status']))
            ->when($f['sede'], fn ($q) => $q->where('sede_id', $f['sede']))
            ->when($f['label'] === 'missing', fn ($q) => $q->whereNull('label_id'))
            ->when($f['label'] === 'with', fn ($q) => $q->whereNotNull('label_id'))
            ->when($this->export_date_from !== '', fn ($q) => $q->whereDate($this->exportDateColumn(), '>=', $this->export_date_from))
            ->when($this->export_date_to !== '', fn ($q) => $q->whereDate($this->exportDateColumn(), '<=', $this->export_date_to))
            ->distinct()
            ->pluck('current_user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($f['user'] !== '' && $f['user'] !== null) {
            $uid = (int) $f['user'];
            if (! $ids->contains($uid)) {
                $ids = $ids->push($uid)->unique()->values();
            }
        }

        if ($ids->isEmpty()) {
            return User::query()->whereRaw('1 = 0')->get();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->withTrashed()
            ->orderBy('name')
            ->orderBy('ap_paterno')
            ->get();
    }

    private function getQuery()
    {
        $f = $this->listFiltersEffective();

        return InvAsset::query()
            ->when($f['search'] !== '', function ($q) use ($f) {
                $term = '%' . $f['search'] . '%';
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)->orWhere('internal_tag', 'like', $term)->orWhere('serial', 'like', $term);
                });
            })
            ->when($f['category'], fn ($q) => $q->where('category_id', $f['category']))
            ->when($f['status'], fn ($q) => $q->where('status_id', $f['status']))
            ->when($f['sede'], fn ($q) => $q->where('sede_id', $f['sede']))
            ->when($f['label'] === 'missing', fn ($q) => $q->whereNull('label_id'))
            ->when($f['label'] === 'with', fn ($q) => $q->whereNotNull('label_id'))
            ->when($this->export_date_from !== '', fn ($q) => $q->whereDate($this->exportDateColumn(), '>=', $this->export_date_from))
            ->when($this->export_date_to !== '', fn ($q) => $q->whereDate($this->exportDateColumn(), '<=', $this->export_date_to))
            ->when($f['user'] !== '' && $f['user'] !== null, fn ($q) => $q->where('current_user_id', (int) $f['user']));
    }

    private function exportDateColumn(): string
    {
        return in_array($this->export_date_field, ['created_at', 'purchase_date'], true)
            ? $this->export_date_field
            : 'created_at';
    }

    public function updatedSelectAll($value)
    {
        $this->selected = $value ? $this->getQuery()->limit(500)->pluck('id')->map(fn($id) => (string)$id)->toArray() : [];
    }

    public function openBulkEdit()
    {
        if (count($this->selected) < 1) return;
        
        // Reseteamos las variables masivas
        $this->bulk_category_id = '';
        $this->bulk_status_id = '';
        $this->bulk_company_id = '';
        $this->bulk_sede_id = '';
        $this->bulk_ubicacion_id = '';
        $this->bulk_ubicaciones = []; // Limpiamos lista de ubicaciones masiva
        
        $this->showBulkModal = true;
    }

    public function saveBulk()
    {
        $this->validate([
            'bulk_category_id' => 'nullable',
            'bulk_status_id' => 'nullable',
            'bulk_company_id' => 'nullable',
            'bulk_sede_id' => 'nullable',
            'bulk_ubicacion_id' => 'nullable',
        ]);

        // Verificamos si al menos un campo tiene valor
        if (!$this->bulk_category_id && !$this->bulk_status_id && !$this->bulk_company_id && !$this->bulk_sede_id && !$this->bulk_ubicacion_id) {
            $this->addError('bulk_error', 'Selecciona al menos una opción para cambiar.');
            return;
        }

        $dataToUpdate = [];
        if ($this->bulk_category_id) $dataToUpdate['category_id'] = $this->bulk_category_id;
        if ($this->bulk_status_id)   $dataToUpdate['status_id']   = $this->bulk_status_id;
        if ($this->bulk_company_id)  $dataToUpdate['company_id']  = $this->bulk_company_id;
        if ($this->bulk_sede_id)     $dataToUpdate['sede_id']     = $this->bulk_sede_id;
        if ($this->bulk_ubicacion_id) $dataToUpdate['ubicacion_id'] = $this->bulk_ubicacion_id;

        InvAsset::whereIn('id', $this->selected)->update($dataToUpdate);

        session()->flash('message', 'Se actualizaron ' . count($this->selected) . ' activos.');
        $this->closeBulkModal();
        $this->resetSelection();
    }

    public function closeBulkModal() { $this->showBulkModal = false; }

    public function deleteSelected()
    {
        if (count($this->selected) > 0) {
            InvAsset::whereIn('id', $this->selected)->delete();
            session()->flash('message', count($this->selected) . ' activos eliminados.');
            $this->resetSelection();
        }
    }

    public function openConfirmDeleteAsset($id)
    {
        $this->confirmAction = 'deleteAsset';
        $this->confirmTargetId = $id;
        $this->confirmTitle = 'Eliminar activo';
        $this->confirmMessage = '¿Está seguro de eliminar este activo? Esta acción puede deshacerse desde el administrador.';
        $this->confirmButtonText = 'Sí, eliminar';
        $this->confirmButtonClass = 'btn-danger';
        $this->showConfirmModal = true;
    }

    public function openConfirmDeleteSelected()
    {
        if (count($this->selected) < 1) return;
        $this->confirmAction = 'deleteSelected';
        $this->confirmTargetId = null;
        $this->confirmTitle = 'Eliminar seleccionados';
        $this->confirmMessage = '¿Está seguro de eliminar los ' . count($this->selected) . ' activos seleccionados?';
        $this->confirmButtonText = 'Sí, eliminar';
        $this->confirmButtonClass = 'btn-danger';
        $this->showConfirmModal = true;
    }

    public function confirmModalConfirm()
    {
        if ($this->confirmAction === 'deleteAsset' && $this->confirmTargetId) {
            $this->deleteAsset($this->confirmTargetId);
        } elseif ($this->confirmAction === 'deleteSelected') {
            $this->deleteSelected();
        }
        $this->confirmModalCancel();
    }

    public function confirmModalCancel()
    {
        $this->showConfirmModal = false;
        $this->confirmAction = '';
        $this->confirmTargetId = null;
    }

    // =================================================================
    //  RENDER
    // =================================================================
    public function render()
    {
        $assets = $this->getQuery()
            ->with(['category', 'status', 'label', 'ubicacion', 'sede', 'currentUser', 'company'])
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $assigneeOptions = $this->assigneesForInventoryResponsibleFilter();

        return view('livewire.inventory.inventory-index', [
            'assets' => $assets,
            'categories' => InvCategory::select('id', 'name')->orderBy('name')->get(),
            'statuses' => InvStatus::select('id', 'name', 'badge_class')->orderBy('name')->get(),
            'assigneeOptions' => $assigneeOptions,
            'activeLabels' => $this->activeLabelsForForm(),
        ])
        ->extends('admin.layout', ['title' => ' | Inventario V2'])
        ->section('content');
    }

    //Fotos metodos

    public function openPhotoModal($id)
    {
        $this->asset_id_for_photos = $id;
        $this->loadStoredPhotos();
        $this->reset('evidence_photos');
        $this->showPhotoModal = true;
    }

    public function loadStoredPhotos()
    {
        $this->stored_photos = InvAssetImage::where('inv_asset_id', $this->asset_id_for_photos)->get();
    }

    public function savePhotos()
    {
        $this->validate([
            'evidence_photos.*' => 'image|max:10240', // Max 10MB
        ]);

        foreach ($this->evidence_photos as $photo) {
            // Se guardan en storage/app/public/assets_evidence
            $path = $photo->store('assets_evidence', 'public');

            InvAssetImage::create([
                'inv_asset_id' => $this->asset_id_for_photos,
                'path' => $path
            ]);
        }

        $this->loadStoredPhotos();
        $this->reset('evidence_photos');
        session()->flash('message', 'Evidencia subida correctamente.');
    }

    public function deletePhoto($id)
    {
        $img = InvAssetImage::find($id);
        if($img){
            // Opcional: Borrar archivo del disco
            // \Storage::disk('public')->delete($img->path);
            $img->delete();
            $this->loadStoredPhotos();
        }
    }

    public function closePhotoModal() { $this->showPhotoModal = false; }

    /** Eliminar un solo activo (soft delete). Llamado desde modal de confirmación o directamente. */
    public function deleteAsset($id)
    {
        $asset = InvAsset::find($id);
        if ($asset) {
            $asset->delete();
            session()->flash('message', 'Activo eliminado.');
            $this->resetSelection();
        }
    }

}



