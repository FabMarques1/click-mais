<?php

session_start();

if(isset($_SESSION['login'])) {
    $nome = $_SESSION['nome'];
    $sobrenome = $_SESSION['sobrenome'];
    $email = $_SESSION['email'];
    $tipoUsuario = $_SESSION['tipo_usuario'];
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CURRÍCULO+</title>

    <link rel="stylesheet" href="css/index.css?v=1">
    <link rel="stylesheet" href="css/perfil.css?v=1">

    <link
        rel="shortcut icon"
        href="assets/img/favicon.png"
        type="image/x-icon"
    >

</head>

<body>

    <header>

        <div class="container">

            <h1>
                CURRICULO<span>+</span>
            </h1>
            <nav>

                <?php if (isset($_SESSION['logado']) && $_SESSION['logado'] == true): ?>

                    <a href="#" id="abrir-perfil">
                        <?php echo htmlspecialchars($nome); ?>
                    </a>

                    <?php if ($tipoUsuario === 2): ?>

                        <a href="ver-curriculos.php">
                            Área de candidatos
                        </a>

                    <?php endif; ?>

                    <a href="logout.php">
                        Sair
                    </a>

                <?php else: ?>

                    <a href="login.php">
                        Entrar
                    </a>

                    <a href="registro.php">
                        Cadastrar
                    </a>

                <?php endif; ?>

            </nav>

        </div>

    </header>


    <main>

        <!-- HERO -->

        <section class="hero">

            <div class="container">

                <div class="hero-text">

                    <p class="tag">
                        SEU CURRÍCULO, NOVAS OPORTUNIDADES
                    </p>

                    <h2>
                        Envie seu currículo.
                        <strong>Encontre oportunidades.</strong>
                    </h2>

                    <p>
                        Envie seu currículo já pronto de forma
                        rápida e segura. Nossa plataforma facilita
                        o acesso dos analistas aos profissionais
                        em busca de novas oportunidades.
                    </p>

                    <a href="form.php" class="button">
                        Enviar meu currículo
                    </a>

                </div>

                <div class="hero-number">

                    <span>+</span>

                    <p>
                        Mais oportunidades
                    </p>

                </div>

            </div>

        </section>


        <!-- CONCEITO -->

        <section class="concept" id="conceito">

            <div class="container">

                <p class="tag">
                    O CONCEITO
                </p>

                <h2>
                    Seu currículo no lugar certo.
                </h2>

                <div class="intro">

                    <p>
                        O Curriculo+ foi desenvolvido para facilitar
                        o envio e o gerenciamento de currículos.
                        O candidato envia seu currículo já pronto,
                        enquanto os analistas podem acessar essas
                        informações para identificar profissionais
                        de acordo com as oportunidades disponíveis.
                    </p>

                </div>

                <div class="highlight">

                    <div>

                        <span>
                            01
                        </span>

                        <h3>
                            Envio
                        </h3>

                        <p>
                            Envie seu currículo já pronto de maneira
                            simples e prática, sem precisar criar um
                            novo documento.
                        </p>

                    </div>

                    <div>

                        <span>
                            02
                        </span>

                        <h3>
                            Acesso
                        </h3>

                        <p>
                            Seus dados ficam disponíveis na plataforma
                            para que os analistas possam consultar
                            os currículos enviados.
                        </p>

                    </div>

                    <div>

                        <span>
                            03
                        </span>

                        <h3>
                            Oportunidades
                        </h3>

                        <p>
                            Facilite a identificação de profissionais
                            e aumente suas chances de ser encontrado
                            para novas oportunidades.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- PILARES -->

        <section class="pillars" id="pilares">

            <div class="container">

                <p class="tag">
                    NOSSOS PILARES
                </p>

                <h2>
                    Uma ponte entre
                    profissionais e oportunidades.
                </h2>

                <div class="cards">

                    <article>

                        <div class="icon">
                            +
                        </div>

                        <h3>
                            Praticidade
                        </h3>

                        <p>
                            Envie seu currículo já pronto sem
                            precisar preencher novamente todas
                            as suas informações profissionais.
                        </p>

                        <a href="form.php">
                            Enviar currículo →
                        </a>

                    </article>


                    <article>

                        <div class="icon">
                            +
                        </div>

                        <h3>
                            Organização
                        </h3>

                        <p>
                            Os currículos enviados são armazenados
                            e organizados para facilitar o trabalho
                            dos analistas.
                        </p>

                        <a href="registro.php">
                            Saiba mais →
                        </a>

                    </article>


                    <article>

                        <div class="icon">
                            +
                        </div>

                        <h3>
                            Conexão
                        </h3>

                        <p>
                            Aproximamos profissionais que buscam
                            oportunidades dos analistas que procuram
                            novos talentos.
                        </p>

                        <a href="form.php">
                            Enviar currículo →
                        </a>

                    </article>

                </div>

            </div>

        </section>


        <!-- BENEFÍCIOS -->

        <section class="benefits" id="beneficios">

            <div class="container">

                <div class="benefits-title">

                    <p class="tag">
                        BENEFÍCIOS
                    </p>

                    <h2>
                        Mais simples.
                        <strong>Mais oportunidades.</strong>
                    </h2>

                </div>


                <div class="benefits-list">

                    <div>

                        <span>
                            01
                        </span>

                        <p>
                            Envie seu currículo já pronto.
                        </p>

                    </div>

                    <div>

                        <span>
                            02
                        </span>

                        <p>
                            Economize tempo no processo de candidatura.
                        </p>

                    </div>

                    <div>

                        <span>
                            03
                        </span>

                        <p>
                            Mantenha suas informações profissionais centralizadas.
                        </p>

                    </div>

                    <div>

                        <span>
                            04
                        </span>

                        <p>
                            Facilite o acesso dos analistas ao seu currículo.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- SEÇÃO FINAL -->

        <section class="final" id="cadastrar">

            <div class="container">

                <p class="tag">
                    SEU PRÓXIMO PASSO
                </p>

                <h2>
                    Sua próxima oportunidade
                    <span>pode começar aqui.</span>
                </h2>

                <p>
                    Envie seu currículo e deixe que os analistas
                    encontrem o profissional que estão procurando.
                </p>

                <br>

                <a href="registro.php" class="button">
                    Enviar meu currículo
                </a>

            </div>

        </section>

    </main>


    <!-- RODAPÉ -->

    <footer>

        <div class="container">

            <p>
                CURRICULO<span>+</span>
            </p>

            <small>
                © 2026 Curriculo+ - Todos os direitos reservados.
            </small>

        </div>

    </footer>


    <!-- MODAL DO PERFIL -->

    <?php if (isset($_SESSION['logado']) && $_SESSION['logado'] == true): ?>

        <div class="perfil-overlay" id="perfil-modal">

            <div class="perfil-card">

                <button
                    type="button"
                    class="fechar-perfil"
                    id="fechar-perfil"
                    aria-label="Fechar perfil"
                >
                    ×
                </button>


                <div class="perfil-header">

                    <img
                        src="assets/img/imagem.jpg"
                        alt="Foto de perfil"
                        class="foto-perfil"
                    >

                    <h1>
                        Meu perfil
                    </h1>

                    <p class="nome-usuario">

                        <?php
                        echo htmlspecialchars(
                            $nome . ' ' . $sobrenome
                        );
                        ?>

                    </p>

                </div>


                <div class="linha"></div>


                <div class="dados-perfil">

                    <div class="campo-perfil">

                        <span class="label">
                            Cidade
                        </span>

                        <span class="valor">
                            <?php
                            echo htmlspecialchars($cidade);
                            ?>
                        </span>

                    </div>


                    <div class="campo-perfil">

                        <span class="label">
                            E-mail
                        </span>

                        <span class="valor">
                            <?php
                            echo htmlspecialchars($email);
                            ?>
                        </span>

                    </div>

                </div>


                <div class="acoes-perfil">

                    <button
                        type="button"
                        class="btn-editar"
                    >
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

        // Abrir perfil
        abrirPerfil.addEventListener("click", function(event) {

            event.preventDefault();

            perfilModal.classList.add("ativo");

        });

        // Fechar pelo botão X
        fecharPerfil.addEventListener("click", function() {

            perfilModal.classList.remove("ativo");

        });

        // Fechar clicando fora do card
        perfilModal.addEventListener("click", function(event) {

            if (event.target === perfilModal) {

                perfilModal.classList.remove("ativo");

            }

        });

        // Fechar pressionando ESC
        document.addEventListener("keydown", function(event) {

            if (event.key === "Escape") {

                perfilModal.classList.remove("ativo");

            }

        });
    </script>
</body>

</html>