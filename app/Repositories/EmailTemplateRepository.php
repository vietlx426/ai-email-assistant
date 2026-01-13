<?php

namespace App\Repositories;

use App\Contracts\EmailTemplateRepositoryInterface;
use App\Models\EmailTemplate;
use Illuminate\Support\Collection;

class EmailTemplateRepository implements EmailTemplateRepositoryInterface
{
    public function create(array $data): EmailTemplate
    {
        return EmailTemplate::create($data);
    }

    public function findById(int $id): ?EmailTemplate
    {
        return EmailTemplate::find($id);
    }

    public function findByType(string $type): Collection
    {
        return EmailTemplate::where('type', $type)
            ->orderBy('usage_count', 'desc')
            ->get();
    }

    public function incrementUsageCount(int $id): void
    {
        EmailTemplate::where('id', $id)->increment('usage_count');
    }

    public function getMostUsed(int $limit = 5): Collection
    {
        return EmailTemplate::orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }
}