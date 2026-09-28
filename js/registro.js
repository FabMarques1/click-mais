const formRegistro = document.getElementById("formRegistro");

const estadoSelect = document.getElementById("estado");
const cidadeSelect = document.getElementById("cidade");

const cepInput = document.getElementById("cep");

const logradouroInput =
    document.getElementById("logradouro");

const bairroInput =
    document.getElementById("bairro");

const dataNascimento =
    document.getElementById("data_nascimento");

const erroData =
    document.getElementById("erroData");


/*
=====================================================
CARREGAR ESTADOS DO BANCO DE DADOS (via PHP)
=====================================================
*/

async function carregarEstados() {

    estadoSelect.innerHTML =
        '<option value="">Carregando estados...</option>';

    estadoSelect.disabled = true;


    try {

        const resposta = await fetch("get_estados.php");

        if (!resposta.ok) {
            throw new Error("Resposta não OK do servidor");
        }

        const estados = await resposta.json();

        estadoSelect.innerHTML =
            '<option value="">Selecione o estado</option>';


        estados.forEach(estado => {

            const option =
                document.createElement("option");

            option.value = estado.sigla;
            option.textContent = estado.nome;

            estadoSelect.appendChild(option);

        });


    } catch (erro) {

        console.error("Erro ao carregar estados:", erro);

        estadoSelect.innerHTML =
            '<option value="">Erro ao carregar estados</option>';

    } finally {

        estadoSelect.disabled = false;

    }

}


/*
Dispara o carregamento assim que o script roda,
substituindo o antigo array fixo "estados".
*/

carregarEstados();


/*
=====================================================
BUSCAR CIDADES DO ESTADO
=====================================================
*/

async function carregarCidades(uf) {

    cidadeSelect.innerHTML =
        '<option value="">Carregando cidades...</option>';

    cidadeSelect.disabled = true;


    if (!uf) {

        cidadeSelect.innerHTML =
            '<option value="">Selecione o estado primeiro</option>';

        return;

    }


    try {

        const resposta = await fetch(
            `get_cidades.php?estado=${uf}`
        );

        if (!resposta.ok) {
            throw new Error("Resposta não OK do servidor");
        }

        const cidades = await resposta.json();

        cidadeSelect.innerHTML =
            '<option value="">Selecione a cidade</option>';


        cidades.forEach(cidade => {

            const option =
                document.createElement("option");

            option.value = cidade.id;

            option.textContent =
                cidade.nome;

            cidadeSelect.appendChild(option);

        });


        cidadeSelect.disabled = false;


    } catch (erro) {

        console.error(
            "Erro ao carregar cidades:",
            erro
        );


        cidadeSelect.innerHTML =
            '<option value="">Erro ao carregar cidades</option>';

    }

}


estadoSelect.addEventListener("change", function () {

    carregarCidades(this.value);

});


/*
=====================================================
BUSCAR ENDEREÇO PELO CEP
=====================================================
*/

cepInput.addEventListener("blur", async function () {

    const cep =
        this.value.replace(/\D/g, "");


    if (cep.length !== 8) {
        return;
    }


    try {

        const resposta = await fetch(
            `https://viacep.com.br/ws/${cep}/json/`
        );


        const endereco =
            await resposta.json();


        if (endereco.erro) {

            alert("CEP não encontrado.");

            return;

        }


        /*
        Preencher logradouro
        */

        logradouroInput.value =
            endereco.logradouro || "";


        /*
        Preencher bairro
        */

        bairroInput.value =
            endereco.bairro || "";


        /*
        Selecionar estado
        */

        estadoSelect.value =
            endereco.uf;


        /*
        Disparar carregamento das cidades
        */

        await carregarCidades(endereco.uf);


        /*
        Selecionar a cidade retornada pelo CEP
        */

        const cidadeEncontrada =
            Array.from(cidadeSelect.options).find(option =>

                option.textContent
                    .toLowerCase() ===
                endereco.localidade
                    .toLowerCase()

            );


        if (cidadeEncontrada) {

            cidadeSelect.value =
                cidadeEncontrada.value;

        }


    } catch (erro) {

        console.error(
            "Erro ao consultar CEP:"
        );

    }

});


/*
=====================================================
MÁSCARA DE CEP
=====================================================
*/

cepInput.addEventListener("input", function () {

    let cep =
        this.value.replace(/\D/g, "");


    if (cep.length > 5) {

        cep =
            cep.substring(0, 5) +
            "-" +
            cep.substring(5, 8);

    }


    this.value = cep;

});


/*
=====================================================
DATA DE NASCIMENTO
=====================================================
*/

/*
Obtém a data atual.
*/

const hoje = new Date();


const ano =
    hoje.getFullYear();


const mes =
    String(
        hoje.getMonth() + 1
    ).padStart(2, "0");


const dia =
    String(
        hoje.getDate()
    ).padStart(2, "0");


const dataAtual =
    `${ano}-${mes}-${dia}`;


/*
Impede que o calendário
selecione uma data futura.
*/

dataNascimento.max =
    dataAtual;


/*
=====================================================
VALIDAÇÃO DA DATA
=====================================================
*/

formRegistro.addEventListener(
    "submit",
    function (event) {

        const data =
            new Date(
                dataNascimento.value +
                "T00:00:00"
            );


        const agora =
            new Date();



        if (

            !dataNascimento.value ||

            isNaN(
                data.getTime()
            ) ||

            data > agora

        ) {

            event.preventDefault();

            erroData.style.display =
                "block";

            dataNascimento.focus();


            return;

        }


        erroData.style.display =
            "none";


        if (!estadoSelect.value || !cidadeSelect.value) {

            event.preventDefault();

            alert("Selecione o estado e a cidade.");

            (estadoSelect.value ? cidadeSelect : estadoSelect).focus();

            return;

        }

    }
);