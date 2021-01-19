<?php

namespace App\Traits\Requests;


trait RowValidation
{

    private function rowKeysRules()
    {
        if (is_array($this->columns)) {
            $rules = ['rows'  => 'required|array'];

            foreach ($this->columns as $column) {
                $rules["rows.*.{$column['key']}"] = 'required';
            }
        }

        return $rules ?? [];
    }

    private function rowAttributes()
    {
        if (is_array($this->columns)) {
            $attributes = ['rows'  => 'rows'];

            foreach ($this->columns as $column) {
                $attributes["rows.*.{$column['key']}"] = strtolower($column['title']);
            }
        }

        return $attributes ?? [];
    }

}
