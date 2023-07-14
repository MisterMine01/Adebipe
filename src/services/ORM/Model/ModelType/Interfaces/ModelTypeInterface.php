<?php

namespace Adebipe\Model\Type;

interface ModelTypeInterface
{
    public function canBeNull(): bool;

    public function isAutoIncrement(): bool;

    public function getPDOParamType(): ?int;

    public function checkType(mixed $value): ?bool;

    public function getSqlCreationType(): ?string;

    public function getSqlType(): string;

    public function getMoreSql(): array;
}