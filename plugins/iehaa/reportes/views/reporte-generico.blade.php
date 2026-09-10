<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} — IEHAA</title>
    <style>
        @page { margin: 20mm 16mm 22mm 16mm; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #23262b;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
        }

        .encabezado {
            border-bottom: 2px solid #b3261e;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .encabezado table { width: 100%; border-collapse: collapse; }
        .encabezado td { vertical-align: middle; }

        .encabezado .logo { width: 46px; }
        .encabezado .logo img { width: 46px; height: auto; }

        .encabezado .textos { padding-left: 12px; }
        .encabezado .universidad {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #6b7075;
        }
        .encabezado .instituto {
            font-size: 13px;
            font-weight: bold;
            color: #17181c;
        }

        .titulo {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #17181c;
            margin: 0 0 4px;
        }

        .meta {
            font-size: 9px;
            color: #6b7075;
            margin-bottom: 12px;
        }

        table.datos {
            width: 100%;
            border-collapse: collapse;
        }

        table.datos thead th {
            background: #17181c;
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: left;
            padding: 7px 8px;
        }

        table.datos tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e4e6ea;
            vertical-align: top;
        }

        table.datos tbody tr:nth-child(even) td {
            background: #f7f7f8;
        }

        .sin-datos {
            padding: 24px;
            text-align: center;
            color: #6b7075;
            border: 1px solid #e4e6ea;
        }

        .pie {
            position: fixed;
            bottom: -14mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #8b9098;
            border-top: 1px solid #e4e6ea;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="encabezado">
        <table>
            <tr>
                @if ($logo)
                <td class="logo"><img src="{{ $logo }}" alt="UES"></td>
                @endif
                <td class="textos">
                    <div class="universidad">{{ \Iehaa\Reportes\Classes\GeneradorReportes::UNIVERSIDAD }}</div>
                    <div class="instituto">{{ \Iehaa\Reportes\Classes\GeneradorReportes::INSTITUTO }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h1 class="titulo">{{ $titulo }}</h1>
    <div class="meta">Total de registros: <strong>{{ $total }}</strong> &nbsp;•&nbsp; Generado el {{ $generado }}</div>

    @if ($total === 0)
        <div class="sin-datos">No hay registros para mostrar.</div>
    @else
        <table class="datos">
            <thead>
                <tr>
                    @foreach ($columnas as $columna)
                        <th>{{ $columna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    <tr>
                        @foreach ($fila as $celda)
                            <td>{{ is_bool($celda) ? ($celda ? 'Sí' : 'No') : ($celda ?? '') }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="pie">
        {{ \Iehaa\Reportes\Classes\GeneradorReportes::UNIVERSIDAD }} • IEHAA • Ciudad Universitaria, San Salvador
    </div>
</body>
</html>
