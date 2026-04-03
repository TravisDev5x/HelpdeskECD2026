<?php

namespace App\Support;

use App\Models\InvMovement;
use Illuminate\Database\Eloquent\Builder;

final class InvAssignmentHistoryQuery
{
    /**
     * @return Builder<InvMovement>
     */
    public static function base(): Builder
    {
        return InvMovement::query()->with([
            'asset' => fn ($q) => $q->withTrashed(),
            'user' => fn ($q) => $q->withTrashed(),
            'previousUser' => fn ($q) => $q->withTrashed(),
            'admin',
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<InvMovement>
     */
    public static function applyFilters(Builder $query, array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $typeScope = (string) ($filters['type_scope'] ?? 'assignments');
        $userId = $filters['user_id'] ?? '';
        $adminId = $filters['admin_id'] ?? '';
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $batchUuid = trim((string) ($filters['batch_uuid'] ?? ''));

        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->whereHas('asset', function ($q) use ($term) {
                $q->where('internal_tag', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('serial', 'like', $term);
            });
        }

        if ($typeScope === 'assignments') {
            $query->whereIn('type', ['CHECKOUT', 'CHECKIN']);
        } elseif ($typeScope !== '' && $typeScope !== 'all') {
            $query->where('type', $typeScope);
        }

        if ($userId !== '' && $userId !== null) {
            $uid = (int) $userId;
            $query->where(function ($q) use ($uid) {
                $q->where('user_id', $uid)->orWhere('previous_user_id', $uid);
            });
        }

        if ($adminId !== '' && $adminId !== null) {
            $query->where('admin_id', (int) $adminId);
        }

        if (! empty($dateFrom)) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if (! empty($dateTo)) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if ($batchUuid !== '') {
            $query->where('batch_uuid', $batchUuid);
        }

        return $query;
    }
}
