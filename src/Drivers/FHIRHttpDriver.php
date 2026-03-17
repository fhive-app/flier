<?php

declare(strict_types=1);

namespace FHIVE\Flier\Drivers;

use FHIVE\Flier\Builder\SearchParam;
use FHIVE\Flier\Patch\FHIRPathPatchDriver;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * PT: Driver HTTP FHIR usando Laravel Http facade. Suporta bearer token para SMART on FHIR.
 * EN: FHIR HTTP driver using Laravel Http facade. Supports bearer token for SMART on FHIR.
 *
 * Uso / Usage:
 *   $driver = new FHIRHttpDriver('https://hapi.fhir.org/baseR4');
 *   $driver = new FHIRHttpDriver('https://fhive.test/fhir', 'eyJ...');
 *
 *   // GET — lê recurso / GET — reads resource
 *   Flier::resource('Patient', ['id' => 'abc'])->useDriver($driver)->read();
 *
 *   // POST — cria com operações / POST — creates with operations
 *   Flier::resource('Patient')->gender('male')->useDriver($driver)->create();
 *
 *   // PATCH — FHIRPath Patch via update() / PATCH — FHIRPath Patch via update()
 *   Flier::resource('Patient', $data)->birthDate()->delete()->useDriver($driver)->update();
 */
final class FHIRHttpDriver implements ResourceDriver, SearchDriver
{
    /**
     * @param  string  $baseUrl  PT: URL base do servidor FHIR / EN: FHIR server base URL
     * @param  string|null  $bearerToken  PT: Token OAuth2 (SMART on FHIR) / EN: OAuth2 token (SMART on FHIR)
     */
    public function __construct(
        private readonly string $baseUrl,
        private ?string $bearerToken = null,
    ) {}

    /**
     * PT: Define ou atualiza o bearer token (fluente).
     * EN: Sets or updates the bearer token (fluent).
     */
    public function withToken(string $token): static
    {
        $this->bearerToken = $token;

        return $this;
    }

    // ——————————————————————————————————————————————————————————————————
    // ResourceDriver
    // ——————————————————————————————————————————————————————————————————

    /** {@inheritdoc} */
    public function read(string $resourceType, string $id): array
    {
        return $this->client()
            ->get("/{$resourceType}/{$id}")
            ->throw()
            ->json();
    }

    /** {@inheritdoc} */
    public function vread(string $resourceType, string $id, string $versionId): array
    {
        return $this->client()
            ->get("/{$resourceType}/{$id}/_history/{$versionId}")
            ->throw()
            ->json();
    }

    /** {@inheritdoc} */
    public function create(string $resourceType, array $data, array $operations): array
    {
        $payload = (new ArrayResourceDriver)->create($resourceType, $data, $operations);

        return $this->client()
            ->post("/{$resourceType}", $payload)
            ->throw()
            ->json();
    }

    /** {@inheritdoc} */
    public function update(string $resourceType, array $data, array $operations): array
    {
        $id = $data['id'] ?? throw new InvalidArgumentException(
            "Resource must contain 'id' for PATCH.",
        );

        $patch = app(FHIRPathPatchDriver::class)->generate($resourceType, $operations);

        return $this->client()
            ->patch("/{$resourceType}/{$id}", $patch)
            ->throw()
            ->json();
    }

    /** {@inheritdoc} */
    public function put(string $resourceType, array $data, array $operations): array
    {
        $payload = (new ArrayResourceDriver)->put($resourceType, $data, $operations);
        $id = $payload['id'] ?? throw new InvalidArgumentException(
            "Resource must contain 'id' for PUT.",
        );

        return $this->client()
            ->put("/{$resourceType}/{$id}", $payload)
            ->throw()
            ->json();
    }

    /** {@inheritdoc} */
    public function delete(string $resourceType, array $data): array
    {
        $id = $data['id'] ?? throw new InvalidArgumentException(
            "Resource must contain 'id' for DELETE.",
        );

        $this->client()
            ->delete("/{$resourceType}/{$id}")
            ->throw();

        return [];
    }

    // ——————————————————————————————————————————————————————————————————
    // SearchDriver
    // ——————————————————————————————————————————————————————————————————

    /**
     * @param  list<SearchParam>  $params
     */
    public function search(string $resourceType, array $params): array
    {
        $query = collect($params)
            ->mapWithKeys(fn (SearchParam $p) => [
                $p->modifier ? "{$p->code}:{$p->modifier}" : $p->code => $p->rawValue,
            ])
            ->all();

        return $this->client()
            ->get("/{$resourceType}", $query)
            ->throw()
            ->json();
    }

    // ——————————————————————————————————————————————————————————————————
    // Interno / Internal
    // ——————————————————————————————————————————————————————————————————

    private function client(): PendingRequest
    {
        $client = Http::withHeaders([
            'Accept' => 'application/fhir+json',
            'Content-Type' => 'application/fhir+json',
        ])->baseUrl(rtrim($this->baseUrl, '/'));

        if ($this->bearerToken !== null) {
            $client = $client->withToken($this->bearerToken);
        }

        return $client;
    }
}
