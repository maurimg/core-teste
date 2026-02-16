<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Chat Teste</title>

<style>
body{margin:0;font-family:Arial;}
#chat{display:flex;flex-direction:column;height:100vh;}
#messages{flex:1;overflow-y:auto;padding:10px;background:#f2f2f2;}
.msg{margin:5px 0;padding:10px;border-radius:10px;max-width:85%;}
.user{background:#0d6efd;color:white;margin-left:auto;}
.ai{background:#ddd;}
#inputArea{display:flex;border-top:1px solid #ccc;}
#inputArea input{flex:1;padding:15px;font-size:16px;border:none;}
#inputArea button{padding:15px;border:none;background:#0d6efd;color:white;}
</style>
</head>

<body>

<div id="chat">
<div id="messages"></div>

<div id="inputArea">
<input id="msg" placeholder="Digite..." />
<button onclick="send()">Enviar</button>
</div>
</div>

<script>
let conversation = [];

async function send(){
    let input=document.getElementById('msg');
    let text=input.value.trim();
    if(!text) return;

    add(text,'user');

    conversation.push({
        role:'client',
        message:text
    });

    input.value='';

    let r=await fetch('/chat-test/send',{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({
            tenant_id:1,
            phone:'999999999',
            conversation:conversation,
            message:text,
            source:'chat-teste'
        })
    });

    let data=await r.json();

    add(data.reply ?? 'Erro','ai');

    conversation.push({
        role:'assistant',
        message:data.reply
    });
}

function add(t,type){
    let m=document.createElement('div');
    m.className='msg '+type;
    m.innerText=t;

    let box=document.getElementById('messages');
    box.appendChild(m);
    box.scrollTop=box.scrollHeight;
}
</script>

</body>
</html>
