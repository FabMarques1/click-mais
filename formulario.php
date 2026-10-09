<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("config/database.php");

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$nome = $_SESSION['nome'] ?? '';
$sobrenome = $_SESSION['sobrenome'] ?? '';
$email = $_SESSION['email'] ?? '';
$cep = $_SESSION['cep'] ?? '';
$logradouro = $_SESSION['logradouro'] ?? '';
$complemento = $_SESSION['complemento'] ?? '';
$bairro = $_SESSION['bairro'] ?? '';
$numero = $_SESSION['numero'] ?? '';
$cidade = $_SESSION['cidade'] ?? '';
$estado = $_SESSION['estado'] ?? '';

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$stmt = $conn->prepare("SELECT nome FROM tbl_cidade WHERE id = ?");
$stmt->bind_param("i", $cidade);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nomeCidade = $row['nome'] ?? '';
$stmt->close();

$partesEndereco = array_filter(
    [
        $logradouro,
        $numero,
        $complemento,
        $bairro,
        $nomeCidade,
        $estado,
        $cep !== '' ? substr($cep, 0, 5) . '-' . substr($cep, 5) : ''
    ],
    fn($valor) => trim((string) $valor) !== ''
);

$enderecoCompleto = implode(', ', $partesEndereco);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Envie seu currículo e candidate-se às oportunidades disponíveis no Click+.">
    <title>Click+ | Envio de currículo</title>
    <link rel="stylesheet" href="css/output.css">
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">
</head>

