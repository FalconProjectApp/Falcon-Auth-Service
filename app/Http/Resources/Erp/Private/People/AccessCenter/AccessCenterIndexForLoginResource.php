<?php

namespace App\Http\Resources\Erp\Private\People\AccessCenter;

use Illuminate\Http\Resources\Json\JsonResource;

class AccessCenterIndexForLoginResource extends JsonResource
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
        return [
            'id'               => (int) $this->id,
            'people_id'        => (int) $this->base_people_id,
            'email'            => (string) $this->email,
            'is_master'        => (bool) $this->is_master,
            'balance_value'    => (int) $this->balance_value,
            'created_at'       => (string) $this->created_at,
            'updated_at'       => (string) $this->updated_at,
            'databases_access' => AccessCenterDatabaseResource::collection($this->whenLoaded('databasesAccess')),
        ];
    }
}
