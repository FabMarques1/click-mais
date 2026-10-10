<?php
session_start();

require_once __DIR__ . '/config/database.php';

$logado = isset($_SESSION['login']) && $_SESSION['login'] == true;

if ($logado) {
    $nome = $_SESSION['nome'] ?? '';
    $sobrenome = $_SESSION['sobrenome'] ?? '';
    $email = $_SESSION['email'] ?? '';
    $tipoUsuario = $_SESSION['tipo_usuario'] ?? 0;
    $icone = $_SESSION['icone'] ?? 'assets/avatar/default_avt_icon.jpg';
    $logradouro = $_SESSION['logradouro'] ?? '';
    $numero = $_SESSION['numero'] ?? '';
    $complemento = $_SESSION['complemento'] ?? '';
    $bairro = $_SESSION['bairro'] ?? '';
    $cidade = $_SESSION['cidade'] ?? '';
    $estado = $_SESSION['estado'] ?? '';
    $cep = $_SESSION['cep'] ?? '';

    $cidadeEstado = trim($cidade . ($estado ? ' - ' . $estado : ''));
    $cepFormatado = strlen($cep) === 8 ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $cep;
    $endereco = implode(', ', array_filter([$logradouro, $numero, $complemento, $bairro, $cidadeEstado, $cepFormatado]));
}

// --- Filtros recebidos pela URL ---
$busca = trim($_GET['search'] ?? '');
$localBusca = trim($_GET['local'] ?? '');
$instituicoesSelecionadas = array_values(array_unique(array_filter(array_map('intval', $_GET['instituicao'] ?? []))));
$ordenar = ($_GET['ordenar'] ?? 'recente') === 'antigo' ? 'antigo' : 'recente';

$condicoes = ['v.ativo = 1'];
$parametros = [];
$tipos = '';

if ($busca !== '') {
    foreach (preg_split('/\s+/', $busca, -1, PREG_SPLIT_NO_EMPTY) as $palavra) {
        $condicoes[] = '(v.titulo LIKE ? OR v.resumo LIKE ? OR v.descricao LIKE ? OR i.nome LIKE ?)';
        $like = '%' . $palavra . '%';
        array_push($parametros, $like, $like, $like, $like);
        $tipos .= 'ssss';
    }
}

if ($localBusca !== '') {
    foreach (preg_split('/\s+/', $localBusca, -1, PREG_SPLIT_NO_EMPTY) as $palavra) {
        $condicoes[] = '(c.nome LIKE ? OR es.nome LIKE ? OR es.sigla LIKE ?)';
        $like = '%' . $palavra . '%';
        array_push($parametros, $like, $like, $like);
        $tipos .= 'sss';
    }
}

// Guardamos as condições sem o filtro de instituição para montar a lista do dropdown
// (assim a contagem de cada instituição reflete a busca/local atuais).
$condicoesSemInstituicao = $condicoes;
$parametrosSemInstituicao = $parametros;
$tiposSemInstituicao = $tipos;

if (!empty($instituicoesSelecionadas)) {
    $placeholders = implode(',', array_fill(0, count($instituicoesSelecionadas), '?'));
    $condicoes[] = "i.id IN ($placeholders)";
    foreach ($instituicoesSelecionadas as $id) {
        $parametros[] = $id;
        $tipos .= 'i';
    }
}

$ordem = $ordenar === 'antigo' ? 'ASC' : 'DESC';

// --- Total de resultados (para decidir se a paginação aparece) ---
$queryTotal = "SELECT COUNT(*) AS total
               FROM tbl_vaga v
               INNER JOIN tbl_modelo_vaga mv ON v.id_modelo_vaga = mv.id
               INNER JOIN tbl_instituicao i ON v.id_instituicao = i.id
               LEFT JOIN tbl_endereco e ON i.id_endereco = e.id
               LEFT JOIN tbl_cidade c ON e.id_cidade = c.id
               LEFT JOIN tbl_estado es ON c.id_estado = es.id
               WHERE " . implode(' AND ', $condicoes);

