<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $budgetId = $this->route('budget')?->id;

        return [
            'category_id' => [
                'required', 'exists:categories,id',
                Rule::unique('budgets')
                    ->where(fn ($q) => $q
                        ->where('user_id', $this->user()->id)
                        ->where('period', $this->input('period')))
                    ->ignore($budgetId),
            ],
            'limit_amount' => ['required', 'numeric', 'min:1'],
            'alert_threshold' => ['required', 'integer', 'min:1', 'max:100'],
            'period' => ['required', 'in:weekly,monthly'],
            'is_active' => ['boolean'],
        ];
    }
}
