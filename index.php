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

$query = "SELECT titulo, resumo, created_at, modelo, instituicao, cidade, estado, sigla
            FROM (
                SELECT
                    v.titulo,
                    v.resumo,
                    DATE_FORMAT(v.created_at, '%d/%m/%Y - %H:%i') AS created_at,
                    mv.modelo,
                    i.nome   AS instituicao,
                    c.nome   AS cidade,
                    es.nome  AS estado,
                    es.sigla AS sigla,
                    ROW_NUMBER() OVER (
                        PARTITION BY v.id_modelo_vaga
                        ORDER BY v.created_at DESC
                    ) AS posicao
                FROM tbl_vaga v
                INNER JOIN tbl_modelo_vaga mv ON v.id_modelo_vaga = mv.id
                INNER JOIN tbl_instituicao i  ON v.id_instituicao = i.id
                LEFT JOIN tbl_endereco e      ON i.id_endereco = e.id
                LEFT JOIN tbl_cidade c        ON e.id_cidade = c.id
                LEFT JOIN tbl_estado es       ON c.id_estado = es.id
            ) AS ranking
            WHERE posicao <= 6
            ORDER BY modelo, posicao";

$stmt = $conn->prepare($query);
$stmt->execute();
$vagas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click+ | Encontre sua próxima oportunidade</title>
    <link rel="icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="css/output.css">
</head>

<body class="bg-background text-text">

<!-- HEADER -->
<header class="sticky top-0 z-40 border-b border-border bg-white/95 backdrop-blur">
    <div class="relative mx-auto flex h-16 max-w-6xl items-center justify-between px-6">

        <!-- Logo -->
        <a href="index.php" class="relative z-10 text-xl font-extrabold tracking-tight text-primary">
            click<span class="text-text">+</span>
        </a>

        <!-- Navegação centralizada -->
        <nav class="absolute left-1/2 hidden -translate-x-1/2 items-center gap-10 md:flex">
            <a href="#inicio" class="text-sm font-medium text-text-secondary transition hover:text-primary">Início</a>
            <a href="#vagas" class="text-sm font-medium text-text-secondary transition hover:text-primary">Vagas</a>
            <a href="#como-funciona" class="text-sm font-medium text-text-secondary transition hover:text-primary">Como funciona</a>
        </nav>

        <!-- Ações do usuário -->
        <div class="relative z-10 hidden items-center gap-4 md:flex">
            <?php if ($logado): ?>

                <?php if ($tipoUsuario >= 2): ?>
                    <a href="ver-curriculos.php" class="text-sm font-medium text-text-secondary transition hover:text-primary">
                        Área de candidatos
                    </a>
                <?php endif; ?>

                <a href="logout.php" class="text-sm font-semibold text-text-secondary transition hover:text-primary">
                    Sair
                </a>

                <!-- Perfil do usuário à direita -->
                <button
                    type="button"
                    id="abrir-perfil"
                    class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-semibold text-text transition hover:border-primary hover:text-primary cursor-pointer"
                    aria-label="Abrir perfil de <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>"
                >

                    <span class="max-w-32 truncate"><?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?></span>
                </button>

            <?php else: ?>

                <a href="login.php" class="text-sm font-semibold text-black transition hover:text-primary">
                    Entrar
                </a>

            <?php endif; ?>
        </div>

        <!-- Botão do menu mobile -->
        <button
            type="button"
            id="abrir-menu"
            class="rounded-lg border border-border p-2 text-text-secondary md:hidden"
            aria-label="Abrir menu"
            aria-expanded="false"
            aria-controls="menu-mobile"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Menu mobile -->
    <div id="menu-mobile" class="hidden border-t border-border bg-white px-6 py-4 md:hidden">
        <div class="flex flex-col gap-3 text-sm font-medium text-text-secondary">
            <a href="#inicio" class="transition hover:text-primary">Início</a>
            <a href="#vagas" class="transition hover:text-primary">Vagas</a>
            <a href="#como-funciona" class="transition hover:text-primary">Como funciona</a>

            <?php if ($logado): ?>

                <div class="flex items-center gap-2 border-t border-border pt-3 font-semibold text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 3.75-1.452A8.96 8.96 0 0 0 21 16.05M15 19.128v-.003a6.75 6.75 0 0 0-6 0v.003m6 0a9 9 0 1 1-6 0m6 0a3 3 0 1 0-6 0m6 0a3 3 0 1 1-6 0" />
                    </svg>
                    <button type="button" id="abrir-perfil-mobile" class="min-w-0 truncate text-left">
                        <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </div>

                <?php if ($tipoUsuario == 2): ?>
                    <a href="ver-curriculos.php" class="transition hover:text-primary">Área de candidatos</a>
                <?php endif; ?>

                <a href="logout.php" class="transition hover:text-primary">Sair</a>

            <?php else: ?>

                <a href="login.php" class="transition hover:text-primary">Entrar</a>

            <?php endif; ?>
            
        </div>
    </div>
</header>

<main>

