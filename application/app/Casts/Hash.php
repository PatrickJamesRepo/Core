<?php
namespace App\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    protected ?string $algorithm;

    public function __construct(string $algorithm = null)
    {
        $this->algorithm = $algorithm;
    }

    public function set($model, $key, $value, $attributes): string
    {
        // if it already looks like a bcrypt hash, leave it alone:
        if (str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$')) {
            return $value;
        }

        return is_null($this->algorithm)
            ? bcrypt($value)
            : hash($this->algorithm, $value);
    }
}
