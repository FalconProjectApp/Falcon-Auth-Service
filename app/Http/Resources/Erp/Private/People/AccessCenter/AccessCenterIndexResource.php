<?php

namespace App\Http\Resources\Erp\Private\People\AccessCenter;

use App\Http\Resources\Erp\People\People\PeopleIndexResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessCenterIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $people = $this->databasesUsersAccess->first()->people;

        return [
            'id'         => (int) $this->id,
            'people_id'  => (int) $people->id,
            'email'      => (string) $this->email,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'people'     => new PeopleIndexResource($people),
        ];
    }
}
