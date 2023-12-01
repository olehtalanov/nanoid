<?php

namespace Talanov\Nanoid;

use Illuminate\Contracts\Support\Arrayable;
use Talanov\Nanoid\NanoIdOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasNanoId
{
    public const DEFAULT_COLUMN_NAME = 'nano_id';

    abstract public function getNanoIdOptions(): NanoIdOptions;

    public static function findByNano(mixed $id = null): Builder
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return self::findManyByNano($id);
        }

        $instance = new static();
        $options = $instance->getNanoIdOptions();

        if ($id !== null) {
            $id = (string) $id;
        }

        return $instance->where($options->field ?? self::DEFAULT_COLUMN_NAME, '=', $id);
    }

    public static function findManyByNano(array|Collection $id): Builder
    {
        $instance = new static();
        $options = $instance->getNanoIdOptions();

        return $instance->whereIn($options->field ?? self::DEFAULT_COLUMN_NAME, $id);
    }

    protected static function bootHasNanoId(): void
    {
        static::creating(static function (Model $model) {
            $model->generateNanoId();
        });
    }

    protected function generateNanoId(): void
    {
        $options = $this->getNanoIdOptions();
        $field = $options->field ?? self::DEFAULT_COLUMN_NAME;
        $value = $this->generateUniqueNanoIdValue();

        if (! $this->recordWithSameUidExists($field, $value)) {
            $this->$field = $value;
        } else {
            $this->generateNanoId();
        }
    }

    protected function generateUniqueNanoIdValue(): string
    {
        $options = $this->getNanoIdOptions();

        $hashBase64 = base64_encode(hash('sha256', microtime(), true));
        $hashUrlsafe = strtr($hashBase64, '+/', '_~');
        $hashUrlsafe = rtrim($hashUrlsafe, '=');

        return substr($hashUrlsafe, 0, $options->length ?? 8);
    }

    protected function recordWithSameUidExists(string $field, string $value): bool
    {
        $query = static::select($field)->where($field, $value)->withoutGlobalScopes();

        if ($this->usesSoftDeletes()) {
            $query->withTrashed();
        }

        return $query->exists();
    }

    protected function usesSoftDeletes(): bool
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this), true);
    }
}