$stmtTotal = $conn->prepare($queryTotal);

if ($tipos !== '') {
    $stmtTotal->bind_param($tipos, ...$parametros);
}

$stmtTotal->execute();
$totalRegistros = (int) $stmtTotal->get_result()->fetch_assoc()['total'];
$stmtTotal->close();

$porPagina = 30;
$totalPaginas = (int) ceil($totalRegistros / $porPagina);
$paginaAtual = max(1, (int) ($_GET['pagina'] ?? 1));
$paginaAtual = $totalPaginas > 0 ? min($paginaAtual, $totalPaginas) : 1;
$offset = ($paginaAtual - 1) * $porPagina;

$query = "SELECT v.titulo, v.resumo,
                 DATE_FORMAT(v.created_at, '%d/%m/%Y - %H:%i') AS created_at,
                 mv.modelo, i.nome AS instituicao, c.nome AS cidade, es.nome AS estado, es.sigla AS sigla
          FROM tbl_vaga v
          INNER JOIN tbl_modelo_vaga mv ON v.id_modelo_vaga = mv.id
          INNER JOIN tbl_instituicao i ON v.id_instituicao = i.id
          LEFT JOIN tbl_endereco e ON i.id_endereco = e.id
          LEFT JOIN tbl_cidade c ON e.id_cidade = c.id
          LEFT JOIN tbl_estado es ON c.id_estado = es.id
          WHERE " . implode(' AND ', $condicoes) . "
          ORDER BY v.created_at $ordem
          LIMIT ? OFFSET ?";

$parametrosPagina = $parametros;
$parametrosPagina[] = $porPagina;
$parametrosPagina[] = $offset;
$tiposPagina = $tipos . 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($tiposPagina, ...$parametrosPagina);
$stmt->execute();
$vagas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Instituições disponíveis para o filtro, com a contagem de vagas de cada uma.
$queryInstituicoes = "SELECT i.id, i.nome, COUNT(*) AS total
                       FROM tbl_vaga v
                       INNER JOIN tbl_instituicao i ON v.id_instituicao = i.id
                       LEFT JOIN tbl_endereco e ON i.id_endereco = e.id
                       LEFT JOIN tbl_cidade c ON e.id_cidade = c.id
                       LEFT JOIN tbl_estado es ON c.id_estado = es.id
                       WHERE " . implode(' AND ', $condicoesSemInstituicao) . "
                       GROUP BY i.id, i.nome
                       ORDER BY i.nome ASC";

$stmtInst = $conn->prepare($queryInstituicoes);

if ($tiposSemInstituicao !== '') {
    $stmtInst->bind_param($tiposSemInstituicao, ...$parametrosSemInstituicao);
}

$stmtInst->execute();
$instituicoes = $stmtInst->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtInst->close();

$conn->close();

$totalFiltrosAtivos = count($instituicoesSelecionadas) + ($ordenar !== 'recente' ? 1 : 0);

$parametrosLimpar = array_filter(['search' => $busca, 'local' => $localBusca], fn($valor) => $valor !== '');
$linkLimparFiltros = 'vagas.php' . ($parametrosLimpar ? '?' . http_build_query($parametrosLimpar) : '');

// Parâmetros atuais (busca, local, instituição, ordenação) preservados ao trocar de página.
$parametrosPaginacao = ['ordenar' => $ordenar];

if ($busca !== '') {
    $parametrosPaginacao['search'] = $busca;
}

if ($localBusca !== '') {
    $parametrosPaginacao['local'] = $localBusca;
}

if (!empty($instituicoesSelecionadas)) {
    $parametrosPaginacao['instituicao'] = $instituicoesSelecionadas;
}

function linkPagina(int $pagina, array $parametros): string
{
    $parametros['pagina'] = $pagina;
    return '?' . http_build_query($parametros);
}

