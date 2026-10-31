<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database as DB;

/**
 * SERVIÇO DO DASHBOARD
 * Junta dados de vários Services para montar o que a tela principal precisa.
 * Serviços podem usar outros serviços: aqui só "compomos" (orquestramos) o resultado.
 */
final class DashboardService
{
    private EstacaoService $estacoes;
    private LeituraService $leituras;

    public function __construct()
    {
        $this->estacoes = new EstacaoService();
        $this->leituras = new LeituraService();
    }

    /** Contadores gerais mostrados no topo da página inicial. */
    public function totais(): array
    {
        return [
            'estacoes'   => (int) DB::one('SELECT COUNT(*) AS n FROM estacoes')['n'],
            'leituras'   => (int) DB::one('SELECT COUNT(*) AS n FROM leituras')['n'],
            'visitantes' => (int) DB::one('SELECT COUNT(*) AS n FROM visitantes')['n'],
        ];
    }

    /**
     * Pacote completo para a tela/API: estação escolhida, última leitura,
     * estatísticas de 24h e séries dos gráficos.
     */
    public function resumo(?int $estacaoId, int $limite = 30): array
    {
        $lista = $this->estacoes->listarAtivas();
        if (!$lista) {
            return ['estacoes' => [], 'estacao' => null, 'ultima' => null, 'stats' => null,
                    'serie' => ['labels' => [], 'temperatura' => [], 'umidade' => [], 'luminosidade' => []],
                    'totais' => $this->totais()];
        }
        // Se não escolheu estação (ou escolheu uma inválida), usa a primeira da lista
        $escolhida = $lista[0];
        foreach ($lista as $e) {
            if ((int) $e["id"] === $estacaoId) { $escolhida = $e; }
        }

        $serie = ['labels' => [], 'temperatura' => [], 'umidade' => [], 'luminosidade' => []];
        foreach ($this->leituras->ultimas((int) $escolhida['id'], $limite) as $l) {
            $serie['labels'][]       = date('H:i', strtotime($l['registrada_em']));
            $serie['temperatura'][]  = (float) $l['temperatura'];
            $serie['umidade'][]      = (float) $l['umidade'];
            $serie['luminosidade'][] = (int) $l['luminosidade'];
        }

        return [
            'estacoes' => array_map(fn($e) => ['id' => (int) $e['id'], 'nome' => $e['nome']], $lista),
            'estacao'  => [
                'id' => (int) $escolhida['id'], 'nome' => $escolhida['nome'], 'local' => $escolhida['local'],
                'online' => $escolhida['online'], 'ultimo_contato' => $escolhida['ultimo_contato'],
            ],
            'ultima'   => $this->leituras->ultima((int) $escolhida['id']),
            'stats'    => $this->leituras->estatisticas24h((int) $escolhida['id']),
            'serie'    => $serie,
            'totais'   => $this->totais(),
        ];
    }
}
