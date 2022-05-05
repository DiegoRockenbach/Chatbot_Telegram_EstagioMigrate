<?php
    // Busca a mensagem que o Telegram mandou
    $input = file_get_contents("php://input");
    $update = json_decode($input);
    $message = $update->message;
    // Prepara as variáveis
    $banco = "banco_" . $message->from->id . ".txt";
    $text = $message->text;
    $token = "5075917162:AAESF8cXzT3molsQTnljGchDcTXMYYHZPLE";
    $chat_id = $message->chat->id;
    // Grava log
    file_put_contents("log.txt", date("c") . ' - ' . $chat_id . ' - ' . $text ."\n", FILE_APPEND);
    $apiTelegram = "https://api.telegram.org/bot".$token."/sendMessage?chat_id=".$chat_id;
    $dados = new stdClass();
    $dados->nome = null;
    $dados->vf = null;
    $dados->item = null;
    $dados->compmaisquest = null;
    $dados->compmaisafirm = null;
    $dados->compmaissn = null;
    $dados->valortotal = null;
    $dados->cpf = null;
    $dados->metpag = null;
    if (file_exists($banco)) {
        $dados = json_decode(file_get_contents($banco));
    }
    if (strpos($text, "/start") === 0) {
        $dados->nome = null;
        $dados->vf = null;
        $dados->item = null;
        $dados->compmaisquest = null;
        $dados->compmaisafirm = null;
        $dados->compmaissn = null;
        $dados->valortotal = null;
        $dados->cpf = null;
        $dados->metpag = null;
        file_put_contents($banco, json_encode($dados));
    }
    if (strpos($text, "/start") === 0) {
        // Primeira mensagem
        file_get_contents($apiTelegram."&text=" . urlencode('Seja bem vindo ao serviço da Migrate de emissão de notas fiscais!'));
        file_get_contents($apiTelegram."&text=" . urlencode('Por favor, informe seu nome:'));
        $dados->vf = true;
        file_put_contents($banco, json_encode($dados));
        die();
    }
    if ((!$dados->nome) and ($dados->vf == true)) {
        // Segunda mensagem, depois do /start
        $dados->nome = $text;
        file_get_contents($apiTelegram."&text=Seu nome é " . $dados->nome);
        $dados->vf = false;
        file_put_contents($banco, json_encode($dados));
    }
    if ($dados->compmaisquest == true){
        $dados->compmaissn = $text;
        if ($dados->compmaissn == "S"){
            $dados->compmaisafirm = true;
        }
        if ($dados->compmaissn == "N"){
            file_get_contents($apiTelegram."&text=O valor total de sua compra foi de R$ " . $dados->valortotal);
        }
        file_put_contents($banco, json_encode($dados));
    }
    if (((!$dados->valortotal) and ($dados->vf == true)) or (($dados->compmaisafirm) and ($dados->vf == true))) {
        $dados->item = $text;
        if ($dados->item == 1) {
            $dados->valortotal = $dados->valortotal + 5;
        }
        if ($dados->item == 2) {
            $dados->valortotal = $dados->valortotal + 4.50;
        }
        if ($dados->item == 3) {
            $dados->valortotal = $dados->valortotal + 12.30;
        }
        if ($dados->item == 4) {
            $dados->valortotal = $dados->valortotal + 8.10;
        }
        $dados->vf = false;
        file_get_contents($apiTelegram."&text=" . urlencode('Você deseja comprar mais algum produto? (S / N)'));
        $dados->compmaisquest = true;
        $dados->compmaisafirm = false;
        file_put_contents($banco, json_encode($dados));
        die();
    }
    if ((!$dados->valortotal) or ($dados->compmaisafirm)) {
        file_get_contents($apiTelegram."&text=" . urlencode('Qual produto você deseja comprar? (insira o código numérico)'));
        file_get_contents($apiTelegram."&text=" . urlencode('1 - Feijão (R$ 5,00)'));
        file_get_contents($apiTelegram."&text=" . urlencode('2 - Arroz (R$ 4,50)'));
        file_get_contents($apiTelegram."&text=" . urlencode('3 - Carne (R$ 12,30)'));
        file_get_contents($apiTelegram."&text=" . urlencode('4 - Pão (R$ 8,10)'));
        $dados->vf = true;
        file_put_contents($banco, json_encode($dados));
        die();
    }
    if ((!$dados->cpf) and ($dados->vf == true)) {
        // Quarta mensagem, depois do /start
        $dados->cpf = $text;
        file_get_contents($apiTelegram."&text=Seu CPF é " . $dados->cpf);
        $dados->vf = false;
        file_put_contents($banco, json_encode($dados));
    }
    if (!$dados->cpf) {
        // Terceira mensagem, depois do /start
        file_get_contents($apiTelegram."&text=" . urlencode('Por favor, informe seu CPF:'));
        $dados->vf = true;
        file_put_contents($banco, json_encode($dados));
        die();
    }
    if ((!$dados->metpag) and ($dados->vf == true)) {
        // Sexta mensagem, depois do /start
        $dados->metpag = $text;
        $dados->vf = false;
        file_put_contents($banco, json_encode($dados));
        if ($dados->metpag == 1) {
            file_get_contents($apiTelegram."&text=Seu método de pagamento escolhido foi " . $dados->metpag . " - Cartão de Crédito");
        }
        if ($dados->metpag == 2) {
            file_get_contents($apiTelegram."&text=Seu método de pagamento escolhido foi " . $dados->metpag . " - Boleto");
        }
    }
    if (!$dados->metpag) {
        // Quinta mensagem, depois do /start
        file_get_contents($apiTelegram."&text=" . urlencode('Por favor, informe seu método de pagamento:'));
        file_get_contents($apiTelegram."&text=" . urlencode('Método 1 - Cartão de crédito'));
        file_get_contents($apiTelegram."&text=" . urlencode('Método 2 - Boleto'));
        $dados->vf = true;
        file_put_contents($banco, json_encode($dados));
        die();
    }
    if (($dados->metpag) and ($dados->nome) and ($dados->cpf) and ($dados->valortotal)){
        // Sétima mensagem, depois do /start
        file_get_contents("https://api.telegram.org/bot".$token."/sendDocument?chat_id=".$chat_id."&document=" . urlencode("https://www.orimi.com/pdf-test.pdf"));
    }