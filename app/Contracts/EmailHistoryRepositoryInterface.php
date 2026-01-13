<?php

namespace App\Contracts;

use App\Models\EmailHistory;
use Illuminate\Support\Collection;

interface EmailHistoryRepositoryInterface
{
    public function create(array $data): EmailHistory;
    public function findById(int $id): ?EmailHistory;
    public function getByOperation(string $operation, int $limit = 10): Collection;
    public function getRecent(int $limit = 10): Collection;
    public function updateRating(int $id, int $rating, ?string $feedback = null): bool;
}