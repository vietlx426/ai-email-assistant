<?php

namespace App\Repositories;

use App\Contracts\EmailHistoryRepositoryInterface;
use App\Models\EmailHistory;
use Illuminate\Support\Collection;

class EmailHistoryRepository implements EmailHistoryRepositoryInterface
{
    public function create(array $data): EmailHistory
    {
        return EmailHistory::create($data);
    }

    public function findById(int $id): ?EmailHistory
    {
        return EmailHistory::find($id);
    }

    public function getByOperation(string $operation, int $limit = 10): Collection
    {
        return EmailHistory::where('operation', $operation)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return EmailHistory::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function updateRating(int $id, int $rating, ?string $feedback = null): bool
    {
        return EmailHistory::where('id', $id)
                ->update([
                    'rating' => $rating,
                    'feedback' => $feedback,
                ]) > 0;
    }
}