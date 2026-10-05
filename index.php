<?php
session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
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
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currículo+</title>
    <link rel="icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="css/output.css">
</head>

<body class="bg-white text-gray-900">

<header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-8">
        <a href="index.php" class="text-2xl font-extrabold tracking-tight text-[#63A893]">
            CURRÍCULO<span class="text-gray-900">+</span>
        </a>

        <nav class="hidden items-center gap-8 md:flex">
            <a href="#inicio" class="text-sm font-medium text-gray-700 transition hover:text-[#63A893]">Início</a>
            <a href="#vagas" class="text-sm font-medium text-gray-700 transition hover:text-[#63A893]">Vagas</a>
            <a href="#como-funciona" class="text-sm font-medium text-gray-700 transition hover:text-[#63A893]">Como funciona</a>

            <?php if (isset($_SESSION['login']) && $_SESSION['login'] == true): ?>
                <button type="button" id="abrir-perfil" class="text-sm font-semibold text-[#63A893] transition hover:text-[#4f907c]">
                    <?= htmlspecialchars($nome) ?>
                </button>

                <?php if ($tipoUsuario == 2): ?>
                    <a href="ver-curriculos.php" class="text-sm font-medium text-gray-700 transition hover:text-[#63A893]">Área de candidatos</a>
                <?php endif; ?>

                <a href="logout.php" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#63A893] hover:text-[#63A893]">Sair</a>
            <?php else: ?>
                <a href="login.php" class="text-sm font-semibold text-gray-700 transition hover:text-[#63A893]">Entrar</a>
                <a href="registro.php" class="rounded-lg bg-[#63A893] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4f907c]">Cadastrar</a>
            <?php endif; ?>
        </nav>

        <button type="button" class="rounded-lg border border-gray-200 p-2 text-gray-700 md:hidden" aria-label="Abrir menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
</header>

<main>

