<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait UsesUuid
{
    public static function bootUsesUuid()
    {
        static::creating(function (Model $model) {
            $model->{$model->uuidFieldName()} = str()->uuid();# in older Laravel versions use Str facade instead of `str()` helper: Str::uuid();
        });
    }

    /**
     * UUID field name
     */
    abstract public function uuidFieldName(): string;

    public static function findByUuid(string $uuid, array $joins = []): Model|null
    {
        return self::with($joins)->where('uuid', $uuid)->first();
    }

    public static function findOrFailByUuid(string $uuid, array $joins = [])
    {
        return self::with($joins)->where('uuid', $uuid)->firstOrFail();
    }
}
