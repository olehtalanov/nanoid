<?php

namespace Talanov\Nanoid;

final class NanoIdOptions
{
    public string $field;

    public int $length;

    public static function make(): NanoIdOptions
    {
        return new self();
    }

    public function saveTo(string $field = 'nano_id'): NanoIdOptions
    {
        $this->field = $field;

        return $this;
    }

    public function length(int $length = 8): NanoIdOptions
    {
        $this->length = $length;

        return $this;
    }
}
