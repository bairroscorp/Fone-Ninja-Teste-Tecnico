<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $produtoId = $this->route('produto')->id;

        return [
            'nome' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('produtos', 'nome')->ignore($produtoId),
            ],
            'preco_venda' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do produto é obrigatório.',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'preco_venda.gt' => 'O preço de venda deve ser positivo.',
        ];
    }
}
