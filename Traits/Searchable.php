<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    abstract public function searchableFields(): array;

    public function scopeSearch(Builder $query, string $phrase = null): Builder
    {
        $searchableFields = $this->searchableFields();
        $fields = array_values(array_filter($searchableFields, function($field) {
            if (!is_array($field)) {
                return $field;
            }
        }));

        $relations = array_filter($searchableFields, function($field) {
            if (is_array($field)) {
                return $field;
            }
        });

        if (!empty($phrase)) {
            $query->where(function(Builder $q) use ($phrase, $fields) {
                foreach ($fields as $k => $field) {
                    if ($k == 0) {
                        $q->where($field, 'like', "%$phrase%");
                    } else {
                        $q->orWhere($field, 'like', "%$phrase%");
                    }
                }
            });

            if (!empty($relations)) {
                foreach ($relations as $relation => $fields) {
                    $query->orWhereHas($relation, function(Builder $q) use ($phrase, $fields) {
                        foreach ($fields as $k => $field) {
                            if ($k == 0) {
                                $q->where($field, 'like', "%$phrase%");
                            } else {
                                $q->orWhere($field, 'like', "%$phrase%");
                            }
                        }
                    });
                }
            }
        }

        return $query;
    }
}
