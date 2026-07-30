<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * How many rows a page of a paginated list holds. The feed and find people
     * page at the same rate, so the number is one thing rather than two that
     * have to be kept in step. See ADR-0005.
     */
    protected const PER_PAGE = 10;
}
