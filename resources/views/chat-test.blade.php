<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat Teste IA</title>

<style>
body{
    margin:0;
    font-family: Arial, Helvetica, sans-serif;
    background:#f2f2f2;
}

.chat-wrapper{
    display:flex;
    flex-direction:column;
    height:100vh;
    max-width:700px;
    margin:auto;
    background:#fff;
}

.chat-header{
    background:#111;
    color:#fff;
    padding:14px;
    text-align:center;
    font-weight:bold;
}

.chat-messages{
    flex:1;
    overflow-y:auto;
    padding:15px;
    display:flex;
    flex-direction:column;
    gap:10px;
}

.msg{
    max-width:85%;
    padding:10px 14px;
    border-radius:14px;
    line-height:1.4;
    font-size:15px;
}

.msg-user{
    background:#0d6efd;
    color:white;
    align-self:flex-end;
}

.msg-ai{
    background:#e5e5ea;
    color:black;
    align-self:flex-start;
}

.chat-input{
    display:flex;
    border-top:1px solid #ccc;
}

.chat-input input{
    flex:1;
    padding:14px;
    border:none;
    font-size:16px;
    outline:none;
}

.chat-input button{
    padding:14px 18px;
    border:none;
    background:#0d6efd;
    color:white;
    font-weight:bold;
    cursor:pointer;
}

/* Ajustes para celular */
@media (max-width:600px){
    .chat-wrapper{
        max-width:100%;
        height:100vh;
    }

    .msg{
        max-width:92%;
        font-size:16px;
    }

    .chat-input input{
        font-size:18px;
    }

    .chat-input button{
        font-size:16px;
    }
}
</style>
</head>

<body>

<div class="chat-wrapper">
    <div class="chat-header">
        Teste Atendimento IA
    </div>

    <div class="chat-messages" id="chatBox">
        <!-- mensagens aparecem aqui -->
    </div>

    <div class="chat-input">
        <input type="text" id="messageInput" placeholder="Digite sua mensagem...">
        <button onclick="sendMessage()">Enviar</button>
    </div>
</div>

<script>
async function sendMessage(){
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if(!text) return;

    addMessage(text, 'user');
    input.value = '';

    const response = await fetch('/chat-test/send', {
        method: 'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({message:text})
    });

    const data = await response.json();

    addMessage(data.reply ?? 'Sem resposta', 'ai');
}

function addMessage(text, type){
    const box = document.getElementById('chatBox');

    const msg = document.createElement('div');
    msg.classList.add('msg');
    msg.classList.add(type === 'user' ? 'msg-user' : 'msg-ai');
    msg.innerText = text;

    box.appendChild(msg);
    box.scrollTop = box.scrollHeight;
}
</script>

</body>
</html>
