<?php

namespace App\Http\Resources\V1\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AllWorkspcaeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */

     private $pagination;

     public function __construct($resource)
     {
         $this->pagination = [
            'perPage' => $resource->perPage(),
            'nextCursor' => optional($resource->nextCursor())?->encode(),
            'prevCursor' => optional($resource->previousCursor())?->encode(),
        ];

         $resource = $resource->getCollection();

         parent::__construct($resource);
        }
    public function toArray(Request $request): array
    {
        return [
            'workspaces' => AllWorkspaceResource::collection(resource: $this->collection),
            'pagination' => $this->pagination
        ];
    }
}
