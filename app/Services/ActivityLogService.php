<?php

declare(strict_types=1);

namespace App\Services;

class ActivityLogService
{
    public function log(
        string $action,
        string $subjectType,
        ?int $subjectId,
        string $subjectLabel,
        ?string $description = null,
        ?array $properties = null,
    ): void {
        // Activity logging can be wired to a database table later.
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentForDashboard(int $limit = 20): array
    {
        return [];
    }
}