<!-- HERO -->
<section id="inicio" class="bg-background-light">
    <div class="mx-auto max-w-4xl px-6 py-20 text-center lg:py-28">
        <span class="inline-flex rounded-full bg-primary/10 px-4 py-1.5 text-sm font-semibold text-primary">
            +120 novas vagas esta semana
        </span>

        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
            Encontre sua <span class="text-primary">próxima oportunidade</span>
        </h1>

        <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-text-secondary">
            Encontre vagas, envie seu currículo e dê o próximo passo na sua carreira.
        </p>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="#vagas" class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3.5 font-semibold text-white transition hover:bg-primary-dark">
                Encontrar vagas
            </a>
            <a href="form.php" class="inline-flex items-center justify-center rounded-xl border border-border bg-white px-6 py-3.5 font-semibold transition hover:border-primary hover:text-primary">
                Enviar meu currículo
            </a>
        </div>

        <!-- Estatísticas (valores de demonstração) -->
        <dl class="mx-auto mt-14 grid max-w-2xl grid-cols-3 gap-6 border-t border-border pt-8">
            <div>
                <dt class="sr-only">Vagas</dt>
                <dd class="text-2xl font-extrabold text-primary sm:text-3xl">1.200+</dd>
                <dd class="mt-1 text-sm text-text-secondary">vagas</dd>
            </div>
            <div>
                <dt class="sr-only">Empresas</dt>
                <dd class="text-2xl font-extrabold text-primary sm:text-3xl">300+</dd>
                <dd class="mt-1 text-sm text-text-secondary">empresas</dd>
            </div>
            <div>
                <dt class="sr-only">Candidatos</dt>
                <dd class="text-2xl font-extrabold text-primary sm:text-3xl">15 mil</dd>
                <dd class="mt-1 text-sm text-text-secondary">candidatos</dd>
            </div>
        </dl>
    </div>
</section>

<!-- VAGAS -->
<section id="vagas" class="py-20">
    <div class="mx-auto max-w-6xl px-6">

        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Vagas disponíveis</h2>
            <p class="mt-3 text-text-secondary">Oportunidades atualizadas diariamente para o seu perfil.</p>
        </div>

        <!-- Busca -->
        <div class="mt-8 rounded-2xl border border-border bg-white p-4 shadow-sm">
            <div class="grid gap-4 md:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label for="busca-vaga" class="mb-2 block text-sm font-medium">Buscar vaga</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 4.5a7.5 7.5 0 006.15 12.15z" />
                        </svg>
                        <input id="busca-vaga" type="text" placeholder="Cargo ou palavra-chave" class="w-full rounded-xl border border-border py-3 pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>

                <div>
                    <label for="busca-local" class="mb-2 block text-sm font-medium">Localização</label>
                    <input id="busca-local" type="text" placeholder="Cidade ou região" class="w-full rounded-xl border border-border px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                </div>

                <div class="flex items-end">
                    <?php if (!$logado): ?>
                        <a href="login.php"><button type="button" class="w-full rounded-xl bg-primary px-8 py-3 font-semibold text-white transition hover:bg-primary-dark">Buscar</button></a>
                    <?php else: ?>
                        <button type="button" class="w-full rounded-xl bg-primary px-8 py-3 font-semibold text-white transition hover:bg-primary-dark">Buscar</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filtros por modalidade -->
        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-2" id="filtros">
                <button type="button" data-filtro="todas" class="filtro rounded-full border border-primary bg-primary px-4 py-1.5 text-sm font-medium text-white transition">Todas</button>
                <button type="button" data-filtro="Remoto" class="filtro rounded-full border border-border bg-white px-4 py-1.5 text-sm font-medium text-text-secondary transition hover:border-primary hover:text-primary">Remoto</button>
                <button type="button" data-filtro="Híbrido" class="filtro rounded-full border border-border bg-white px-4 py-1.5 text-sm font-medium text-text-secondary transition hover:border-primary hover:text-primary">Híbrido</button>
                <button type="button" data-filtro="Presencial" class="filtro rounded-full border border-border bg-white px-4 py-1.5 text-sm font-medium text-text-secondary transition hover:border-primary hover:text-primary">Presencial</button>
            </div>
            <?php
            
            ?>
            <p class="text-sm text-text-secondary"><span id="contador">6 vaga(s) encontrada(s)</span></p>
        </div>

        <!-- Cards (substituir pelo loop do banco de dados) -->
        <div id="lista-vagas" class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">

            <?php foreach ($vagas as $vaga): ?>
                <article data-modalidade="<?php echo htmlspecialchars($vaga['modelo']); ?>" class="vaga flex flex-col rounded-2xl border border-border bg-white p-6 shadow-sm transition hover:border-primary/40 hover:shadow-md">

                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-lg font-bold text-primary">A</div>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"><?php echo htmlspecialchars($vaga['modelo']); ?></span>
                    </div>

                    <h3 class="mt-5 text-lg font-bold"><?php echo htmlspecialchars($vaga['titulo']); ?></h3>

                    <div class="mt-2 space-y-1 text-sm text-text-secondary">
                        <p class="font-medium"><?php echo htmlspecialchars($vaga['instituicao']); ?></p>
                        <p class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?php echo htmlspecialchars($vaga['cidade'] . ', ' . $vaga['estado'] . ' - ' . $vaga['sigla']); ?>
                        </p>
                    </div>

                    <p class="mt-4 flex-1 text-sm leading-6 text-text-secondary">
                        <?php echo htmlspecialchars($vaga['resumo']); ?>
                    </p>

                    <div class="mt-6 flex items-center justify-between border-t border-border pt-4">
                        <span class="flex items-center gap-1.5 text-xs text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?php echo htmlspecialchars($vaga['created_at']); ?>
                        </span>
                        <a href="#" class="text-sm font-semibold text-primary transition hover:text-primary-dark">Ver vaga</a>
                    </div>
                </article>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- COMO FUNCIONA -->
