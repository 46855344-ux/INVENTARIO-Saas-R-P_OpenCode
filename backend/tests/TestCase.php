<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Simula el arranque de una nueva petición HTTP: en producción cada
     * petición se atiende con guardas nuevas, pero el contenedor de pruebas
     * conserva las instancias y el usuario cacheado por el guard de Sanctum.
     */
    protected function resetAuthState(): void
    {
        config(['auth.defaults.guard' => 'web']);

        $this->app['auth']->forgetGuards();
    }
}
