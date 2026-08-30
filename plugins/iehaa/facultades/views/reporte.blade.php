<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte IEHAA - UES</title>
    <style>
        @page {
            margin: 20mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 4px solid #003087;
            padding-bottom: 20px;
        }

        .logo {
            width: 130px;
            height: auto;
            margin-bottom: 10px;
        }

        .universidad {
            font-size: 16px;
            margin: 5px 0;
        }

        .instituto {
            font-size: 18px;
            font-weight: bold;
            color: #003087;
            margin: 8px 0 20px 0;
        }

        .titulo-reporte {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            color: #1a1a1a;
            margin: 25px 0 30px 0;
        }

        .info-box {
            border: 1px solid #003087;
            border-radius: 6px;
            padding: 18px 25px;
            margin: 30px 0;
            background-color: #f8f9fa;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-item {
            margin-bottom: 12px;
        }

        .label {
            font-weight: bold;
            color: #003087;
            font-size: 14px;
        }

        .value {
            font-size: 15px;
            margin-top: 3px;
        }

        .footer {
            position: fixed;
            bottom: -18mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="instituto">
            INSTITUTO DE ESTUDIOS HISTÓRICOS, ANTROPOLÓGICOS Y ARQUEOLÓGICOS<br>
            (IEHAA)
        </div>
    </div>

    <h1 class="titulo-reporte">
        {{ $titulo ?? 'TÍTULO DEL REPORTE' }}
    </h1>

    <!-- Información del documento -->

    @foreach ($facultades as $facultad)

    <div class="info-box">
        <div class="info-grid">
            <div class="info-item">
                <div class="label">#</div>
                <div class="value">{{ $loop->iteration ?? 'IEHAA-2026-0001' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Nombre</div>
                <div class="value">{{ $facultad['nombre'] ?? '' }}</div>
            </div>
        </div>
    </div>

    @endforeach

    <div class="footer">
        Universidad de El Salvador • IEHAA • Ciudad Universitaria, San Salvador, El Salvador - {{ now()->format('d \d\e F \d\e Y \a \l\a\s H:i') }}
    </div>

</body>

</html>