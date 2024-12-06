<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BarangTransaksiRequest extends FormRequest
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
            'barang_id' => 'required',
            'kode_transaksi' => 'nullable|max:191',
            'tanggal' => 'required|date',
            'qty' => 'required|numeric',
            'tanggal_kadaluarsa' => $this->is_masuk ? 'required|date' : 'nullable'
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
            'barang_id' => 'Barang',
            'kode_transaksi' => 'Kode Transaksi',
            'tanggal' => 'Tanggal',
            'qty' => 'QTY',
            'tanggal_kadaluarsa' => 'Tanggal Kadaluarsa'
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
