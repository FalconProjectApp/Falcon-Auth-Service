<?php

namespace App\Http\Resources\Erp\Private\People\AccessCenter;

use Illuminate\Http\Resources\Json\JsonResource;

class AccessCenterDatabaseResource extends JsonResource
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
            'id'                => (int) $this->id,
            'base'              => (string) $this->base,
            'base_people_id'    => (int) $this->pivot->base_people_id,
            'is_active'         => (bool) $this->pivot->is_active,
            'environment'       => (string) $this->pivot->environment,
            'database_group_id' => (int) $this->database_group_id,
            'created_at'        => (string) $this->created_at,
            'updated_at'        => (string) $this->updated_at,
        ];
    }
}
