<?php

namespace Talanov\Nanoid;

use Talanov\Nanoid\NanoIdOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasNanoId
{
    abstract public function getNanoIdOptions(): NanoIdOptions;

    public function findByNano(string $nanoID): Builder
    {
        $options = $this->getNanoIdOptions();

        return self::where($options->field ?? 'nano_id', $nanoID);
    }

    public function findManyByNano(array|Collection $nanoID): Builder
    {
        $options = $this->getNanoIdOptions();

        return self::whereIn($options->field ?? 'nano_id', $nanoID);
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
        $field = $options->field ?? 'nano_id';
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
        $hashUrlsafe = strtr($hashBase64, '+/', '-_');
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
