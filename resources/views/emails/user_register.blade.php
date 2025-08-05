<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Bem-vindo!</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
<table cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
    <tr>
        <td>
            <table cellpadding="0" cellspacing="0" width="600" align="center"
                   style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">
                <tr>
                    <td style="background-color: #4CAF50; color: white; text-align: center; padding: 20px 0; font-size: 24px;">
                        Bem-vindo a {{ env("APP_NAME") }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 30px; color: #333;">
                        <h2 style="margin-top: 0;">Olá {{ $user->name ?? "Senhor(a)." }},</h2>
                        <p>Seu registro foi realizado com sucesso! Estamos felizes em tê-lo conosco.</p>
                        <p>Sua senha temporária é: {{ $passwordPlain }}</p>
                        <p>Para acessar sua conta, clique no botão abaixo:</p>
                        <p style="text-align: center;">
                            <a
                                    href="{{  env("APP_URL") }}/admin/login"
                                    style="
                                        background-color: #4CAF50;
                                        color: white;
                                        padding: 12px 24px;
                                        text-decoration: none;
                                        border-radius: 4px;
                                        display: inline-block;
                                    "
                            >
                                Primeiro Acesso com senha temporária
                            </a>
                        </p>
                        <p style="margin-top: 30px;">Se você não criou esta conta, por favor ignore este e-mail.</p>
                        <p>Abraços,<br/>Equipe {{ env("APP_NAME") }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee; text-align: center; padding: 15px; font-size: 12px; color: #666;">
                        &copy; :-) {{ env("APP_NAME") }}. Todos os direitos reservados.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

