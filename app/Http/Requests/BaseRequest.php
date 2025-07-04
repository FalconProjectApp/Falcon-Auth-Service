<?php

namespace App\Http\Requests;

use FalconERP\Skeleton\Models\Erp\Billing;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    protected $ruleConfig;
    protected $model;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (null === $this->ruleConfig) {
            return true;
        }

        $rules = (new Billing())
            ->byBetweenAndRule($this->ruleConfig, Carbon::now()->toDateString())
            ->get();

        $totalAmount = config($this->ruleConfig);

        $rules->each(function ($rule) use (&$totalAmount) {
            $totalAmount += (null !== $rule ? $rule->contracted_quantity - $rule->used_quantity : 0);
        });

        return $totalAmount <= $this->model::count() ? false : true;
    }
}
