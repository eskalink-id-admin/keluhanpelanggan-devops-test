<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('release:stamp {branch} {commit} {build} {--release-version=}', function () {
    $metadata = [
        'branch' => $this->argument('branch'),
        'commit' => $this->argument('commit'),
        'build' => $this->argument('build'),
    ];

    if ($this->option('release-version') !== null) {
        $metadata['version'] = $this->option('release-version');
    }

    foreach ($metadata as $value) {
        if (trim($value) === '' || strlen($value) > 200 || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            $this->error('Metadata rilis harus berisi 1–200 karakter tanpa karakter kontrol.');

            return 1;
        }
    }

    $written = file_put_contents(base_path('release.json'), json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL, LOCK_EX);

    if ($written === false) {
        $this->error('Gagal menulis release.json.');

        return 1;
    }

    $this->info('release.json dibuat. Sertakan dalam artifact; bangun ulang config cache setelah stamping.');

    return 0;
})->purpose('Menulis identitas build ke artifact tanpa memerlukan Git pada server deployment');
