```html
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Gavetas - IEHAA UES</title>

    <style>
        @page {
            margin: 18mm 20mm 20mm 20mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
        }

        /* =========================
           ENCABEZADO
        ========================= */

        .header {
            text-align: center;
            border-bottom: 3px solid #003087;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .universidad {
            font-size: 15px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .instituto {
            font-size: 17px;
            font-weight: bold;
            color: #003087;
            text-transform: uppercase;
        }

        .siglas {
            font-size: 13px;
            font-weight: bold;
            color: #555;
            margin-top: 3px;
        }

        /* =========================
           TÍTULO
        ========================= */

        .titulo-reporte {
            text-align: center;
            font-size: 21px;
            font-weight: bold;
            color: #1a1a1a;
            text-transform: uppercase;
            margin: 20px 0 25px 0;
        }

        /* =========================
           RESUMEN
        ========================= */

        .resumen {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 12px;
        }

        /* =========================
           ARCHIVERO
        ========================= */

        .archivero {
            border: 1px solid #c9cfd8;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .archivero-header {
            background-color: #003087;
            color: white;
            padding: 10px 12px;
        }

        .archivero-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .archivero-header td {
            vertical-align: middle;
        }

        /* Correlativo */

        .correlativo {
            width: 45px;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.5);
            padding-right: 10px;
        }

        /* Nombre */

        .nombre-archivero {
            padding-left: 12px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* =========================
           DATOS
        ========================= */

        .datos {
            width: 100%;
            border-collapse: collapse;
        }

        .datos td {
            padding: 10px 13px;
        }

        .label {
            display: block;
            font-size: 9px;
            font-weight: bold;
            color: #003087;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .value {
            font-size: 11px;
            color: #333;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            position: fixed;
            bottom: -13mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 6px;
        }
    </style>
</head>

<body>

    <!-- =========================
         ENCABEZADO
    ========================= -->

    <div class="header">

        <div class="universidad">
            UNIVERSIDAD DE EL SALVADOR
        </div>

        <div class="instituto">
            INSTITUTO DE ESTUDIOS HISTÓRICOS,
            ANTROPOLÓGICOS Y ARQUEOLÓGICOS
        </div>

        <div class="siglas">
            (IEHAA)
        </div>

    </div>


    <!-- =========================
         TÍTULO
    ========================= -->

    <h1 class="titulo-reporte">
        {{ $titulo ?? 'REPORTE DE ARCHIVEROS' }}
    </h1>


    <!-- =========================
         TOTAL DE REGISTROS
    ========================= -->

    <div class="resumen">
        Total de gavetas:
        <strong>{{ count($gavetas) }}</strong>
    </div>


    <!-- =========================
         ARCHIVEROS
    ========================= -->

    @foreach ($gavetas as $gaveta)

    <div class="archivero">

        <!-- ENCABEZADO DEL ARCHIVERO -->

        <div class="archivero-header">

            <table>
                <tr>

                    <td class="correlativo">
                        {{ $loop->iteration }}
                    </td>

                    <!-- <td class="nombre-archivero">
                        Archivero {{ $archivero['id'] ?? '' }}
                    </td> -->

                </tr>
            </table>

        </div>


        <!-- DATOS DEL ARCHIVERO -->

        <table class="datos">

            <tr>

                <td>

                    <!-- <span class="label">
                        Codigo
                    </span> -->

                    <span class="value">
                        gaveta #{{ $gaveta['codigo'] ?? 'No disponible' }}
                    </span>

                </td>

            </tr>

        </table>

    </div>

    @endforeach


    <!-- =========================
         FOOTER
    ========================= -->

    <div class="footer">

        Universidad de El Salvador • IEHAA •
        Ciudad Universitaria, San Salvador, El Salvador

        &nbsp;|&nbsp;

        Generado el {{ now()->format('d/m/Y H:i') }}

    </div>

</body>

</html>
```