<body class="min-h-screen overflow-x-hidden bg-background-light font-sans text-text antialiased">

    <main>
        <section class="relative isolate min-h-screen overflow-hidden py-5 sm:py-8 lg:py-10">

            <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-24 -z-10 size-80 rounded-full bg-primary/10 blur-3xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-24 -left-24 -z-10 size-80 rounded-full bg-primary/10 blur-3xl"></div>

            <div class="container relative mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Cabeçalho -->
                <header class="mb-7 flex items-center justify-between">
                    <a href="index.php" class="inline-flex items-center gap-1 text-2xl font-extrabold tracking-tight text-primary">
                        click<span class="text-text">+</span>
                    </a>

                    <a href="index.php" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-semibold text-text-secondary transition hover:border-primary hover:text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                        </svg>
                        Voltar
                    </a>
                </header>

                <div class="grid min-w-0 grid-cols-1 items-start gap-7 lg:grid-cols-5 lg:gap-12 xl:gap-16">

                    <!-- Apresentação -->
                    <aside class="min-w-0 lg:col-span-2 lg:pt-10">

                        <span class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-2 text-xs font-bold tracking-wide text-primary">
                            <span class="size-2 rounded-full bg-primary"></span>
                            TRABALHE CONOSCO
                        </span>

                        <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight text-text sm:text-4xl xl:text-5xl">
                            Sua próxima oportunidade começa
                            <span class="text-primary">aqui.</span>
                        </h1>

                        <p class="mt-4 max-w-md text-base leading-7 text-text-secondary">
                            Envie e salve seu currículo para participar de futuros processos seletivos. Preencha os campos no formulário e finalize sua candidatura.
                        </p>

                        <div class="mt-8 space-y-5">

                            <div class="flex items-center gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 3v4M8 3v4M3 10h18"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-text">Conte-nos sobre você</h2>
                                    <p class="text-sm text-text-secondary">Dê o pitch de você mesmo, destaque suas habilidades e experiências.</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v18h16V8l-6-6Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M8 13h8M8 17h8"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-text">Envie o currículo</h2>
                                    <p class="text-sm text-text-secondary">Monte e escolha o currículo que mais se encaixa com você.</p>
                                </div>
                            </div>

                        </div>
                    </aside>

                    <!-- Formulário -->
                    <section class="min-w-0 lg:col-span-3">

                        <div class="overflow-hidden rounded-2xl border border-border bg-background shadow-xl shadow-slate-900/5 sm:rounded-3xl">

                            <div class="border-b border-border px-5 py-5 sm:px-7">
                                <h2 class="text-2xl font-extrabold tracking-tight text-text">
                                    Envie seu currículo
                                </h2>
                                <p class="mt-1 text-sm text-text-secondary">
                                    Preencha os campos e finalize sua candidatura.
                                </p>
                            </div>

                            <form action="enviar-curriculo.php" method="POST" enctype="multipart/form-data" class="space-y-6 p-5 sm:p-7">

                                <!-- Dados pessoais -->
                                <section class="space-y-4">

                                    <div class="flex items-center gap-3">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-extrabold text-primary">01</span>
                                        <h3 class="font-bold text-text">Dados pessoais</h3>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                        <div class="min-w-0">
                                            <label for="nome" class="mb-1.5 block text-sm font-semibold text-text">Nome</label>
                                            <input type="text" id="nome" value="<?= e($nome) ?>" readonly class="w-full min-w-0 cursor-not-allowed rounded-xl border border-border bg-background-light px-3 py-2.5 text-sm text-text-secondary outline-none">
                                        </div>

                                        <div class="min-w-0">
                                            <label for="sobrenome" class="mb-1.5 block text-sm font-semibold text-text">Sobrenome</label>
                                            <input type="text" id="sobrenome" value="<?= e($sobrenome) ?>" readonly class="w-full min-w-0 cursor-not-allowed rounded-xl border border-border bg-background-light px-3 py-2.5 text-sm text-text-secondary outline-none">
                                        </div>

                                    </div>

                                    <div class="min-w-0">
                                        <label for="email" class="mb-1.5 block text-sm font-semibold text-text">E-mail</label>
                                        <input type="email" id="email" value="<?= e($email) ?>" readonly class="w-full min-w-0 cursor-not-allowed rounded-xl border border-border bg-background-light px-3 py-2.5 text-sm text-text-secondary outline-none">
                                    </div>

                                    <div class="min-w-0">
                                        <label for="endereco" class="mb-1.5 block text-sm font-semibold text-text">Endereço</label>
                                        <input type="text" id="endereco" value="<?= e($enderecoCompleto) ?>" readonly class="w-full min-w-0 cursor-not-allowed rounded-xl border border-border bg-background-light px-3 py-2.5 text-sm text-text-secondary outline-none">
                                    </div>

                                </section>

                                <div class="h-px w-full bg-border"></div>

                                <!-- Perfil profissional -->
                                <section class="space-y-4">

                                    <div class="flex items-center gap-3">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-extrabold text-primary">02</span>
                                        <h3 class="font-bold text-text">Perfil profissional</h3>
                                    </div>

                                    <div>
                                        <label for="resumoProfissional" class="mb-1.5 block text-sm font-semibold text-text">
                                            Resumo profissional
                                            <span class="font-normal text-text-muted">(opcional)</span>
                                        </label>

                                        <textarea name="resumoProfissional" id="resumoProfissional" maxlength="200" rows="3" placeholder="Experiências, habilidades e objetivos profissionais..." class="w-full resize-y rounded-xl border border-border bg-background px-4 py-3 text-sm leading-6 text-text outline-none transition placeholder:text-text-muted focus:border-primary focus:ring-2 focus:ring-primary/10"></textarea>
                                    </div>

                                </section>

                                <div class="h-px w-full bg-border"></div>

                                <!-- Currículo -->
                                <section class="space-y-4">

                                    <div class="flex items-center gap-3">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-extrabold text-primary">03</span>
                                        <h3 class="font-bold text-text">Currículo</h3>
                                    </div>

                                    <div>
                                        <label for="curriculo" class="mb-1.5 block text-sm font-semibold text-text">
                                            Arquivo do currículo <span class="text-danger">*</span>
                                        </label>

                                        <div id="fileArea" class="relative flex min-w-0 flex-col items-center justify-center gap-3 overflow-hidden rounded-xl border-2 border-dashed border-border bg-background-light px-4 py-6 text-center transition hover:border-primary/50 hover:bg-primary/5">

                                            <div class="pointer-events-none flex size-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-6" aria-hidden="true">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                    <path d="M14 2v6h6M8 13h8M8 17h8"/>
                                                </svg>
                                            </div>

                                            <div class="pointer-events-none min-w-0">
                                                <p id="fileText" class="break-words text-sm font-semibold text-text">
                                                    Clique para anexar seu currículo
                                                </p>
                                                <p id="fileSubtext" class="mt-1 text-xs text-text-secondary">
                                                    PDF, DOC ou DOCX
                                                </p>
                                            </div>

                                            <input type="file" name="curriculo" id="curriculo" accept=".pdf,.doc,.docx" required aria-describedby="fileSubtext fileError" class="absolute inset-0 z-20 h-full w-full cursor-pointer opacity-0">

                                        </div>

                                        <p id="fileError" role="alert" class="mt-2 hidden text-sm text-danger"></p>

                                        <!-- Pré-visualização mantida para o JavaScript -->
                                        <div id="previewContainer" class="mt-4 hidden min-w-0 overflow-hidden rounded-xl border border-border bg-background">

                                            <div class="flex flex-col gap-3 border-b border-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <h3 id="previewTitle" class="break-words text-sm font-bold text-text">Pré-visualização do currículo</h3>
                                                    <p id="previewStatus" class="mt-1 text-xs text-text-secondary" role="status"></p>
                                                </div>

                                                <a id="previewLink" target="_blank" rel="noopener noreferrer" class="hidden w-fit shrink-0 text-sm font-semibold text-primary hover:text-primary-dark">
                                                    Abrir arquivo
                                                </a>
                                            </div>

                                            <div class="min-w-0 p-3">
                                                <iframe id="pdfPreview" title="Pré-visualização do currículo em PDF" class="hidden h-80 w-full rounded-lg border border-border sm:h-[28rem]"></iframe>
                                                <pre id="textPreview" class="hidden max-h-80 w-full overflow-auto whitespace-pre-wrap break-words rounded-lg bg-background-light p-4 text-xs leading-5 text-text-secondary sm:text-sm"></pre>
                                            </div>

                                        </div>
                                    </div>

                                </section>

                                <!-- Envio -->
                                <div class="space-y-4 border-t border-border pt-5">

                                    <p class="text-xs leading-5 text-text-secondary">
                                        Seus dados serão utilizados para fins de recrutamento e seleção.
                                    </p>

                                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-2 active:scale-[0.99] sm:text-base">
                                        Enviar currículo

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/>
                                        </svg>
                                    </button>

                                </div>

                            </form>
                        </div>

                        <p class="mt-3 text-center text-xs text-text-muted">
                            O envio do currículo não garante contratação.
                        </p>

                    </section>

                </div>

                <footer class="mt-8 border-t border-border pt-5">
                    <p class="text-center text-xs text-text-muted">
                        &copy; <?= date('Y') ?> Click+. Todos os direitos reservados.
                    </p>
                </footer>

            </div>
        </section>
    </main>

    <script src="js/vendor/docToText.js" defer></script>
    <script src="js/vendor/mammoth.browser.min.js" defer></script>
    <script src="js/curriculo.js?v=4" defer></script>

    <?php require_once("includes/notificacao.php"); ?>

</body>
</html>

<?php
$conn->close();
?>