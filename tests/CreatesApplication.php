<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        if ($app->configurationIsCached()) {
            throw new \RuntimeException('Hapus config cache dengan php artisan config:clear sebelum menjalankan test.');
        }

        $app->make(Kernel::class)->bootstrap();

        if (! $app->environment('testing')
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Test hanya boleh berjalan pada environment testing dengan SQLite in-memory.');
        }

        return $app;
    }
}
