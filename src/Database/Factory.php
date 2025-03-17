<?php

namespace Vista\Database;

/**
 * A factory for creating database instances.
 */
interface Factory
{
    public function create(string $type = 'sql'): Database;
}
