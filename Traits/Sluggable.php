<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait Sluggable
{
    public static function bootSluggable()
    {
        static::creating(function (Model $model) {
            $model->slug = (!empty($model->slug)) ? self::createSlug($model->slug) : self::createSlug($model->{$model->sluggableSource()});
        });

        static::updating(function (Model $model) {
            $model->slug = self::createSlug($model->{$model->sluggableSource()}, $model->id);
        });
    }

    /**
     * Field name of slug source
     */
    abstract public function sluggableSource(): string;

    public static function createSlug(string $source, $modelId = null): string
    {
        $slug = (string)str()->slug($source, '-'); # in older Laravel versions use Str facade instead of `str()` helper: Str::slug($source, '-');
        $entity = self::findBySlug($slug);
        if (empty($entity)) {
            return $slug;
        }

        if (!empty($modelId) && $modelId == $entity->id) {
            return $slug;
        }

        while (true) {
            $newSlug = $slug . '-' . str()->random(5); # in older Laravel versions use Str facade instead of `str()` helper: Str::random(5);
            $entity = self::findBySlug($newSlug);
            if (empty($entity)) {
                return $newSlug;
            }
        }
    }

    public static function findBySlug(string $slug)
    {
        return self::where('slug', $slug)->first();
    }

    public static function findOrFailBySlug(string $slug)
    {
        return self::where('slug', $slug)->firstOrFail();
    }
}
