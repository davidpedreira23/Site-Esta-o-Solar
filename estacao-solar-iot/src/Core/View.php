<?php
declare(strict_types=1);

namespace App\Core;

/** Renderiza um arquivo de views/ dentro do layout padrão. */
final class View
{
    public static function render(string $nome, array $dados = [], string $layout = 'layout/main'): string
    {
        $raiz = dirname(__DIR__, 2) . '/views/';

        // Renderiza a página e guarda o resultado em $conteudo
        $conteudo = (static function (string $arquivo, array $dados): string {
            extract($dados, EXTR_SKIP);
            ob_start();
            require $arquivo;
            return (string) ob_get_clean();
        })($raiz . $nome . '.php', $dados);

        if ($layout === '') {
            return $conteudo;
        }
        // Encaixa a página dentro do layout (cabeçalho + menu + rodapé)
        return (static function (string $arquivo, string $conteudo, array $dados): string {
            extract($dados, EXTR_SKIP);
            ob_start();
            require $arquivo;
            return (string) ob_get_clean();
        })($raiz . $layout . '.php', $conteudo, $dados);
    }
}
