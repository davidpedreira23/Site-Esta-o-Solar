<?php
/**
 * SIMULADOR DO ESP32 — envia leituras falsas para a API, igual ao dispositivo real.
 * Útil para testar o site (ou gravar o vídeo) sem o hardware ligado.
 *
 * Uso:  php tools/simular_esp32.php [URL_DA_API] [API_KEY] [quantidade] [intervalo_s]
 * Ex.:  php tools/simular_esp32.php http://localhost:8000/api/leituras estacao-solar-demo-0001 20 2
 */
$url   = $argv[1] ?? 'http://localhost:8000/api/leituras';
$chave = $argv[2] ?? 'estacao-solar-demo-0001';
$qtd   = (int) ($argv[3] ?? 20);
$pausa = (int) ($argv[4] ?? 2);

$temp = 25.0; $umid = 60.0; $luz = 50;
for ($i = 1; $i <= $qtd; $i++) {
    // Variação aleatória pequena, como um sensor de verdade
    $temp = max(-10, min(45, $temp + (mt_rand(-10, 10) / 10)));
    $umid = max(10, min(95, $umid + (mt_rand(-20, 20) / 10)));
    $luz  = max(0, min(100, $luz + mt_rand(-8, 8)));
    $corpo = json_encode([
        'temperatura' => round($temp, 1), 'umidade' => round($umid, 1),
        'luminosidade' => $luz, 'ldr_bruto' => (int) round($luz * 40.95),
    ]);

    // POST sem depender da extensão cURL
    $ctx = stream_context_create(['http' => [
        'method' => 'POST', 'content' => $corpo, 'ignore_errors' => true, 'timeout' => 5,
        'header' => "Content-Type: application/json\r\nX-API-KEY: $chave\r\n",
    ]]);
    @file_get_contents($url, false, $ctx);
    preg_match('#HTTP/\S+ (\d+)#', $http_response_header[0] ?? '', $m);
    $http = $m[1] ?? 'sem resposta';

    echo "[$i/$qtd] $corpo -> HTTP $http\n";
    if ($i < $qtd) sleep($pausa);
}
