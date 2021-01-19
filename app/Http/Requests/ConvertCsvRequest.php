<?php

namespace App\Http\Requests;

use App\Traits\Requests\RowValidation;
use Illuminate\Foundation\Http\FormRequest;

class ConvertCsvRequest extends FormRequest
{
    use RowValidation;

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
            'columns.*.title' => 'required'
        ];

        return array_merge(
            $defaultRules,
            $this->rowKeysRules()
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        $defaultAttributes = [
            'columns' => 'column',
            'columns.*.title' => 'column title',
        ];

        return array_merge(
            $defaultAttributes,
            $this->rowAttributes()
        );
    }

}
