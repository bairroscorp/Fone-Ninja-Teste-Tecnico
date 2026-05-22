<?php

namespace App\Exceptions;

use Exception;

class EstoqueInsuficienteException extends Exception
{
    public function __construct(string $nomeProduto)
    {
        parent::__construct("Estoque insuficiente para o produto {$nomeProduto}");
    }
}
