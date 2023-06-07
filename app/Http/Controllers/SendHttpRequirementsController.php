<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SendHttpRequirementsController extends Controller
{
    public function date_request(Request $request)
    {
        function converterData($data) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                $componentes = explode('-', $data);
                $ano = $componentes[0];
                $mes = $componentes[1];
                $dia = $componentes[2];
                return "$dia-$mes-$ano";
            }
        }

        $data = $request->input("event.triggerTime");
        preg_match('/(\d{4}-\d{2}-\d{2})/', $data, $correspondencias);
        if (isset($correspondencias[1])) {
            $data = $correspondencias[1];
        }
        $data_da_conclusao = converterData($data);
        return $data_da_conclusao;
    }
    public function task_data_request(Request $request)   
    {
        $boardId = $request->input("event.boardId");
        $token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjI1Nzk0MzQyNSwiYWFpIjoxMSwidWlkIjozOTI0NzA4NSwiaWFkIjoiMjAyMy0wNS0yMlQxNjo1NzowNi4zNzJaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6OTE2MjM2NCwicmduIjoidXNlMSJ9.b4zxCzUg5O6QPU8jbZE02MGXa7D1lKcr_uJjAKovuCw';
        $apiUrl = 'https://api.monday.com/v2';
        $headers = ['Content-Type: application/json', 'Authorization: ' . $token];

        $board_id = $boardId;
        $query = '{boards(ids: '.$board_id.') { name id items { name column_values{id type text } } } }'; //
        $data = @file_get_contents($apiUrl, false, stream_context_create([
        'http' => [
        'method' => 'POST',
        'header' => $headers,
        'content' => json_encode(['query' => $query]),
        ]
        ]));
        $responseContent = json_decode($data, true);

        return $responseContent;
    } 
    public function search_task_request(Request $request)
    {   
        $pulseId = $request->input("event.pulseId");
        $responseContent = $this->task_data_request($request);

        $pulseIdNum = count($responseContent["data"]["boards"][0]["items"][0]["column_values"]);
        $pulseIdNum -= 1;
        $i = " ";
        $numero_do_item = 0;
        while ($i != $pulseId){
            $i = $responseContent["data"]["boards"][0]["items"][$numero_do_item]["column_values"][$pulseIdNum]["text"];
            $numero_do_item += 1;
        }
        $numero_do_item -= 1;
        return $numero_do_item;
    }
    public function task_name_request(Request $request)
    {
        $numero_do_item = $this-> search_task_request($request);
        $responseContent = $this-> task_data_request($request);
        $nome_da_tarefa = $responseContent["data"]["boards"][0]["items"][$numero_do_item]['name'];
        return $nome_da_tarefa;
    }
    public function task_client_name_request(Request $request)
    {
        $numero_do_item = $this-> search_task_request($request);
        $responseContent = $this-> task_data_request($request);
        $nome_do_cliente = $responseContent["data"]["boards"][0]["items"][$numero_do_item]["column_values"][3]["text"];
        return $nome_do_cliente;
    }
    public function task_status_request(Request $request)
    {
        $numero_do_item = $this-> search_task_request($request);
        $responseContent = $this-> task_data_request($request);
        $status_da_tarefa = $responseContent["data"]["boards"][0]["items"][$numero_do_item]["column_values"][4]["text"];
        return $status_da_tarefa;
    }
    public function headers()
    {
        $headers = [
            'access-token' => '646e26706c3a20bd8ebf67bf',
            'Content-Type' => 'application/json'
        ];
        return $headers;
    }
    public function body($contactId,$mensagem) 
    {
        $body = [
            "contactId" => "$contactId",
            "message" => "$mensagem",
            "isWhisper" => false,
            "forceSend" => true,
            "verifyContact" => false
        ];
        return $body;
    }
    public function time_request()
    {
        $horario = date('H:i'); 
        $horario = (int)$horario;
        if (($horario >= 6) && ($horario < 12)){
            $comeco = "Bom dia";
        } 
        else if (($horario >= 12) && ($horario < 18)){
            $comeco = "Boa tarde";
        } 
        else if (($horario >= 18) && ($horario < 23)){
            $comeco = "Boa noite";
        }
        else{
            $comeco = "Boa noite, me desculpe pelo horário";
        }
        return $comeco;
    }
    public function main(Request $request)
    {
        $status_da_tarefa = $this->task_status_request($request);
        $nome_da_tarefa = $this->task_name_request($request);
        $nome_do_cliente = $this->task_client_name_request($request);
        $data_de_conclusao = $this->date_request($request);
        $comeco = $this->time_request();

        if ($status_da_tarefa == "Feito"){
            $status_da_tarefa = "Concluída";
        }
        if ($nome_do_cliente == 'Alvenius'){
            $contactId = "646e35de623f1ae818739794"; //grupo teste 1
        }
        else if ($nome_do_cliente == 'Mega Safe'){
            $contactId = "646e35de623f1ae818739794"; //grupo teste 2 
        }
        else if ($nome_do_cliente == 'Hungry Digital'){
            $contactId = "646e35de623f1ae818739794"; //grupo teste 3 
        }
        else{
            $contactId = "646e35de623f1ae818739794"; //não retorna nada
        }

        $mensagem = "$comeco $nome_do_cliente! A tarefa $nome_da_tarefa, foi $status_da_tarefa na data: $data_de_conclusao";

        $body = $this->body($contactId,$mensagem);
        return $body;
    }
}
