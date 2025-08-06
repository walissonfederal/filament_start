<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Política de Privacidade da {{ env("APP_NAME") }}">
    <title>Política de Privacidade - {{ env("APP_NAME") }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        h1, h2 {
            color: #0056b3;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 10px;
        }
        a {
            color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Política de Privacidade</h1>
    <p>Bem-vindo à política de privacidade da <strong>{{ env("APP_NAME") }}</strong>.
        A proteção dos seus dados é uma prioridade para nós. Este documento explica como coletamos,
        usamos, armazenamos e protegemos suas informações.
    </p>

    <h2>Informações que Coletamos</h2>
    <p>Podemos coletar os seguintes tipos de informações:</p>
    <ul>
        <li>Informações pessoais fornecidas diretamente por você, como nome, e-mail, telefone e endereço;</li>
        <li>Informações de uso do site, como endereço IP, navegador e páginas acessadas;</li>
        <li>Dados necessários para fins de pagamento, quando aplicável.</li>
    </ul>

    <h2>Uso das Informações</h2>
    <p>Utilizamos as informações coletadas para os seguintes propósitos:</p>
    <ul>
        <li>Prestar e melhorar nossos serviços;</li>
        <li>Personalizar a experiência do usuário;</li>
        <li>Enviar comunicados importantes e notificações;</li>
        <li>Cumprir obrigações legais.</li>
    </ul>

    <h2>Compartilhamento de Dados</h2>
    <p>
        A {{ env("APP_NAME") }} não vende, aluga ou compartilha suas informações pessoais com terceiros,
        exceto quando necessário para:
    </p>
    <ul>
        <li>Cumprir a legislação vigente;</li>
        <li>Executar serviços essenciais contratados por você;</li>
        <li>Proteger nossos direitos legais.</li>
    </ul>

    <h2>Armazenamento e Segurança</h2>
    <p>
        Implementamos medidas rigorosas para proteger suas informações contra acesso não autorizado,
        alteração, divulgação ou destruição. Os dados são armazenados em servidores seguros e tratados
        com confidencialidade.
    </p>

    <h2>Seus Direitos</h2>
    <p>
        Você tem o direito de acessar, corrigir ou excluir suas informações pessoais armazenadas por nós.
        Para exercer esses direitos, entre em contato através do nosso e-mail de suporte.
    </p>

    <h2>Alterações nesta Política</h2>
    <p>
        Esta política pode ser atualizada periodicamente. Recomendamos que você revise esta página
        regularmente para estar ciente de quaisquer mudanças.
    </p>

    <h2>Contato</h2>
    <p>
        Se você tiver dúvidas ou preocupações sobre esta política de privacidade, entre em contato conosco:
    </p>
    <p>
        <strong>{{ env("APP_NAME") }}</strong><br>
        CNPJ: 00.000.000/0001-00<br>
        E-mail: user@gmail.com<br>
        Telefone: (00) 90000-0000</p>

    <p>Última atualização: {{ \Illuminate\Support\Carbon::now()->toDateTimeLocalString() }}</p>
</div>
</body>
</html>
