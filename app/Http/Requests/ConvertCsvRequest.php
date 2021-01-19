<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertCsvRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $defaultRules = [
            'columns' => 'required|array',
            'columns.*.key' => 'required',
            'columns.*.title' => 'required',
            'rows'  => 'required|array'
        ];

        return array_merge(
            $defaultRules,
            $this->rowKeysRules()
        );
    }

    private function rowKeysRules()
    {
        if (is_array($this->columns)) {
            foreach ($this->columns as $column) {
                $rules["rows.*.{$column['key']}"] = 'required';
            }
        }

        return $rules ?? [];
    }
}
