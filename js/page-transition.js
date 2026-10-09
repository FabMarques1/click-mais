(() => {
    const loader = document.getElementById("page-loader");

    if (!loader) return;

    let navegando = false;
    const tempoTransicao = 300;

    function mostrarLoader() {
        loader.classList.remove("hidden");
        loader.classList.add("flex");
    }

    function ocultarLoader() {
        loader.classList.add("hidden");
        loader.classList.remove("flex");
        navegando = false;
    }

    ocultarLoader();

    window.addEventListener("pageshow", ocultarLoader);

    document.addEventListener("click", (event) => {
        const link = event.target.closest("a[href]");

        if (!link || event.defaultPrevented) return;

        if (
            event.button !== 0 ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey ||
            event.metaKey ||
            link.target === "_blank" ||
            link.hasAttribute("download")
        ) {
            return;
        }

        const url = link.getAttribute("href");

        // Ignora âncoras, links vazios e protocolos especiais.
        if (
            !url ||
            url.startsWith("#") ||
            /^(javascript:|mailto:|tel:)/i.test(url)
        ) {
            return;
        }

        let destino;

        try {
            destino = new URL(url, window.location.href);
        } catch {
            return;
        }

        // Somente páginas PHP do mesmo site.
        if (
            destino.origin !== window.location.origin ||
            !/\.php$/i.test(destino.pathname)
        ) {
            return;
        }

        // Ignora links para a mesma página.
        if (
            destino.pathname === window.location.pathname &&
            destino.search === window.location.search
        ) {
            return;
        }

        if (navegando) return;

        event.preventDefault();
        navegando = true;

        mostrarLoader();

        setTimeout(() => {
            window.location.assign(destino.href);
        }, tempoTransicao);
    });
})();