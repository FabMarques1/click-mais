<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/erros.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['login']) || (int) ($_SESSION['tipo_usuario'] ?? 0) < 2) {
    header('Location: index.php');
    exit;
}

$instituicao = $_SESSION['instituicao'];

function eCurriculo($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$filtroVaga = $_GET['vaga'] ?? '';

$filtroOrdem = strtoupper($_GET['ordem'] ?? 'ASC');
$filtroOrdem = in_array($filtroOrdem, ['ASC', 'DESC'], true) ? $filtroOrdem : 'ASC';

$porPagina = 10;
$paginaAtual = max(1, (int) ($_GET['pagina'] ?? 1));

try {
    // Lista de vagas para o filtro.
    $stmtVagas = $conn->prepare(
        "SELECT id, titulo FROM tbl_vaga ORDER BY titulo ASC"
    );
    $stmtVagas->execute();
    $resultVaga = $stmtVagas->get_result();
    $vagas = $resultVaga->fetch_all(MYSQLI_ASSOC);
    $stmtVagas->close();

    // Conta os currículos para calcular a paginação.
    $sqlTotal = "
        SELECT COUNT(id_curriculo) AS total
        FROM tbl_curriculo_tem_vaga cv
        INNER JOIN tbl_curriculo c ON cv.id_curriculo = c.id
        INNER JOIN tbl_usuario u ON c.id_usuario = u.id
        INNER JOIN tbl_vaga v ON cv.id_vaga = v.id
        WHERE v.id_instituicao = ?
    ";

    if ($filtroVaga !== '') {
        $sqlTotal .= " AND v.titulo = ?";
        $stmtTotal = $conn->prepare($sqlTotal);
        $stmtTotal->bind_param('is', $instituicao,$filtroVaga);
    } else {
        $stmtTotal = $conn->prepare($sqlTotal);
        $stmtTotal->bind_param('s', $instituicao);
    }

    $stmtTotal->execute();
    $totalRegistros = (int) $stmtTotal->get_result()->fetch_assoc()['total'];
    $stmtTotal->close();

    $totalPaginas = (int) ceil($totalRegistros / $porPagina);

    if ($totalPaginas > 0) {
        $paginaAtual = min($paginaAtual, $totalPaginas);
    } else {
        $paginaAtual = 1;
    }

    $offset = ($paginaAtual - 1) * $porPagina;

    // Busca os candidatos.
    $sql = "
        SELECT
            u.nome,
            u.sobrenome,
            u.email,
            v.titulo
        FROM tbl_curriculo_tem_vaga cv
        INNER JOIN tbl_curriculo c
            ON cv.id_curriculo = c.id
        LEFT JOIN tbl_usuario u
            ON c.id_usuario = u.id
        INNER JOIN tbl_vaga v
            ON cv.id_vaga = v.id
        WHERE v.id_instituicao = ?
    ";

    if ($filtroVaga !== '') {
        $sql .= " AND v.titulo = ?";
    }

    $sql .= " ORDER BY u.nome $filtroOrdem, u.sobrenome ASC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    if ($filtroVaga !== '') {
        $stmt->bind_param('isii', $instituicao, $filtroVaga, $porPagina, $offset);
    } else {
        $stmt->bind_param('iii', $instituicao, $porPagina, $offset);
    }

    $stmt->execute();
    $resultInfo = $stmt->get_result();

} catch (Throwable $erro) {
    redirecionarComErro(
        'Não foi possível carregar os currículos. Tente novamente ou contate o suporte.',
        'index.php'
    );
    exit;
}

$nomeRecrutador = $_SESSION['nome'] ?? 'Recrutador';

$parametrosPaginacao = [
    'ordem' => $filtroOrdem
];

if ($filtroVaga !== '') {
    $parametrosPaginacao['vaga'] = $filtroVaga;
}

