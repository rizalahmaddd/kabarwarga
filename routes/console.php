<?php

use App\Models\Household;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('kk:secure', function () {
    $moved = 0;
    Household::whereNotNull('kk_image_path')->each(function (Household $household) use (&$moved) {
        $path = $household->kk_image_path;
        if (Storage::disk('public')->exists($path) && ! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, Storage::disk('public')->get($path));
            Storage::disk('public')->delete($path);
            $moved++;
        }
    });

    $this->info("{$moved} foto KK dipindahkan ke penyimpanan privat.");
})->purpose('Move KK scans uploaded before the privacy fix off the public disk');
