<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Painel de Leads</title>

    <style>
        body {
            font-family: Arial;
            margin: 40px;
            background: #f5f6fa;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #222;
            color: #fff;
        }

        tr:hover {
            background: #f1f1f1;
        }

        select {
            padding: 5px;
        }

        button {
            padding: 6px 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<h1>Painel de Leads</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Telefone</th>
            <th>Interesse</th>
            <th>Status</th>
            <th>Vendedor</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody id="leadsTable"></tbody>
</table>

<script>
async function loadLeads() {
    const response = await fetch('/api/leads?tenant_id=1');
    const data = await response.json();

    const table = document.getElementById('leadsTable');
    table.innerHTML = '';

    data.leads.forEach(lead => {
        const row = `
            <tr>
                <td>${lead.id}</td>
                <td>${lead.name ?? ''}</td>
                <td>${lead.phone ?? ''}</td>
                <td>${lead.interest ?? ''}</td>
                <td>
                    <select id="status-${lead.id}">
                        <option value="new" ${lead.status=='new'?'selected':''}>Novo</option>
                        <option value="qualified" ${lead.status=='qualified'?'selected':''}>Qualificado</option>
                        <option value="closed" ${lead.status=='closed'?'selected':''}>Fechado</option>
                    </select>
                </td>
                <td>${lead.forwarded_to ?? '-'}</td>
                <td>
                    <button onclick="updateLead(${lead.id})">
                        Salvar
                    </button>
                </td>
            </tr>
        `;

        table.innerHTML += row;
    });
}

async function updateLead(id) {
    const status = document.getElementById(`status-${id}`).value;

    await fetch(`/api/leads/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status: status
        })
    });

    alert('Status atualizado!');
    loadLeads();
}

loadLeads();
</script>

</body>
</html>
