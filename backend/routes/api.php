<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\VendaController;

Route::get('/produtos', [ProdutoController::class, 'index']);
Route::post('/produtos', [ProdutoController::class, 'store']);
Route::get('/produtos/{produto}', [ProdutoController::class, 'show']);
Route::put('/produtos/{produto}', [ProdutoController::class, 'update']);
Route::delete('/produtos/{produto}', [ProdutoController::class, 'destroy']);

Route::get('/compras', [CompraController::class, 'index']);
Route::get('/compras/{compra}', [CompraController::class, 'show']);
Route::post('/compras', [CompraController::class, 'store']);
Route::post('/compras/{compra}/cancelar', [CompraController::class, 'cancelar']);

Route::get('/vendas', [VendaController::class, 'index']);
Route::get('/vendas/{venda}', [VendaController::class, 'show']);
Route::post('/vendas', [VendaController::class, 'store']);
Route::post('/vendas/{venda}/cancelar', [VendaController::class, 'cancelar']);
