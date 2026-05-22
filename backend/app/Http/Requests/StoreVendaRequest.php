<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente' => ['required', 'string', 'min:2', 'max:255'],
            'produtos' => ['required', 'array', 'min:1'],
            'produtos.*.id' => ['required', 'integer', 'exists:produtos,id'],
            'produtos.*.quantidade' => ['required', 'integer', 'min:1'],
            'produtos.*.preco_unitario' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $produtos = $this->input('produtos', []);
            $ids = array_column($produtos, 'id');
            if (count($ids) !== count(array_unique($ids))) {
                $validator->errors()->add('produtos', 'Não é permitido repetir o mesmo produto na venda.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'cliente.required' => 'O cliente é obrigatório.',
            'produtos.required' => 'Informe pelo menos um produto.',
            'produtos.*.id.exists' => 'Produto não encontrado.',
        ];
    }
}
