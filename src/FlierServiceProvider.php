<?php

declare(strict_types=1);

namespace FHIVE\Flier;

use FHIVE\Flier\Contracts\FHIRSearchParameterSource;
use FHIVE\Flier\Drivers\FHIRHttpDriver;
use FHIVE\Flier\Patch\FHIRPathPatchDriver;
use FHIVE\Flier\Sources\CompositeSearchParameterSource;
use Illuminate\Support\ServiceProvider;

/**
 * PT: Provedor de serviços do módulo Flier — SDK FHIR open-source (builders, drivers, macros).
 * EN: Service provider for the Flier module — open-source FHIR SDK (builders, drivers, macros).
 */
class FlierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/flier.php', 'flier');

        // CompositeSearchParameterSource: singleton — lista de fontes imutável após o boot.
        // CompositeSearchParameterSource: singleton — source list is immutable after boot.
        $this->app->singleton(CompositeSearchParameterSource::class);

        // FHIRSearchParameterSource resolve para o composite — fontes registradas via Flier::registerSearchParameterSource().
        // FHIRSearchParameterSource resolves to the composite — sources registered via Flier::registerSearchParameterSource().
        $this->app->bind(FHIRSearchParameterSource::class, CompositeSearchParameterSource::class);

        // FHIRPathPatchDriver: singleton — sem estado, puro.
        // FHIRPathPatchDriver: singleton — stateless, pure.
        $this->app->singleton(FHIRPathPatchDriver::class);

        // FHIRHttpDriver: scoped — usa config para base URL, sem estado entre requests (Octane-safe).
        // FHIRHttpDriver: scoped — uses config for base URL, no state between requests (Octane-safe).
        $this->app->scoped(FHIRHttpDriver::class, fn () => new FHIRHttpDriver(
            baseUrl: (string) config('flier.base_url'),
        ));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/flier.php' => config_path('flier.php'),
        ], 'flier-config');
    }
}