<section id="como-funciona" class="bg-background-light py-20">
    <div class="mx-auto max-w-6xl px-6">

        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Como funciona?</h2>
        </div>

        <div class="mt-12 grid gap-10 md:grid-cols-3">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary text-lg font-bold text-white">01</div>
                <h3 class="mt-5 text-lg font-bold">Encontre uma vaga</h3>
                <p class="mt-2 leading-7 text-text-secondary">Pesquise oportunidades disponíveis que combinam com seu perfil.</p>
            </div>

            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary text-lg font-bold text-white">02</div>
                <h3 class="mt-5 text-lg font-bold">Envie seu currículo</h3>
                <p class="mt-2 leading-7 text-text-secondary">Escolha uma vaga e envie seu currículo de forma simples.</p>
            </div>

            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary text-lg font-bold text-white">03</div>
                <h3 class="mt-5 text-lg font-bold">Conquiste sua oportunidade</h3>
                <p class="mt-2 leading-7 text-text-secondary">Seu currículo chega até a empresa responsável pela vaga.</p>
            </div>
        </div>
    </div>
</section>

</main>

<!-- FOOTER -->
<footer class="border-t border-border bg-white">
    <div class="mx-auto max-w-6xl px-6 py-10">
        <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
            <div>
                <a href="index.php" class="text-lg font-extrabold text-primary">Currículo<span class="text-text">+</span></a>
                <p class="mt-2 max-w-xs text-sm text-text-secondary">
                    Conectando candidatos a oportunidades de emprego através do envio de currículos.
                </p>
            </div>

            <nav class="flex flex-wrap gap-6 text-sm text-text-secondary">
                <a href="#vagas" class="transition hover:text-primary">Vagas</a>
                <a href="#como-funciona" class="transition hover:text-primary">Como funciona</a>
                <a href="#" class="transition hover:text-primary">Privacidade</a>
                <a href="#" class="transition hover:text-primary">Contato</a>
            </nav>
        </div>

        <p class="mt-8 border-t border-border pt-6 text-sm text-text-muted">
            © 2026 Currículo+. Todos os direitos reservados.
        </p>
    </div>
</footer>

<?php if ($logado): ?>
<!-- MODAL DE PERFIL -->
<div id="perfil-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-6 backdrop-blur-sm">
    <div class="perfil-modal-conteudo relative w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl">

        <button type="button" id="fechar-perfil" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-full text-2xl text-text-muted transition hover:bg-background-light hover:text-text" aria-label="Fechar">
            ×
        </button>

        <div class="text-center">
            <img src="<?= htmlspecialchars($icone) ?>" alt="Foto de perfil" class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-primary/10">
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
            <button type="button" class="w-full rounded-xl bg-primary px-5 py-3 font-semibold text-white transition hover:bg-primary-dark">
                Editar perfil
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Menu mobile
const menuMobile = document.getElementById("menu-mobile");
document.getElementById("abrir-menu").addEventListener("click", () => menuMobile.classList.toggle("hidden"));

// Filtros de modalidade + contador
const filtros = document.querySelectorAll(".filtro");
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

    if(total > 0) {
        contador.textContent = total + " vaga(s) encontrada(s)";
    } else {
        contador.textContent = "Nenhuma vaga encontrada";
    }
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

// Modal de perfil
const perfilModal = document.getElementById("perfil-modal");

if (perfilModal) {
    const conteudoPerfil = perfilModal.querySelector(".perfil-modal-conteudo");
    const abrir = () => {
        perfilModal.classList.remove("hidden");
        perfilModal.classList.add("flex");
        conteudoPerfil.classList.remove("perfil-modal-conteudo-animar");
        void conteudoPerfil.offsetWidth;
        conteudoPerfil.classList.add("perfil-modal-conteudo-animar");
    };
    const fechar = () => { perfilModal.classList.remove("flex"); perfilModal.classList.add("hidden"); };

    document.getElementById("abrir-perfil").addEventListener("click", abrir);
    document.getElementById("abrir-perfil-mobile").addEventListener("click", abrir);
    document.getElementById("fechar-perfil").addEventListener("click", fechar);
    perfilModal.addEventListener("click", e => { if (e.target === perfilModal) fechar(); });
    document.addEventListener("keydown", e => { if (e.key === "Escape") fechar(); });
}
</script>

</body>
</html>
