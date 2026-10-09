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

        if (!url || /^(#|javascript:|mailto:|tel:)/i.test(url)) {
            return;
        }

        let destino;

        try {
            destino = new URL(url, window.location.href);
        } catch {
            return;
        }

        // Só ativa a transição para páginas PHP do mesmo site.
        if (
            destino.origin !== window.location.origin ||
            !/\.php$/i.test(destino.pathname) ||
            destino.href === window.location.href ||
            navegando
        ) {
            return;
        }

        event.preventDefault();
        navegando = true;

        mostrarLoader();

        setTimeout(() => {
            window.location.assign(destino.href);
        }, tempoTransicao);
    });

    window.addEventListener("popstate", () => {
        mostrarLoader();
    });
})();
