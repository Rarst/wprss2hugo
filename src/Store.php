<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo;

/**
 * Interface to store any pending data at an end of processing.
 */
interface Store
{
    public function store(): void;
}
