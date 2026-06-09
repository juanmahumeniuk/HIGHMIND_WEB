<?php
declare(strict_types=1);

namespace App\Models\Contracts;

interface AdminListable
{
    /** @return list<array<string, mixed>> */
    public function adminListar(): array;
}
