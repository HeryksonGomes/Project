<?php
// Valida a data completa antes de ignorar o ano na consulta zodiacal.
function validarDataNascimento($valor)
{
    if (!is_string($valor) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $valor)) {
        return false;
    }
    $partes = explode('-', $valor);
    if (!checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0])) {
        return false;
    }
    return DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
}

function consultarSigno($data, $signos)
{
    // Um ano bissexto de referência permite comparar dia/mês, inclusive 29/02.
    $referencia = DateTimeImmutable::createFromFormat('!d/m/Y', $data->format('d/m') . '/2000');
    foreach ($signos->signo as $signo) {
        $inicio = DateTimeImmutable::createFromFormat('!d/m/Y', (string) $signo->dataInicio . '/2000');
        $fim = DateTimeImmutable::createFromFormat('!d/m/Y', (string) $signo->dataFim . '/2000');
        if ($inicio === false || $fim === false) {
            continue;
        }
        // Intervalos inclusivos. Capricórnio atravessa dezembro/janeiro.
        $pertence = $inicio <= $fim
            ? ($referencia >= $inicio && $referencia <= $fim)
            : ($referencia >= $inicio || $referencia <= $fim);
        if ($pertence) {
            return $signo;
        }
    }
    return null;
}

function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$erro = null;
$signoEncontrado = null;
$data = false;
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    $erro = 'Para descobrir seu signo, preencha o formulário na página inicial.';
} else {
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $data = validarDataNascimento($data_nascimento);
    if ($data === false) {
        http_response_code(422);
        $erro = 'Informe uma data de nascimento válida no formulário.';
    } elseif (!function_exists('simplexml_load_file')) {
        http_response_code(500);
        $erro = 'A extensão SimpleXML do PHP precisa estar habilitada para realizar a consulta.';
    } else {
        $estadoAnterior = libxml_use_internal_errors(true);
        $signos = simplexml_load_file(__DIR__ . '/signos.xml', 'SimpleXMLElement', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($estadoAnterior);
        if ($signos === false) {
            http_response_code(500);
            $erro = 'Não foi possível carregar as informações dos signos. Tente novamente mais tarde.';
        } else {
            $signoEncontrado = consultarSigno($data, $signos);
            if ($signoEncontrado === null) {
                http_response_code(500);
                $erro = 'Não foi possível encontrar um signo para a data informada.';
            }
        }
    }
}
include __DIR__ . '/layouts/header.php';
?>
<body class="pagina-resultado">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <?php if ($erro !== null): ?>
            <section class="card consulta-card border-0 shadow-sm text-center p-4 p-sm-5" aria-labelledby="titulo-erro">
                <h1 id="titulo-erro" class="h3 mb-3">Não foi possível consultar</h1>
                <p class="alert alert-warning" role="alert"><?= escapar($erro) ?></p>
                <a href="index.php" class="btn btn-primary">Voltar ao formulário</a>
            </section>
        <?php else: ?>
            <section class="resultado-card text-center rounded-3 shadow p-4 p-sm-5" aria-labelledby="signo-nome">
                <p class="resultado-legenda mb-3">Seu signo é</p>
                <h1 id="signo-nome" class="display-4 mb-4"><?= escapar($signoEncontrado->signoNome) ?></h1>
                <p class="resultado-descricao mb-4"><?= escapar($signoEncontrado->descricao) ?></p>
                <p class="small mb-4">Período: <?= escapar($signoEncontrado->dataInicio) ?> a <?= escapar($signoEncontrado->dataFim) ?></p>
                <a href="index.php" class="btn btn-outline-light">Consultar outra data</a>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
