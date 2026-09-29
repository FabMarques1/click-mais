(() => {
    const input = document.getElementById("curriculo");
    if (!input) return;

    const fileText = document.getElementById("fileText");
    const fileSubtext = document.getElementById("fileSubtext");
    const fileError = document.getElementById("fileError");
    const container = document.getElementById("previewContainer");
    const status = document.getElementById("previewStatus");
    const pdf = document.getElementById("pdfPreview");
    const text = document.getElementById("textPreview");
    const link = document.getElementById("previewLink");
    let previewUrl = null;
    let selection = 0;

    function clearPreview() {
        selection++;
        pdf.hidden = true;
        pdf.removeAttribute("src");
        text.hidden = true;
        text.textContent = "";
        link.hidden = true;
        link.removeAttribute("href");
        container.hidden = true;
        status.textContent = "";
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
    }

    function reset() {
        clearPreview();
        fileText.textContent = "Clique ou arraste seu arquivo aqui";
        fileSubtext.textContent = "Prévia: PDF, DOC ou DOCX até 2MB. Envio: PDF.";
        fileError.textContent = "";
        input.setCustomValidity("");
        input.removeAttribute("aria-invalid");
    }

    function reject(message) {
        reset();
        input.value = "";
        input.setAttribute("aria-invalid", "true");
        fileError.textContent = message;
    }

    input.addEventListener("change", async () => {
        reset();
        const file = input.files[0];
        if (!file) return;
        const extension = file.name.split(".").pop().toLowerCase();
        if (!file.name.includes(".") || !["pdf", "doc", "docx"].includes(extension)) {
            reject("Formato inválido. Selecione um arquivo PDF, DOC ou DOCX.");
            return;
        }
        if (file.size === 0 || file.size > 2 * 1024 * 1024) {
            reject("Selecione um arquivo não vazio de até 2 MB.");
            return;
        }

        fileText.textContent = file.name;
        fileSubtext.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";
        if (extension !== "pdf") {
            const message = "O envio aceita apenas PDF. Salve o currículo em PDF e selecione o arquivo.";
            input.setCustomValidity(message);
            fileSubtext.textContent += " — Para enviar, selecione a versão em PDF.";
        }
        container.hidden = false;
        status.textContent = "Preparando prévia…";
        const currentSelection = selection;

        try {
            const buffer = await file.arrayBuffer();
            if (currentSelection !== selection) return;

            if (extension === "pdf") {
                const header = new TextDecoder("ascii").decode(buffer.slice(0, 5));
                if (header !== "%PDF-") throw new Error("Invalid PDF");
                previewUrl = URL.createObjectURL(new Blob([buffer], { type: "application/pdf" }));
                pdf.src = previewUrl;
                pdf.hidden = false;
                link.href = previewUrl;
                link.hidden = false;
                status.textContent = "Se a prévia não aparecer, abra o PDF em outra aba.";
            } else {
                const content = extension === "doc"
                    ? window.docToText(buffer)
                    : (await window.mammoth.extractRawText({ arrayBuffer: buffer })).value;
                if (currentSelection !== selection) return;
                if (content === null) throw new Error("Unsupported DOC");
                text.textContent = content;
                text.hidden = false;
                status.textContent = content.trim()
                    ? "Prévia do texto, processada neste dispositivo. Imagens e diagramação não são exibidas."
                    : "Não foi encontrado texto para exibir. O documento pode conter apenas imagens.";
            }
        } catch {
            if (currentSelection !== selection) return;
            clearPreview();
            fileError.textContent = "Não foi possível gerar a prévia. Verifique se o arquivo abre normalmente e se não está protegido por senha.";
        }
    });

    input.form?.addEventListener("reset", reset);
    window.addEventListener("pagehide", clearPreview);
    window.addEventListener("pageshow", (event) => {
        if (event.persisted && input.files.length) input.dispatchEvent(new Event("change"));
    });
})();
