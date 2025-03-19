<?php

namespace Vista\QueryBuilder;

/**
 * A factory for creating query builder instances.
 */
interface Factory
{
    public function create(): QueryBuilder;
}
