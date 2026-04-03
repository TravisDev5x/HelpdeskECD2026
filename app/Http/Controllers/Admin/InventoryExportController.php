<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvAsset;
use App\Exports\InventoryWorkbookExport;
use App\Models\InvCategory;
use App\Models\InvStatus;
use App\Models\Sede;
use App\Models\User;
use App\Support\InventoryV2FilterPermissions;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read inventory']);
    }

    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $search = InventoryV2FilterPermissions::effectiveScalar($user, 'search', $request->input('search'));
        $categoryId = InventoryV2FilterPermissions::effectiveScalar($user, 'category', $request->input('category_filter'));
        $statusId = InventoryV2FilterPermissions::effectiveScalar($user, 'status', $request->input('status_filter'));
        $sedeId = InventoryV2FilterPermissions::effectiveScalar($user, 'sede', $request->input('sede_filter'));
        $assigneeMay = InventoryV2FilterPermissions::userMayUse($user, 'assignee') && $request->filled('user_filter');
        $labelFilter = (string) $request->input('label_filter', '');
        $dateField = in_array($request->input('date_field'), ['created_at', 'purchase_date'], true)
            ? $request->input('date_field')
            : 'created_at';
        $dateFrom = (string) $request->input('date_from', '');
        $dateTo = (string) $request->input('date_to', '');

        $query = InvAsset::query()
            ->with(['category', 'status', 'label', 'company', 'sede', 'ubicacion', 'currentUser'])
            ->when($search, function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('internal_tag', 'like', $term)
                        ->orWhere('serial', 'like', $term);
                });
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($statusId, fn ($q) => $q->where('status_id', $statusId))
            ->when($sedeId, fn ($q) => $q->where('sede_id', $sedeId))
            ->when($assigneeMay, fn ($q) => $q->where('current_user_id', (int) $request->input('user_filter')))
            ->when($labelFilter === 'missing', fn ($q) => $q->whereNull('label_id'))
            ->when($labelFilter === 'with', fn ($q) => $q->whereNotNull('label_id'))
            ->when($dateFrom !== '', fn ($q) => $q->whereDate($dateField, '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate($dateField, '<=', $dateTo))
            ->orderBy('id', 'desc');

        $collection = $query->get();
        $categoryLabel = $categoryId ? (InvCategory::find((int) $categoryId)?->name ?? 'N/A') : 'Todas';
        $statusLabel = $statusId ? (InvStatus::find((int) $statusId)?->name ?? 'N/A') : 'Todos';
        $sedeLabel = $sedeId ? (Sede::find((int) $sedeId)?->sede ?? 'N/A') : 'Todas';
        $assigneeLabel = $assigneeMay
            ? (User::withTrashed()->find((int) $request->input('user_filter'))?->name ?? 'N/A')
            : 'Todos';
        $labelFilterLabel = $labelFilter === 'missing' ? 'Sin etiqueta de sede' : ($labelFilter === 'with' ? 'Con etiqueta de sede' : 'Todas');

        return Excel::download(
            new InventoryWorkbookExport($collection, [
                'search' => $search ?: '',
                'category_label' => $categoryLabel,
                'status_label' => $statusLabel,
                'sede_label' => $sedeLabel,
                'assignee_label' => $assigneeLabel,
                'label_filter_label' => $labelFilterLabel,
                'date_field' => $dateField,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]),
            'inventario_v2_' . date('Y-m-d_His') . '.xlsx'
        );
    }
}