function linkPagina(int $pagina, array $parametros): string
{
    $parametros['pagina'] = $pagina;
    return '?' . http_build_query($parametros);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Área de recrutamento do Click+ para consultar currículos e perfis de candidatos.">
    <title>Click+ | Currículos recebidos</title>
    <link rel="stylesheet" href="css/output.css">
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">
</head>

<body class="min-h-screen bg-background-light font-sans text-text antialiased">

    <!-- Cabeçalho -->
    <header class="sticky top-0 z-40 border-b border-border bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="index.php" class="text-xl font-extrabold tracking-tight text-primary">
                click<span class="text-text">+</span>
            </a>

            <div class="flex items-center gap-3 sm:gap-5">
                <span class="hidden text-sm text-text-secondary sm:inline">
                    Área de recrutamento
                </span>

                <span class="hidden h-5 w-px bg-border sm:block"></span>

                <span class="max-w-36 truncate text-sm font-semibold text-text sm:max-w-52">
                    <?= eCurriculo($nomeRecrutador) ?>
                </span>

                <a href="logout.php"
                   class="rounded-lg px-3 py-2 text-sm font-semibold text-text-secondary transition hover:bg-background-light hover:text-primary">
                    Sair
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

        <!-- Título da página -->
        <section class="mb-8">
            <a href="index.php" class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-text-secondary transition hover:text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                </svg>
                Voltar ao início
            </a>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-widest text-primary">
                        Recrutamento
                    </p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-text sm:text-4xl">
                        Currículos recebidos
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-text-secondary sm:text-base">
                        Consulte os candidatos, filtre por vaga e acesse os detalhes de cada perfil.
                    </p>
                </div>

                <div class="rounded-xl border border-border bg-white px-4 py-3 sm:min-w-40">
                    <p class="text-xs font-medium text-text-secondary">Candidaturas encontradas</p>
                    <p class="mt-1 text-2xl font-extrabold text-text">
                        <?= number_format($totalRegistros, 0, ',', '.') ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Filtros -->
        <section class="mb-6 rounded-2xl border border-border bg-white p-4 shadow-sm sm:p-5">
            <form action="ver-curriculos.php" method="GET" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_220px_auto_auto]">

                <div class="min-w-0">
                    <label for="vaga" class="mb-2 block text-sm font-semibold text-text">
                        Filtrar por vaga
                    </label>

                    <select name="vaga" id="vaga"
                            class="w-full rounded-xl border border-border bg-white px-3 py-3 text-sm text-text outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15">
                        <option value="">Todas as vagas</option>

                        <?php foreach ($vagas as $vaga): ?>
                            <option value="<?= (string) $vaga['titulo'] ?>"
                                <?= (string) $filtroVaga === (string) $vaga['titulo'] ? 'selected' : '' ?>>
                                <?= eCurriculo($vaga['titulo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="min-w-0">
                    <label for="ordem" class="mb-2 block text-sm font-semibold text-text">
                        Ordenar por nome
                    </label>

                    <select name="ordem" id="ordem"
                            class="w-full rounded-xl border border-border bg-white px-3 py-3 text-sm text-text outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15">
                        <option value="ASC" <?= $filtroOrdem === 'ASC' ? 'selected' : '' ?>>
                            A a Z
                        </option>
                        <option value="DESC" <?= $filtroOrdem === 'DESC' ? 'selected' : '' ?>>
                            Z a A
                        </option>
                    </select>
                </div>

                <button type="submit"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary/30 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16M7 12h10m-7 7h4"/>
                    </svg>
                    Aplicar filtros
                </button>

                <a href="ver-curriculos.php"
                   class="inline-flex min-h-11 items-center justify-center rounded-xl border border-border px-5 py-3 text-sm font-semibold text-text-secondary transition hover:bg-background-light hover:text-text">
                    Limpar
                </a>
            </form>
        </section>

        <!-- Lista de candidatos -->
        <section class="overflow-hidden rounded-2xl border border-border bg-white shadow-sm">
            <div class="flex flex-col gap-1 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <h2 class="font-bold text-text">Lista de candidatos</h2>
                <p class="text-sm text-text-secondary">
                    <?php if ($totalRegistros > 0): ?>
                        Exibindo <?= $offset + 1 ?>–<?= min($offset + $porPagina, $totalRegistros) ?>
                        de <?= number_format($totalRegistros, 0, ',', '.') ?> candidaturas
                    <?php else: ?>
                        Nenhuma candidatura para exibir
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($resultInfo && $resultInfo->num_rows > 0): ?>

                <!-- Tabela para telas médias e grandes -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-background-light">
                                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-text-secondary">
                                    Candidato
                                </th>
                                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-text-secondary">
                                    E-mail
                                </th>
                                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-text-secondary">
                                    Vaga desejada
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wide text-text-secondary">
                                    Ação
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-border">
                            <?php while ($rowInfo = $resultInfo->fetch_assoc()): ?>
                                <tr class="transition hover:bg-background-light/70">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-text">
                                            <?= eCurriculo($rowInfo['nome'] . ' ' . $rowInfo['sobrenome']) ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-text-secondary">
                                        <a href="mailto:<?= eCurriculo($rowInfo['email']) ?>"
                                           class="transition hover:text-primary">
                                            <?= eCurriculo($rowInfo['email']) ?>
                                        </a>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="inline-flex max-w-64 rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-medium text-primary">
                                            <?= eCurriculo($rowInfo['titulo']) ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <a href="perfil-candidato.php?email=<?= urlencode($rowInfo['email']) ?>&vaga=<?= urlencode($rowInfo['titulo']) ?>"
                                           class="inline-flex items-center justify-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-semibold text-text transition hover:border-primary hover:text-primary">
                                            Ver perfil
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                // O resultado já foi consumido na tabela acima.
                // A versão para celular é preparada antes da consulta na versão final.
                ?>

            <?php else: ?>

                <div class="px-5 py-16 text-center sm:px-8">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-background-light text-text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="size-7" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16 16 4 4M8 11h6"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-text">Nenhum currículo encontrado</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-text-secondary">
                        Não encontramos candidaturas para os filtros selecionados. Tente escolher outra vaga ou limpar os filtros.
                    </p>
                    <a href="ver-curriculos.php"
                       class="mt-5 inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-primary-dark">
                        Ver todas as candidaturas
                    </a>
                </div>

            <?php endif; ?>

            <?php if ($totalPaginas > 1): ?>
                <nav aria-label="Paginação de candidatos"
                     class="flex flex-col gap-4 border-t border-border px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                    <p class="text-sm text-text-secondary">
                        Página <?= $paginaAtual ?> de <?= $totalPaginas ?>
                    </p>

                    <div class="flex flex-wrap items-center gap-1.5">
                        <?php if ($paginaAtual > 1): ?>
                            <a href="<?= eCurriculo(linkPagina($paginaAtual - 1, $parametrosPaginacao)) ?>"
                               class="inline-flex size-10 items-center justify-center rounded-lg border border-border text-sm font-semibold text-text-secondary transition hover:border-primary hover:text-primary"
                               aria-label="Página anterior">
                                &larr;
                            </a>
                        <?php endif; ?>

                        <?php
                        $inicio = max(1, $paginaAtual - 2);
                        $fim = min($totalPaginas, $paginaAtual + 2);
                        ?>

                        <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                            <?php if ($i === $paginaAtual): ?>
                                <span aria-current="page"
                                      class="inline-flex size-10 items-center justify-center rounded-lg bg-primary text-sm font-bold text-white">
                                    <?= $i ?>
                                </span>
                            <?php else: ?>
                                <a href="<?= eCurriculo(linkPagina($i, $parametrosPaginacao)) ?>"
                                   class="inline-flex size-10 items-center justify-center rounded-lg border border-border text-sm font-semibold text-text-secondary transition hover:border-primary hover:text-primary">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($paginaAtual < $totalPaginas): ?>
                            <a href="<?= eCurriculo(linkPagina($paginaAtual + 1, $parametrosPaginacao)) ?>"
                               class="inline-flex size-10 items-center justify-center rounded-lg border border-border text-sm font-semibold text-text-secondary transition hover:border-primary hover:text-primary"
                               aria-label="Próxima página">
                                &rarr;
                            </a>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif; ?>
        </section>

        <footer class="py-8 text-center text-xs text-text-muted">
            &copy; <?= date('Y') ?> Click+. Todos os direitos reservados.
        </footer>

    </main>

    <?php require __DIR__ . '/includes/notificacao.php'; ?>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
