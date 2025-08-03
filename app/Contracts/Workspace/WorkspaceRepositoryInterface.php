<?php

namespace App\Contracts;

use App\Models\Workspace\Workspace;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface WorkspaceRepositoryInterface
{
    public function allWorkspace(array $data): CursorPaginator;
    public function create(array $data): Workspace;

}
