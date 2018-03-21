<?php

namespace App\Http\Requests\Api\Category;

use App\Http\Requests\Api\AbstractRequest;

class CreateCategoryRequest extends AbstractRequest
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
        return [
            'name_vi' => 'required|min:3|max:100',
            'name_en' => 'required|min:3|max:100',
            'name_jp' => 'required|min:3|max:100',
        ];
    }
}
