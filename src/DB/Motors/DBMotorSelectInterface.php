<?php

namespace Architekt\DB\Motors;

use Architekt\DB\EntityInterface;

interface DBMotorSelectInterface
{
    public function __construct(DBMotorInterface $DBMotor, EntityInterface $entity);

    public function build(): string;

    public function between(
        string|EntityInterface $fieldOrEntity,
        int|string $fromOrFieldWhenEntity,
        int|string $toOrFromWhenEntity,
        int|string $toWhenEntityWithField
    ): static;

    public function fetch(): ?array;

    public function select(
        string|EntityInterface $fieldOrEntity,
        ?string $aliasOrFieldWhenEntityFirst = null,
        ?string $aliasWhenEntityWithField = null
    ): static;

    public function ob(): static;

    public function cb(): static;

    public function filter(): static;

    public function filterOr(): static;

    public function filterNot(): static;

    public function filterGreater(): static;

    public function filterGreaterOrEqual(): static;

    public function filterLess(): static;

    public function filterLessOrEqual(): static;

    public function limit(int $limit): static;

    public function orderAsc(string|EntityInterface $fieldOrEntity, ?string $fieldWhenEntity = null): static;

    public function orderDesc(string|EntityInterface $fieldOrEntity, ?string $fieldWhenEntity = null): static;

    public function join(EntityInterface $entity): static;

    public function leftJoin(EntityInterface $entity): static;

    public function filterOn(): static;

    public function resultsNb(): ?int;
}