<section id="inicio" class="overflow-hidden">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 lg:grid-cols-2 lg:px-8 lg:py-28">
        <div>
            <span class="mb-5 inline-flex rounded-full bg-[#63A893]/10 px-4 py-2 text-sm font-semibold text-[#63A893]">
                Encontre novas oportunidades
            </span>

            <h1 class="max-w-2xl text-4xl font-extrabold leading-tight tracking-tight text-gray-950 sm:text-5xl lg:text-6xl">
                Encontre sua <span class="text-[#63A893]">próxima oportunidade.</span>
            </h1>

            <p class="mt-6 max-w-xl text-lg leading-8 text-gray-600">
                Encontre vagas que combinam com você e envie seu currículo de forma simples, rápida e segura.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#vagas" class="inline-flex items-center justify-center rounded-xl bg-[#63A893] px-6 py-3.5 font-semibold text-white transition hover:bg-[#4f907c]">
                    Encontrar vagas
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>

                <a href="form.php" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-6 py-3.5 font-semibold text-gray-800 transition hover:border-[#63A893] hover:text-[#63A893]">
                    Enviar meu currículo
                </a>
            </div>
        </div>

        <div class="relative hidden lg:block">
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-[#63A893]/10 blur-3xl"></div>

            <div class="relative rounded-3xl border border-gray-100 bg-gray-50 p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Oportunidades</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">Encontre sua vaga</p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#63A893]/10 text-[#63A893]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.619-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="rounded-2xl bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">Oportunidades para você</p>
                                <p class="mt-1 text-sm text-gray-500">Diversas vagas esperando por novos talentos.</p>
                            </div>

                            <span class="rounded-full bg-[#63A893]/10 px-3 py-1 text-xs font-semibold text-[#63A893]">Vagas</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#63A893]/10 text-[#63A893]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <div>
                                <p class="font-semibold text-gray-900">Currículo enviado</p>
                                <p class="text-sm text-gray-500">Simples e rápido.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="vagas" class="bg-gray-50 py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">

        <div class="max-w-2xl">
            <span class="text-sm font-bold uppercase tracking-wider text-[#63A893]">Oportunidades</span>

            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">
                Vagas disponíveis
            </h2>

            <p class="mt-4 text-gray-600">
                Confira algumas das oportunidades disponíveis e encontre uma vaga que combina com seu perfil.
            </p>
        </div>

        <div class="mt-10 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 md:grid-cols-3">

                <div class="md:col-span-1">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Buscar vaga</label>

                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 4.5a7.5 7.5 0 006.15 12.15z" />
                        </svg>

                        <input type="text" placeholder="Cargo ou palavra-chave" class="w-full rounded-xl border border-gray-200 py-3 pl-10 pr-4 text-sm outline-none transition focus:border-[#63A893] focus:ring-2 focus:ring-[#63A893]/20">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Localização</label>

                    <input type="text" placeholder="Cidade ou região" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#63A893] focus:ring-2 focus:ring-[#63A893]/20">
                </div>

                <div class="flex items-end">
                    <button type="button" class="w-full rounded-xl bg-[#63A893] px-5 py-3 font-semibold text-white transition hover:bg-[#4f907c]">
                        Buscar vagas
                    </button>
                </div>

            </div>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">

            <article class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#63A893]/40 hover:shadow-md">

                <div class="flex items-start justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#63A893]/10 text-[#63A893]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-4V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM8 7h8" />
                        </svg>
                    </div>

                    <span class="rounded-full bg-[#63A893]/10 px-3 py-1 text-xs font-semibold text-[#63A893]">
                        Disponível
                    </span>
                </div>

                <h3 class="mt-6 text-xl font-bold text-gray-900">Nome da vaga</h3>

                <p class="mt-2 text-sm font-medium text-gray-500">Nome da empresa</p>

                <div class="mt-5 space-y-2 text-sm text-gray-500">

                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#63A893]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Cidade / Estado
                    </div>

                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#63A893]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tipo de contratação
                    </div>

                </div>

                <a href="#" class="mt-6 inline-flex w-full items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-800 transition group-hover:border-[#63A893] group-hover:text-[#63A893]">
                    Ver detalhes

                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

            </article>

        </div>
    </div>
</section>

<section id="como-funciona" class="bg-white py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-bold uppercase tracking-wider text-[#63A893]">Simples assim</span>

            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">
                Como funciona?
            </h2>

            <p class="mt-4 text-gray-600">
                Encontre uma oportunidade e envie seu currículo em poucos passos.
            </p>
        </div>

        <div class="mt-14 grid gap-8 md:grid-cols-3">

            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#63A893]/10 text-xl font-bold text-[#63A893]">01</div>

                <h3 class="mt-6 text-xl font-bold text-gray-900">Encontre uma vaga</h3>

                <p class="mt-3 leading-7 text-gray-600">
                    Navegue pelas oportunidades disponíveis e encontre aquela que combina com seu perfil.
                </p>
            </div>

            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#63A893]/10 text-xl font-bold text-[#63A893]">02</div>

                <h3 class="mt-6 text-xl font-bold text-gray-900">Envie seu currículo</h3>

                <p class="mt-3 leading-7 text-gray-600">
                    Cadastre seus dados e envie seu currículo de maneira simples e segura.
                </p>
            </div>

            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#63A893]/10 text-xl font-bold text-[#63A893]">03</div>

                <h3 class="mt-6 text-xl font-bold text-gray-900">Conquiste sua oportunidade</h3>

                <p class="mt-3 leading-7 text-gray-600">
                    Seu currículo estará disponível para as oportunidades cadastradas na plataforma.
                </p>
            </div>

        </div>
    </div>
</section>

<section class="bg-[#79B8B2]">
    <div class="mx-auto max-w-7xl px-6 py-20 lg:px-8">

        <div class="flex flex-col items-center justify-between gap-8 text-center md:flex-row md:text-left">

            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    Sua próxima oportunidade pode começar aqui.
                </h2>

                <p class="mt-4 max-w-2xl text-white/80">
                    Cadastre-se e deixe seu currículo disponível para novas oportunidades.
                </p>
            </div>

            <a href="registro.php" class="shrink-0 rounded-xl bg-white px-6 py-3.5 font-semibold text-[#63A893] transition hover:bg-gray-100">
                Criar minha conta
            </a>

        </div>
    </div>
</section>

</main>

<footer class="border-t border-gray-100 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-8 sm:flex-row sm:items-center sm:justify-between lg:px-8">

        <div>
            <p class="text-lg font-extrabold text-[#63A893]">
                CURRÍCULO<span class="text-gray-900">+</span>
            </p>

            <p class="mt-1 text-sm text-gray-500">
                Conectando profissionais a oportunidades.
            </p>
        </div>

        <p class="text-sm text-gray-400">
            © 2026 Currículo+. Todos os direitos reservados.
        </p>

    </div>
</footer>

<?php if (isset($_SESSION['login']) && $_SESSION['login'] == true): ?>

<div id="perfil-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-6 backdrop-blur-sm">

    <div class="relative w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl">

        <button type="button" id="fechar-perfil" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-full text-2xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
            ×
        </button>

        <div class="text-center">

            <img src="<?= htmlspecialchars($icone) ?>" alt="Foto de perfil" class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-[#63A893]/10">

            <h2 class="mt-5 text-2xl font-bold text-gray-900">Meu perfil</h2>

            <p class="mt-1 text-gray-500">
                <?= htmlspecialchars($nome . ' ' . $sobrenome) ?>
            </p>

        </div>

        <div class="my-6 h-px bg-gray-100"></div>

        <div class="space-y-5">

            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-gray-400">Endereço</span>

                <p class="mt-1 text-sm leading-6 text-gray-700">
                    <?= htmlspecialchars($logradouro . ", " . $numero . ", " . $complemento . ", " . $bairro . ", " . $cidade . " - " . $estado . ", " . substr($cep, 0, 5) . "-" . substr($cep, 5)) ?>
                </p>
            </div>

            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-gray-400">E-mail</span>

                <p class="mt-1 text-sm text-gray-700">
                    <?= htmlspecialchars($email) ?>
                </p>
            </div>

        </div>

        <div class="mt-8">
            <button type="button" class="w-full rounded-xl bg-[#63A893] px-5 py-3 font-semibold text-white transition hover:bg-[#4f907c]">
                Editar perfil
            </button>
        </div>

    </div>
</div>

<?php endif; ?>

<script>
const abrirPerfil = document.getElementById("abrir-perfil");
const fecharPerfil = document.getElementById("fechar-perfil");
const perfilModal = document.getElementById("perfil-modal");

if (abrirPerfil && fecharPerfil && perfilModal) {
    abrirPerfil.addEventListener("click", function(event) {
        event.preventDefault();
        perfilModal.classList.remove("hidden");
        perfilModal.classList.add("flex");
    });

    fecharPerfil.addEventListener("click", function() {
        perfilModal.classList.remove("flex");
        perfilModal.classList.add("hidden");
    });

    perfilModal.addEventListener("click", function(event) {
        if (event.target === perfilModal) {
            perfilModal.classList.remove("flex");
            perfilModal.classList.add("hidden");
        }
    });

    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            perfilModal.classList.remove("flex");
            perfilModal.classList.add("hidden");
        }
    });
}
</script>

</body>
</html>