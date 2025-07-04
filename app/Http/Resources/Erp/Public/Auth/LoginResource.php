<?php

namespace App\Http\Resources\Erp\Public\Auth;

use App\Http\Resources\Erp\Private\People\AccessCenter\AccessCenterIndexForLoginResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
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
            'access_token' => (string) $this->access_token,
            'expires_in'   => (int) $this->expires_in,
            'user'         => new AccessCenterIndexForLoginResource($this->user),
        ];
    }
}
