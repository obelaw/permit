<?php

namespace Obelaw\Permit\Attributes;

use Attribute;

#[Attribute]
final class PagePermission
{
    public function __construct(
        public string $id,
        public string|null $title = null,
        public string|null $description = null,
        public string $category = 'global',
    ) {}
}
