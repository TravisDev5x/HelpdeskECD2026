<?php

namespace App\Livewire\Admin\Users\Concerns;

trait ValidatesUserOrganizationalFields
{
    protected function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $t = trim($value);

        return $t === '' ? null : $t;
    }
}
