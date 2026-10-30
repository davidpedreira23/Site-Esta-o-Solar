<?php
declare(strict_types=1);

namespace App\Core;

/** Erro de validação: carrega um array [campo => mensagem]. Vira 422 na API. */
class ValidationException extends \RuntimeException
{
    public function __construct(public array $errors)
    {
        parent::__construct('Dados inválidos.');
    }
}

/** Registro não encontrado -> 404. */
class NotFoundException extends \RuntimeException {}

/** Falta de autenticação/permissão -> 401 / 403. */
class HttpException extends \RuntimeException
{
    public function __construct(public int $status, string $mensagem)
    {
        parent::__construct($mensagem);
    }
}
