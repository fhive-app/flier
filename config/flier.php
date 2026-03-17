<?php

declare(strict_types=1);

/**
 * PT: Configuração do Flier — SDK FHIR HTTP client.
 * EN: Flier configuration — FHIR HTTP client SDK.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | PT: Servidor FHIR padrão / EN: Default FHIR server
    |--------------------------------------------------------------------------
    |
    | PT: URL base do servidor FHIR para o FHIRHttpDriver.
    | EN: Base URL of the FHIR server for FHIRHttpDriver.
    |
    */
    'base_url' => env('FHIR_BASE_URL', 'http://localhost:8080/fhir'),

    /*
    |--------------------------------------------------------------------------
    | PT: Timeout (segundos) / EN: Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('FHIR_TIMEOUT', 30),

];
