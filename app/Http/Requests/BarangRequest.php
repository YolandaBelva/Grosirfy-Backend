<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'kategori_id' => 'required',
            'kode' => $this->method() == 'POST' ? 'required|string|max:191|unique:barang,id' : 
                'required|string|max:191|unique:barang,id,'.$this->route('id'),
            'nama' => 'required|string|max:191',
            'satuan' => 'required|string|max:191',
            'harga' => 'required|numeric',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'kategori_id' => 'Kategori',
            'kode' => 'Kode',
            'nama' => 'Nama',
            'satuan' => 'Satuan',
            'harga' => 'Harga',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'response_code' => 422,
                'response_message' => 'Validasi gagal',
                'errors' => $validator->errors()->toArray(),
                'data' => []
            ], 422)
        );
    }
}
