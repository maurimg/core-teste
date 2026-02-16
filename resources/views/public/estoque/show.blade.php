<!DOCTYPE html>
<html>
<head>
    <title>{{ $vehicle->marca }} {{ $vehicle->modelo }}</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial; background:#f4f4f4; margin:0; padding:20px;">

    <a href="{{ route('estoque.index') }}">← Voltar ao estoque</a>

    <h1>
        {{ $vehicle->marca }} {{ $vehicle->modelo }} - {{ $vehicle->ano }}
    </h1>

    <h2 style="color:green;">
        R$ {{ number_format($vehicle->preco, 2, ',', '.') }}
    </h2>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
        @foreach($vehicle->images as $image)
            <img 
                src="{{ $image->url_original }}" 
                style="width:300px; height:200px; object-fit:cover;"
            >
        @endforeach
    </div>

    <hr>

    <h2>Tenho Interesse</h2>

    @if(session('success'))
        <p style="color:green;">
            {{ session('success') }}
        </p>
    @endif

    <form method="POST" action="{{ route('estoque.interesse') }}" style="max-width:400px;">
        @csrf

        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

        <label>Nome:</label><br>
        <input type="text" name="nome" style="width:100%; padding:8px; margin-bottom:10px;"><br>

        <label>Telefone:</label><br>
        <input type="text" name="telefone" style="width:100%; padding:8px; margin-bottom:10px;"><br>

        <label>Mensagem:</label><br>
        <textarea name="mensagem" style="width:100%; padding:8px; margin-bottom:10px;"></textarea><br>

        <button type="submit" style="padding:10px 20px; background:#28a745; color:#fff; border:none; cursor:pointer;">
            Enviar Interesse
        </button>
    </form>

</body>
</html>
