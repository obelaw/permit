<?php

namespace Obelaw\Permit\Attributes;

use Attribute;

#[Attribute]
final class Permissions
{
    public function __construct(
        public string $id = null,
        public string|null $title = null,
        public string|null $description = null,
        public array $permissions,
    ) {
    }
}
