<?php

namespace App\Contracts;

use App\Models\EmailTemplate;
use Illuminate\Support\Collection;

interface EmailTemplateRepositoryInterface
{
    public function create(array $data): EmailTemplate;
    public function findById(int $id): ?EmailTemplate;
    public function findByType(string $type): Collection;
    public function incrementUsageCount(int $id): void;
    public function getMostUsed(int $limit = 5): Collection;
}