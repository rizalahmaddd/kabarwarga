<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public const DEFAULTS = [
        'site_name' => 'Kabar Warga',
        'site_tagline' => '',
        'address' => '',
        'treasurer_contact' => '',
        'payment_info' => '',
    ];

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public static function allValues(): array
    {
        return Cache::rememberForever('settings', fn () => array_merge(
            self::DEFAULTS,
            self::query()->pluck('value', 'key')->filter(fn ($v) => $v !== null)->all(),
        ));
    }

    public static function read(string $key): string
    {
        return (string) (self::allValues()[$key] ?? '');
    }

    public static function write(array $values): void
    {
        foreach ($values as $key => $value) {
            self::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget('settings');
    }
}
