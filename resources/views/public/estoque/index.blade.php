<!DOCTYPE html>
<html>
<head>
    <title>Estoque de Veículos</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial; background:#f4f4f4; margin:0; padding:20px;">

    <h1 style="text-align:center;">Estoque de Veículos</h1>

    @if($vehicles->isEmpty())
        <p style="text-align:center;">Nenhum veículo disponível no momento.</p>
    @endif

    <div style="display:flex; flex-wrap:wrap; gap:20px; justify-content:center;">
        @foreach($vehicles as $vehicle)
            <div style="background:#fff; width:300px; padding:15px; border-radius:5px; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
                
                <h2 style="margin:0 0 10px 0;">
                    {{ $vehicle->marca }} {{ $vehicle->modelo }}
                </h2>

                <p><strong>Ano:</strong> {{ $vehicle->ano }}</p>

                <p><strong>Preço:</strong> 
                    R$ {{ number_format($vehicle->preco, 2, ',', '.') }}
                </p>

                @if($vehicle->images->first())
                    <img 
                        src="{{ $vehicle->images->first()->url_original }}" 
                        style="width:100%; height:180px; object-fit:cover; margin-bottom:10px;"
                    >
                @else
                    <div style="width:100%; height:180px; background:#ddd; display:flex; align-items:center; justify-content:center;">
                        Sem imagem
                    </div>
                @endif

                <a href="{{ route('estoque.show', $vehicle->id) }}"
                   style="display:block; text-align:center; padding:10px; background:#007bff; color:#fff; text-decoration:none; border-radius:4px;">
                    Ver detalhes
                </a>
            </div>
        @endforeach
    </div>

</body>
</html>
