<?php

namespace App\Http\Requests\Api\Log;

use App\Http\Requests\Api\AbstractRequest;

class SearchLogRequest extends AbstractRequest
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
            'key' => 'required|string',
            'page' => 'required|integer'
        ];
    }
}
