<?php

namespace App\Support\Inventory;

use App\Models\Company;
use App\Models\Department;
use App\Models\InvAsset;
use App\Models\InvCategory;
use App\Models\InvLabel;
use App\Models\InvMovement;
use App\Models\InvStatus;
use App\Models\Position;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Spatie\Permission\Models\Role;

class InventoryImportMatcher
{
    private array $labelNameCache = [];
    private array $autoCreatedUsersCache = [];
    private ?array $defaultOrgCache = null;

    public function normalizeHeaderKey($value): string
    {
        return Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();
    }

    public function associateRowByHeaders(array $headers, array $row): array
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

    public function rowIsEmpty(array $assoc): bool
    {
        foreach ($assoc as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    public function parseRow(array $row, array $options = []): array
    {
        $errors = [];
        $warnings = [];

        $statusMap = $options['status_map'] ?? $this->buildNameIdMap(InvStatus::query()->get(['id', 'name']));
        $categoryMap = $options['category_map'] ?? $this->buildNameIdMap(InvCategory::query()->get(['id', 'name']));
        $companyMap = $options['company_map'] ?? $this->buildNameIdMap(Company::query()->get(['id', 'name']));
        $sedeMap = $options['sede_map'] ?? $this->buildNameIdMap(Sede::query()->get(['id', 'sede as name']));
        $userLookup = $options['user_lookup'] ?? $this->buildUserLookupMaps();

        $defaultStatusId = (int) ($options['default_status_id'] ?? 0);
        $defaultCategoryId = (int) ($options['default_category_id'] ?? 0);
        $defaultCompanyId = (int) ($options['default_company_id'] ?? 0);

        $serial = $this->normalizeIdentity($this->pickValue($row, ['serie', 'serial', 'numero_serie', 'no_serie']));
        if (! $serial) {
            $errors[] = 'Serie vacía';
        }

        $asset = $serial ? InvAsset::query()->where('serial', $serial)->first() : null;
        $isCreate = ! $asset;

        $name = $this->pickValue($row, ['nombre', 'equipo', 'nombre_equipo']) ?? '';
        if ($isCreate && trim($name) === '') {
            $errors[] = 'Nombre requerido para crear';
        }

        $internalTag = $this->normalizeIdentity($this->pickValue($row, ['etiqueta', 'tag', 'tag_interno', 'internal_tag']));
        $statusInput = $this->pickValue($row, ['status', 'estatus', 'estado']);
        $categoryInput = $this->pickValue($row, ['categoria', 'category']);
        $companyInput = $this->pickValue($row, ['empresa', 'compania', 'company']);
        $sedeInput = $this->pickValue($row, ['sede', 'sucursal']);
        $notes = $this->pickValue($row, ['observa', 'observaciones', 'notas', 'notes']);
        $owner = $this->pickValue($row, ['owner', 'dueno', 'propietario']);
        $medio = $this->pickValue($row, ['medio', 'canal']);
        $assignedInput = $this->pickValue($row, ['persona_asignada', 'persona_responsable', 'responsable', 'empleado', 'no_empleado', 'usuario_responsable', 'email_responsable']);
        $createMissingUsers = (bool) ($options['create_missing_users'] ?? false);

        $statusId = $this->resolveCatalogId($statusInput, $statusMap) ?: ($defaultStatusId ?: null);
        $categoryId = $this->resolveCatalogId($categoryInput, $categoryMap) ?: ($defaultCategoryId ?: null);
        $companyId = $this->resolveCatalogId($companyInput, $companyMap) ?: ($defaultCompanyId ?: null);
        $sedeId = $this->resolveCatalogId($sedeInput, $sedeMap);
        $labelId = $this->resolveActiveLabelIdFromSede($sedeId);
        $assignedUserId = $this->resolveUserId($assignedInput, $userLookup);
        $labelName = $labelId ? $this->labelNameById($labelId) : null;

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
        if ($internalTag && $labelName && $this->normalizeIdentity($internalTag) === $this->normalizeIdentity($labelName)) {
            $errors[] = 'Tag interno igual a etiqueta de sede (deben ser diferentes)';
        }
        if ($assignedInput && ! $assignedUserId) {
            $warnings[] = $createMissingUsers
                ? 'Persona asignada no encontrada (se creará usuario pendiente en la importación).'
                : 'Persona asignada no encontrada (se importa sin responsable)';
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

        $parsed = [
            'asset_id' => $asset?->id,
            'action' => $isCreate ? 'CREAR' : 'ACTUALIZAR',
            'serial' => $serial,
            'name' => trim((string) $name),
            'internal_tag' => $internalTag,
            'status_id' => $statusId,
            'category_id' => $categoryId,
            'company_id' => $companyId,
            'sede_id' => $sedeId,
            'label_id' => $labelId,
            'assigned_user_id' => $assignedUserId,
            'assigned_user_raw' => $assignedInput,
            'notes' => $notes,
            'brand' => $this->pickValue($row, ['marca', 'brand']),
            'model' => $this->pickValue($row, ['modelo', 'model']),
            'ip' => $this->pickValue($row, ['ip']),
            'mac' => $this->pickValue($row, ['mac']),
            'owner' => $owner,
            'medio' => $medio,
            'cost' => $this->parseMoney($this->pickValue($row, ['costo', 'coste', 'cost'])),
            'purchase_date' => $this->parseDate($this->pickValue($row, ['fecha_ingreso', 'fecha_de_ingreso', 'fecha', 'purchase_date'])),
        ];

        return [
            'parsed' => $parsed,
            'errors' => $errors,
            'warnings' => $warnings,
            'action' => $parsed['action'],
        ];
    }

    public function upsertParsedRow(array $parsed, ?int $adminId = null, array $options = []): array
    {
        if (empty($parsed['assigned_user_id']) && ! empty($parsed['assigned_user_raw']) && ! empty($options['create_missing_users'])) {
            $parsed['assigned_user_id'] = $this->findOrCreatePendingUser((string) $parsed['assigned_user_raw']);
        }

        if (! empty($parsed['asset_id'])) {
            $asset = InvAsset::find($parsed['asset_id']);
            if (! $asset) {
                throw new \RuntimeException('Activo objetivo no encontrado para actualización.');
            }

            $specs = $this->mergeSpecs((array) ($asset->specs ?? []), $parsed);
            $updateData = array_filter([
                'name' => $parsed['name'] ?: null,
                'internal_tag' => $parsed['internal_tag'] ?: null,
                'category_id' => $parsed['category_id'] ?: null,
                'status_id' => $parsed['status_id'] ?: null,
                'label_id' => $parsed['label_id'],
                'company_id' => $parsed['company_id'] ?: null,
                'sede_id' => $parsed['sede_id'] ?: null,
                'current_user_id' => $parsed['assigned_user_id'],
                'notes' => $parsed['notes'] ?: null,
                'cost' => $parsed['cost'],
                'purchase_date' => $parsed['purchase_date'],
                'specs' => $specs,
            ], fn ($value) => $value !== null);

            $asset->update($updateData);

            return ['action' => 'ACTUALIZAR', 'asset_id' => $asset->id];
        }

        $asset = InvAsset::create([
            'uuid' => (string) Str::uuid(),
            'serial' => $parsed['serial'],
            'name' => $parsed['name'],
            'internal_tag' => $parsed['internal_tag'] ?: null,
            'category_id' => $parsed['category_id'],
            'status_id' => $parsed['status_id'],
            'label_id' => $parsed['label_id'],
            'company_id' => $parsed['company_id'],
            'sede_id' => $parsed['sede_id'] ?: null,
            'current_user_id' => $parsed['assigned_user_id'],
            'condition' => 'BUENO',
            'notes' => $parsed['notes'] ?: null,
            'cost' => $parsed['cost'] ?? 0,
            'purchase_date' => $parsed['purchase_date'],
            'specs' => $this->mergeSpecs([], $parsed),
        ]);

        InvMovement::create([
            'asset_id' => $asset->id,
            'type' => 'AUDIT',
            'admin_id' => $adminId ?: (auth()->id() ?? 1),
            'date' => now(),
            'notes' => 'Alta por importación Excel (modo crear/actualizar por serie).',
        ]);

        return ['action' => 'CREAR', 'asset_id' => $asset->id];
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

    private function normalizeIdentity(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $normalized = Str::upper(trim($value));
        return $normalized === '' ? null : $normalized;
    }

    private function buildNameIdMap(Collection $records): array
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
        return $nameMap[$this->normalizeHeaderKey($value)] ?? null;
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
        $maps = ['by_usuario' => [], 'by_email' => [], 'by_name' => []];
        $users = User::query()->withTrashed()->select('id', 'usuario', 'email', 'name', 'ap_paterno', 'ap_materno')->get();
        foreach ($users as $user) {
            if ($user->usuario) {
                $maps['by_usuario'][$this->normalizePersonKey($user->usuario)] = (int) $user->id;
            }
            if ($user->email) {
                $maps['by_email'][$this->normalizePersonKey($user->email)] = (int) $user->id;
            }
            $full = trim(implode(' ', array_filter([$user->name, $user->ap_paterno, $user->ap_materno])));
            if ($full !== '') {
                $maps['by_name'][$this->normalizePersonKey($full)] = (int) $user->id;
            }
        }
        return $maps;
    }

    private function resolveUserId(?string $value, array $lookup): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $key = $this->normalizePersonKey($value);
        return $lookup['by_usuario'][$key] ?? $lookup['by_email'][$key] ?? $lookup['by_name'][$key] ?? null;
    }

    private function findOrCreatePendingUser(string $raw): ?int
    {
        $key = $this->normalizePersonKey($raw);
        if ($key === '') {
            return null;
        }

        if (isset($this->autoCreatedUsersCache[$key])) {
            return $this->autoCreatedUsersCache[$key];
        }

        $lookup = $this->buildUserLookupMaps();
        $existingId = $lookup['by_usuario'][$key] ?? $lookup['by_email'][$key] ?? $lookup['by_name'][$key] ?? null;
        if ($existingId) {
            $this->autoCreatedUsersCache[$key] = (int) $existingId;
            return (int) $existingId;
        }

        $org = $this->resolveDefaultOrg();
        if (! $org) {
            return null;
        }

        [$name, $apPaterno, $apMaterno, $email] = $this->parseRawPerson($raw);
        $usuario = $this->buildUniqueUsuario($raw);

        $user = User::create([
            'name' => $name,
            'ap_paterno' => $apPaterno,
            'ap_materno' => $apMaterno,
            'usuario' => $usuario,
            'email' => $email,
            'phone' => null,
            'area_id' => $org['area_id'],
            'department_id' => $org['department_id'],
            'position_id' => $org['position_id'],
            'campaign_id' => null,
            'password' => Str::password(16),
            'certification' => 0,
            'motivo_baja' => null,
            'fecha_baja' => null,
        ]);

        $basicRole = Role::query()->where('name', 'Basico')->first();
        if ($basicRole) {
            $user->assignRole($basicRole);
        }

        $this->autoCreatedUsersCache[$key] = (int) $user->id;

        return (int) $user->id;
    }

    private function resolveDefaultOrg(): ?array
    {
        if ($this->defaultOrgCache !== null) {
            return $this->defaultOrgCache;
        }

        $position = Position::query()->select('id', 'department_id')->orderBy('id')->first();
        if (! $position) {
            return $this->defaultOrgCache = null;
        }

        $department = Department::query()->select('id', 'area_id')->find($position->department_id);
        if (! $department) {
            return $this->defaultOrgCache = null;
        }

        return $this->defaultOrgCache = [
            'position_id' => (int) $position->id,
            'department_id' => (int) $department->id,
            'area_id' => $department->area_id ? (int) $department->area_id : null,
        ];
    }

    private function parseRawPerson(string $raw): array
    {
        $value = trim($raw);
        $email = filter_var($value, FILTER_VALIDATE_EMAIL) ? Str::lower($value) : null;
        $clean = Str::of($value)->ascii()->replaceMatches('/\s+/', ' ')->trim()->value();

        if ($clean === '' || ctype_digit($clean)) {
            return ["PENDIENTE RH {$value}", null, null, $email];
        }

        $parts = preg_split('/\s+/', $clean) ?: [];
        $name = $parts[0] ?? 'PENDIENTE RH';
        $apPaterno = $parts[1] ?? null;
        $apMaterno = count($parts) > 2 ? implode(' ', array_slice($parts, 2)) : null;

        return [$name, $apPaterno, $apMaterno, $email];
    }

    private function buildUniqueUsuario(string $raw): string
    {
        $base = Str::of($raw)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '')
            ->substr(0, 20)
            ->value();
        if ($base === '') {
            $base = 'RH'.now()->format('YmdHis');
        }

        $candidate = $base;
        $suffix = 1;
        while (User::withTrashed()->where('usuario', $candidate)->exists()) {
            $candidate = Str::limit($base, 16, '').str_pad((string) $suffix, 4, '0', STR_PAD_LEFT);
            $suffix++;
        }

        return $candidate;
    }

    private function parseMoney(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $clean = preg_replace('/[^0-9.\-]/', '', str_replace(',', '.', $value));
        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }
        return (float) $clean;
    }

    private function parseDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        $ts = strtotime($value);
        return $ts === false ? null : date('Y-m-d', $ts);
    }

    private function resolveActiveLabelIdFromSede(?int $sedeId): ?int
    {
        if (! $sedeId) {
            return null;
        }
        return InvLabel::query()->where('sede_id', $sedeId)->where('is_active', true)->value('id');
    }

    private function labelNameById(int $id): ?string
    {
        if (array_key_exists($id, $this->labelNameCache)) {
            return $this->labelNameCache[$id];
        }

        $name = InvLabel::query()->whereKey($id)->value('name');
        $this->labelNameCache[$id] = $name ? (string) $name : null;

        return $this->labelNameCache[$id];
    }

    private function mergeSpecs(array $current, array $row): array
    {
        $specs = $current;
        foreach (['brand' => 'marca', 'model' => 'modelo', 'ip' => 'ip', 'mac' => 'mac', 'owner' => 'owner', 'medio' => 'medio'] as $k => $specKey) {
            $value = trim((string) ($row[$k] ?? ''));
            if ($value !== '') {
                $specs[$specKey] = $value;
            }
        }
        return $specs;
    }
}