function localDaVaga(array $vaga): string
{
    $cidadeEstado = $vaga['estado'] && $vaga['sigla'] ? $vaga['estado'] . ' - ' . $vaga['sigla'] : '';
    $partes = array_filter([$vaga['cidade'] ?? null, $cidadeEstado ?: null]);

    return $partes ? implode(', ', $partes) : 'Local não informado';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click+ | Vagas disponíveis</title>
    <link rel="icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="css/output.css">
</head>

<body class="bg-background text-text font-[Arial,sans-serif]">

    <div id="page-loader" class="hidden fixed inset-0 z-[9999] items-center justify-center bg-background">
        <div class="h-12 w-12 animate-spin rounded-full border-4 border-primary-light border-t-primary"></div>
    </div>

    <header class="sticky top-0 z-40 border-b border-border bg-white/95 backdrop-blur-xl">
        <div class="relative mx-auto flex h-16 max-w-6xl items-center justify-between px-6">

            <a href="index.php"
                class="relative z-10 text-xl font-extrabold tracking-tight text-primary transition-all duration-300 hover:-translate-y-0.5 hover:drop-shadow-[0_0_8px_var(--color-primary)]">
                click<span class="text-text">+</span>
            </a>

            <nav class="absolute left-1/2 hidden -translate-x-1/2 items-center gap-10 md:flex">
                <a href="index.php#inicio"
                    class="text-sm font-medium text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:text-primary hover:drop-shadow-[0_0_8px_var(--color-primary)]">Início</a>
                <a href="vagas.php"
                    class="text-sm font-medium text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:text-primary hover:drop-shadow-[0_0_8px_var(--color-primary)]">Vagas</a>
                <a href="index.php#como-funciona"
                    class="text-sm font-medium text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:text-primary hover:drop-shadow-[0_0_8px_var(--color-primary)]">Como
                    funciona</a>
            </nav>

            <div class="relative z-10 hidden items-center gap-4 md:flex">
                <?php if ($logado): ?>

                    <?php if ($tipoUsuario >= 2): ?>
                        <a href="ver-curriculos.php"
                            class="text-sm font-medium text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:text-primary hover:drop-shadow-[0_0_8px_var(--color-primary)]">
                            Área de candidatos
                        </a>
                    <?php endif; ?>

                    <a href="logout.php"
                        class="text-sm font-semibold text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:text-primary hover:drop-shadow-[0_0_8px_var(--color-primary)]">
                        Sair
                    </a>

                    <button type="button" id="abrir-perfil"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-semibold text-text transition-all duration-300 hover:-translate-y-0.5 hover:border-primary hover:bg-primary/5 hover:text-primary hover:shadow-[0_0_15px_var(--color-primary)]"
                        aria-label="Abrir perfil de <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>">
                        <span class="max-w-32 truncate"><?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>

                <?php else: ?>

                    <a href="login.php"
                        class="text-sm font-semibold text-black transition-all duration-300 hover:-translate-y-0.5 hover:text-primary hover:drop-shadow-[0_0_8px_var(--color-primary)]">
                        Entrar
                    </a>

                <?php endif; ?>
            </div>

            <button type="button" id="abrir-menu"
                class="rounded-lg border border-border p-2 text-text-secondary transition-all duration-300 hover:border-primary hover:bg-primary/5 hover:text-primary md:hidden"
                aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-mobile">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <div id="menu-mobile" class="hidden border-t border-border bg-white px-6 py-4 md:hidden">
            <div class="flex flex-col gap-3 text-sm font-medium text-text-secondary">
                <a href="index.php#inicio"
                    class="transition-all duration-300 hover:translate-x-1 hover:text-primary">Início</a>
                <a href="vagas.php" class="transition-all duration-300 hover:translate-x-1 hover:text-primary">Vagas</a>
                <a href="index.php#como-funciona"
                    class="transition-all duration-300 hover:translate-x-1 hover:text-primary">Como funciona</a>

                <?php if ($logado): ?>

                    <div class="flex items-center gap-2 border-t border-border pt-3 font-semibold text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 3.75-1.452A8.96 8.96 0 0 0 21 16.05M15 19.128v-.003a6.75 6.75 0 0 0-6 0v.003m6 0a9 9 0 1 1-6 0m6 0a3 3 0 1 0-6 0m6 0a3 3 0 1 1-6 0" />
                        </svg>
                        <button type="button" id="abrir-perfil-mobile"
                            class="min-w-0 truncate text-left transition-colors hover:text-primary-dark">
                            <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </div>

                    <?php if ($tipoUsuario == 2): ?>
                        <a href="ver-curriculos.php"
                            class="transition-all duration-300 hover:translate-x-1 hover:text-primary">Área de candidatos</a>
                    <?php endif; ?>

                    <a href="logout.php" class="transition-all duration-300 hover:translate-x-1 hover:text-primary">Sair</a>

                <?php else: ?>

                    <a href="login.php"
                        class="transition-all duration-300 hover:translate-x-1 hover:text-primary">Entrar</a>

                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>

        <section class="relative isolate overflow-hidden bg-background-light">
            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
                <div
                    class="absolute -top-24 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-primary/10 blur-3xl sm:h-96 sm:w-96">
                </div>
                <div
                    class="absolute bottom-0 right-0 h-64 w-64 translate-x-1/3 translate-y-1/3 rounded-full bg-primary/10 blur-3xl">
                </div>
            </div>

            <div class="mx-auto max-w-6xl px-6 py-14">

                <div class="max-w-2xl mt-6">
                    <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Vagas disponíveis</h1>
                    <p class="mt-3 text-text-secondary">
                        <?php if ($busca !== ''): ?>
                            Resultados para "<?= htmlspecialchars($busca) ?>"
                        <?php else: ?>
                            Encontre vagas que mais se encaixam com o seu perfil.
                        <?php endif; ?>
                    </p>
                </div>

                <form action="vagas.php" method="GET">
                    <div class="mt-8 rounded-2xl border border-border bg-white p-4 shadow-sm">
                        <div class="grid gap-4 md:grid-cols-[1fr_1fr_auto]">
                            <div>
                                <label for="busca-vaga" class="mb-2 block text-sm font-medium">Buscar vaga</label>
                                <div class="relative">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-text-muted"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 4.5a7.5 7.5 0 006.15 12.15z" />
                                    </svg>
                                    <input name="search" id="busca-vaga" type="text"
                                        value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Cargo ou palavra-chave"
                                        class="w-full rounded-xl border border-border py-3 pl-10 pr-4 text-sm outline-none transition-all duration-300 focus:border-primary focus:ring-2 focus:ring-primary/20">
                                </div>
                            </div>

                            <div>
                                <label for="busca-local" class="mb-2 block text-sm font-medium">Localização</label>
                                <input name="local" id="busca-local" type="text"
                                    value="<?= htmlspecialchars($localBusca, ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Cidade ou região"
                                    class="w-full rounded-xl border border-border px-4 py-3 text-sm outline-none transition-all duration-300 focus:border-primary focus:ring-2 focus:ring-primary/20">
                            </div>

                            <div class="flex items-end">
                                <button type="submit"
                                    class="w-full rounded-xl bg-primary px-8 py-3 font-semibold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-primary-dark hover:shadow-lg hover:shadow-primary/20 active:translate-y-0">Buscar</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-2" id="filtros">
                            <button type="button" data-filtro="todas"
                                class="filtro rounded-full border border-primary bg-primary px-4 py-1.5 text-sm font-medium text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:shadow-primary/15 active:translate-y-0">Todas</button>
                            <button type="button" data-filtro="Remoto"
                                class="filtro rounded-full border border-border bg-white px-4 py-1.5 text-sm font-medium text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:border-primary hover:text-primary hover:shadow-md hover:shadow-primary/10 active:translate-y-0">Remoto</button>
                            <button type="button" data-filtro="Híbrido"
                                class="filtro rounded-full border border-border bg-white px-4 py-1.5 text-sm font-medium text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:border-primary hover:text-primary hover:shadow-md hover:shadow-primary/10 active:translate-y-0">Híbrido</button>
                            <button type="button" data-filtro="Presencial"
                                class="filtro rounded-full border border-border bg-white px-4 py-1.5 text-sm font-medium text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:border-primary hover:text-primary hover:shadow-md hover:shadow-primary/10 active:translate-y-0">Presencial</button>

                            <div class="relative" id="filtrar-wrapper">
                                <button type="button" id="abrir-filtrar" aria-haspopup="true" aria-expanded="false"
                                    aria-controls="painel-filtrar"
                                    class="inline-flex items-center gap-2 rounded-full border border-border bg-white px-4 py-1.5 text-sm font-medium text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:border-primary hover:text-primary hover:shadow-md hover:shadow-primary/10 active:translate-y-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21.75v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                                    </svg>
                                    Filtrar
                                    <?php if ($totalFiltrosAtivos > 0): ?>
                                        <span
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-xs font-bold text-white"><?= $totalFiltrosAtivos ?></span>
                                    <?php endif; ?>
                                    <svg id="chevron-filtrar" xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 transition-transform duration-300" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div id="painel-filtrar"
                                    class="hidden absolute left-0 top-full z-30 mt-2 w-72 rounded-2xl border border-border bg-white p-5 shadow-xl shadow-primary/10">

                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-wide text-text-muted">Ordenar
                                            por</h4>
                                        <div class="mt-3 space-y-2">
                                            <label class="flex cursor-pointer items-center gap-2 text-sm">
                                                <input type="radio" name="ordenar" value="recente"
                                                    <?= $ordenar === 'recente' ? 'checked' : '' ?>
                                                    class="h-4 w-4 accent-primary">
                                                Mais recente
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 text-sm">
                                                <input type="radio" name="ordenar" value="antigo" <?= $ordenar === 'antigo' ? 'checked' : '' ?> class="h-4 w-4 accent-primary">
                                                Mais antigo
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-5 border-t border-border pt-5">
                                        <h4 class="text-xs font-bold uppercase tracking-wide text-text-muted">
                                            Instituição</h4>
                                        <div class="mt-3 max-h-48 space-y-2 overflow-y-auto pr-1">
                                            <?php foreach ($instituicoes as $inst): ?>
                                                <label
                                                    class="flex cursor-pointer items-center justify-between gap-2 text-sm">
                                                    <span class="flex items-center gap-2">
                                                        <input type="checkbox" name="instituicao[]"
                                                            value="<?= (int) $inst['id'] ?>" <?= in_array((int) $inst['id'], $instituicoesSelecionadas, true) ? 'checked' : '' ?>
                                                            class="h-4 w-4 rounded accent-primary">
                                                        <?= htmlspecialchars($inst['nome']) ?>
                                                    </span>
                                                    <span class="text-text-muted"><?= (int) $inst['total'] ?></span>
                                                </label>
                                            <?php endforeach; ?>

                                            <?php if (empty($instituicoes)): ?>
                                                <p class="text-sm text-text-muted">Nenhuma instituição encontrada.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-5 flex items-center justify-between gap-3 border-t border-border pt-4">
                                        <a href="<?= htmlspecialchars($linkLimparFiltros, ENT_QUOTES, 'UTF-8') ?>"
                                            class="text-sm font-semibold text-text-secondary transition-colors hover:text-primary">Limpar</a>
                                        <button type="submit"
                                            class="rounded-xl bg-primary px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-dark">Aplicar
                                            filtros</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-text-secondary"><span id="contador"><?= count($vagas) ?> vaga(s)
                                encontrada(s)</span></p>
                    </div>
                </form>

                <div class="mb-16">

                    <div id="lista-vagas" class="mt-6 mb-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">

                        <?php foreach ($vagas as $vaga): ?>
                            <article data-modalidade="<?= htmlspecialchars($vaga['modelo']) ?>"
                                class="vaga group flex flex-col rounded-2xl border border-border bg-white p-6 shadow-sm">

                                <div class="flex items-start justify-between">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-lg font-bold text-primary transition-all duration-300 group-hover:bg-primary group-hover:text-white group-hover:shadow-md group-hover:shadow-primary/20">
                                        <?= htmlspecialchars(mb_strtoupper(mb_substr($vaga['instituicao'], 0, 1))) ?>
                                    </div>
                                    <span
                                        class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary transition-colors duration-300 group-hover:bg-primary/15"><?= htmlspecialchars($vaga['modelo']) ?></span>
                                </div>

                                <h3 class="mt-5 text-lg font-bold transition-colors duration-300 group-hover:text-primary">
                                    <?= htmlspecialchars($vaga['titulo']) ?></h3>

                                <div class="mt-2 space-y-1 text-sm text-text-secondary">
                                    <p class="font-medium"><?= htmlspecialchars($vaga['instituicao']) ?></p>
                                    <p class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-muted" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <?= htmlspecialchars(localDaVaga($vaga)) ?>
                                    </p>
                                </div>

                                <p class="mt-4 flex-1 text-sm leading-6 text-text-secondary">
                                    <?= htmlspecialchars($vaga['resumo']) ?>
                                </p>

                                <div class="mt-6 flex items-center justify-between border-t border-border pt-4">
                                    <span class="flex items-center gap-1.5 text-xs text-text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <?= htmlspecialchars($vaga['created_at']) ?>
                                    </span>
                                    <a href="#"
                                        class="text-sm font-semibold text-primary transition-all duration-300 hover:translate-x-0.5 hover:text-primary-dark hover:drop-shadow-[0_0_6px_var(--color-primary)]">Ver
                                        vaga</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                        <?php if (empty($vagas)): ?>
                            <section
                                class="col-span-full overflow-hidden rounded-2xl border border-border bg-white shadow-sm mb-6">
                                <div class="px-5 py-16 text-center sm:px-8">
                                    <div
                                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.6" class="size-7" aria-hidden="true">
                                            <circle cx="11" cy="11" r="7" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16 16 4 4M8 11h6" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 font-bold text-text">Nenhuma vaga encontrada</h3>

                                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-text-secondary">
                                        Não encontramos vagas com esse nome. Tente outros termos de busca ou remova alguns
                                        filtros.
                                    </p>

                                    <a href="vagas.php"
                                        class="mt-5 inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white">
                                        Ver todas as vagas
                                    </a>
                                </div>
                            </section>
                        <?php endif; ?>

                    

                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Paginação de vagas"
                            class="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-between">

                            <p class="text-sm text-text-secondary">
                                Página <?= $paginaAtual ?> de <?= $totalPaginas ?>
                            </p>

                            <div class="flex flex-wrap items-center justify-center gap-1.5">
                                <?php if ($paginaAtual > 1): ?>
                                    <a href="<?= htmlspecialchars(linkPagina($paginaAtual - 1, $parametrosPaginacao), ENT_QUOTES, 'UTF-8') ?>"
                                        class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-white text-sm font-semibold text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:border-primary hover:text-primary"
                                        aria-label="Página anterior">
                                        &larr;
                                    </a>
                                <?php endif; ?>

                                <?php
                                $inicioPagina = max(1, $paginaAtual - 2);
                                $fimPagina = min($totalPaginas, $paginaAtual + 2);
                                ?>

                                <?php for ($i = $inicioPagina; $i <= $fimPagina; $i++): ?>
                                    <?php if ($i === $paginaAtual): ?>
                                        <span aria-current="page"
                                            class="inline-flex size-10 items-center justify-center rounded-lg bg-primary text-sm font-bold text-white">
                                            <?= $i ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="<?= htmlspecialchars(linkPagina($i, $parametrosPaginacao), ENT_QUOTES, 'UTF-8') ?>"
                                            class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-white text-sm font-semibold text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:border-primary hover:text-primary">
                                            <?= $i ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($paginaAtual < $totalPaginas): ?>
                                    <a href="<?= htmlspecialchars(linkPagina($paginaAtual + 1, $parametrosPaginacao), ENT_QUOTES, 'UTF-8') ?>"
                                        class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-white text-sm font-semibold text-text-secondary transition-all duration-300 hover:-translate-y-0.5 hover:border-primary hover:text-primary"
                                        aria-label="Próxima página">
                                        &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </nav>
                    <?php endif; ?>

                </div>
            </div>
        </section>

    </main>

    <footer class="border-t border-border bg-white">
        <div class="mx-auto max-w-6xl px-6 py-10">
            <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                <div>
                    <a href="index.php"
                        class="inline-block text-lg font-extrabold text-primary transition-all duration-300 hover:-translate-y-0.5 hover:drop-shadow-[0_0_8px_var(--color-primary)]">click<span
                            class="text-text">+</span></a>
                    <p class="mt-2 max-w-xs text-sm text-text-secondary">
                        Conectando candidatos a oportunidades de emprego através do envio de currículos.
                    </p>
                </div>

                <nav class="flex flex-wrap gap-6 text-sm text-text-secondary">
                    <a href="vagas.php"
                        class="transition-all duration-300 hover:-translate-y-0.5 hover:text-primary">Vagas</a>
                    <a href="index.php#como-funciona"
                        class="transition-all duration-300 hover:-translate-y-0.5 hover:text-primary">Como funciona</a>
                    <a href="#" class="transition-all duration-300 hover:text-primary">Privacidade</a>
                    <a href="#" class="transition-all duration-300 hover:text-primary">Contato</a>
                </nav>
            </div>

            <p class="mt-8 border-t border-border pt-6 text-sm text-text-muted">
                © 2026 Click+. Todos os direitos reservados.
            </p>
        </div>
    </footer>

    <?php if ($logado): ?>
        <div id="perfil-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-6 backdrop-blur-sm">
            <div class="perfil-modal-conteudo relative w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl">

                <button type="button" id="fechar-perfil"
                    class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-full text-2xl text-text-muted transition-all duration-300 hover:rotate-90 hover:bg-background-light hover:text-text"
                    aria-label="Fechar">
                    ×
                </button>

                <div class="text-center">
                    <img src="<?= htmlspecialchars($icone) ?>" alt="Foto de perfil"
                        class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-primary/10 transition-all duration-300 hover:ring-primary/30">
                    <h2 class="mt-5 text-2xl font-bold">Meu perfil</h2>
                    <p class="mt-1 text-text-secondary"><?= htmlspecialchars($nome . ' ' . $sobrenome) ?></p>
                </div>

                <div class="my-6 h-px bg-border"></div>

                <div class="space-y-5">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wide text-text-muted">Endereço</span>
                        <p class="mt-1 text-sm leading-6"><?= htmlspecialchars($endereco) ?></p>
                    </div>

                    <div>
                        <span class="text-xs font-bold uppercase tracking-wide text-text-muted">E-mail</span>
                        <p class="mt-1 text-sm"><?= htmlspecialchars($email) ?></p>
                    </div>
                </div>

                <div class="mt-8">
                    <button type="button"
                        class="w-full rounded-xl bg-primary px-5 py-3 font-semibold text-white shadow-md shadow-primary/15 transition-all duration-300 hover:-translate-y-0.5 hover:bg-primary-dark hover:shadow-lg hover:shadow-primary/25 active:translate-y-0">
                        Editar perfil
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Menu mobile
        const menuMobile = document.getElementById("menu-mobile");
        const botaoMenu = document.getElementById("abrir-menu");

        botaoMenu.addEventListener("click", () => {
            const aberto = !menuMobile.classList.contains("hidden");

            menuMobile.classList.toggle("hidden");
            botaoMenu.setAttribute("aria-expanded", String(!aberto));
        });

        // Fechar menu mobile após selecionar uma seção
        menuMobile.querySelectorAll('a[href^="#"], a[href^="index.php#"]').forEach(link => {
            link.addEventListener("click", () => {
                menuMobile.classList.add("hidden");
                botaoMenu.setAttribute("aria-expanded", "false");
            });
        });

        // Filtros de modalidade + contador (aplicados sobre os resultados já carregados)
        const filtros = document.querySelectorAll(".filtro");
        const contador = document.getElementById("contador");
        const ativo = ["bg-primary", "text-white", "border-primary"];
        const inativo = ["bg-white", "text-text-secondary", "border-border"];

        function filtrarVagas(modalidade) {
            const vagas = document.querySelectorAll(".vaga");
            let total = 0;

            vagas.forEach(vaga => {
                const mostrar = modalidade === "todas" || vaga.dataset.modalidade === modalidade;
                vaga.classList.toggle("hidden", !mostrar);

                if (mostrar) total++;
            });

            contador.textContent = total > 0
                ? total + " vaga(s) encontrada(s)"
                : "Nenhuma vaga encontrada";
        }

        filtros.forEach(botao => {
            botao.addEventListener("click", () => {
                filtros.forEach(b => {
                    b.classList.remove(...ativo);
                    b.classList.add(...inativo);
                });

                botao.classList.remove(...inativo);
                botao.classList.add(...ativo);

                filtrarVagas(botao.dataset.filtro);
            });
        });

        // Dropdown "Filtrar" (instituição + ordenação), estilo Mercado Livre
        const filtrarWrapper = document.getElementById("filtrar-wrapper");
        const botaoFiltrar = document.getElementById("abrir-filtrar");
        const painelFiltrar = document.getElementById("painel-filtrar");
        const chevronFiltrar = document.getElementById("chevron-filtrar");

        if (filtrarWrapper && botaoFiltrar && painelFiltrar) {
            const fecharPainel = () => {
                painelFiltrar.classList.add("hidden");
                botaoFiltrar.setAttribute("aria-expanded", "false");
                chevronFiltrar.classList.remove("rotate-180");
            };

            botaoFiltrar.addEventListener("click", (evento) => {
                evento.stopPropagation();
                const aberto = !painelFiltrar.classList.contains("hidden");

                painelFiltrar.classList.toggle("hidden");
                botaoFiltrar.setAttribute("aria-expanded", String(!aberto));
                chevronFiltrar.classList.toggle("rotate-180", !aberto);
            });

            document.addEventListener("click", (evento) => {
                if (!filtrarWrapper.contains(evento.target)) fecharPainel();
            });

            document.addEventListener("keydown", (evento) => {
                if (evento.key === "Escape") fecharPainel();
            });
        }

        // Modal de perfil
        const perfilModal = document.getElementById("perfil-modal");

        if (perfilModal) {
            const conteudoPerfil = perfilModal.querySelector(".perfil-modal-conteudo");
            const botaoPerfil = document.getElementById("abrir-perfil");
            const botaoPerfilMobile = document.getElementById("abrir-perfil-mobile");
            const botaoFecharPerfil = document.getElementById("fechar-perfil");

            const abrir = () => {
                perfilModal.classList.remove("hidden");
                perfilModal.classList.add("flex");
                document.body.classList.add("overflow-hidden");

                conteudoPerfil.classList.remove("perfil-modal-conteudo-animar");
                void conteudoPerfil.offsetWidth;
                conteudoPerfil.classList.add("perfil-modal-conteudo-animar");
            };

            const fechar = () => {
                perfilModal.classList.remove("flex");
                perfilModal.classList.add("hidden");
                document.body.classList.remove("overflow-hidden");
            };

            if (botaoPerfil) {
                botaoPerfil.addEventListener("click", abrir);
            }

            if (botaoPerfilMobile) {
                botaoPerfilMobile.addEventListener("click", () => {
                    menuMobile.classList.add("hidden");
                    botaoMenu.setAttribute("aria-expanded", "false");
                    abrir();
                });
            }

            if (botaoFecharPerfil) {
                botaoFecharPerfil.addEventListener("click", fechar);
            }

            perfilModal.addEventListener("click", e => {
                if (e.target === perfilModal) fechar();
            });

            document.addEventListener("keydown", e => {
                if (e.key === "Escape") fechar();
            });
        }
    </script>

    <script src="js/page-transition.js"></script>

</body>

</html>