<?php

declare(strict_types=1);

namespace FHIVE\Flier\Drivers;

use FHIVE\Flier\Builder\Operations\Operation;

/**
 * Contrato para drivers de operações em recursos FHIR.
 * Contract for FHIR resource operation drivers.
 *
 * Um driver recebe as operações acumuladas pelo ResourceBuilder e as materializa:
 * aplica no banco, envia HTTP, gera patch, etc.
 *
 * A driver receives the operations accumulated by ResourceBuilder and materializes them:
 * applies to DB, sends HTTP, generates patch, etc.
 */
interface ResourceDriver
{
    /**
     * PT: Lê um recurso pelo ID (GET /ResourceType/id).
     * EN: Reads a resource by ID (GET /ResourceType/id).
     */
    public function read(string $resourceType, string $id): mixed;

    /**
     * PT: Lê uma versão específica do recurso (GET /ResourceType/id/_history/vid).
     * EN: Reads a specific version of the resource (GET /ResourceType/id/_history/vid).
     */
    public function vread(string $resourceType, string $id, string $versionId): mixed;

    /**
     * PT: Cria o recurso com os dados + operações aplicadas.
     * EN: Creates the resource with data + applied operations.
     *
     * @param  array<string, mixed>  $data
     * @param  list<Operation>  $operations
     */
    public function create(string $resourceType, array $data, array $operations): mixed;

    /**
     * PT: Atualiza (patch/merge) o recurso com as operações acumuladas.
     * EN: Updates (patch/merge) the resource with the accumulated operations.
     *
     * @param  array<string, mixed>  $data
     * @param  list<Operation>  $operations
     */
    public function update(string $resourceType, array $data, array $operations): mixed;

    /**
     * PT: Substitui o recurso inteiro (PUT semântico).
     * EN: Replaces the entire resource (PUT semantics).
     *
     * @param  array<string, mixed>  $data
     * @param  list<Operation>  $operations
     */
    public function put(string $resourceType, array $data, array $operations): mixed;

    /**
     * PT: Remove o recurso.
     * EN: Deletes the resource.
     *
     * @param  array<string, mixed>  $data
     */
    public function delete(string $resourceType, array $data): mixed;
}
