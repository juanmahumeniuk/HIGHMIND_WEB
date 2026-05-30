<?php
declare(strict_types=1);

namespace App\Models\Contracts;

interface AdminReadable
{
    /** @return array<string, mixed>|null */
    public function adminObtener(int $id): ?array;
}
