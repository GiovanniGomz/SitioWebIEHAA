<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte IEHAA - UES</title>

    ```
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
       INFORMACIÓN DEL REPORTE
    ========================= */

        .resumen {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 12px;
        }

        /* =========================
       FICHA DEL INVESTIGADOR
    ========================= */

        .investigador {
            border: 1px solid #c9cfd8;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        /* Encabezado de cada investigador */

        .investigador-header {
            background-color: #003087;
            color: white;
            padding: 9px 12px;
        }

        .investigador-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .investigador-header td {
            vertical-align: middle;
        }

        .correlativo {
            width: 45px;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.5);
            padding-right: 10px;
        }

        .nombre-investigador {
            padding-left: 12px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* =========================
       TABLA DE DATOS
    ========================= */

        .datos {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .datos td {
            width: 50%;
            padding: 10px 13px;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
        }

        .datos td:first-child {
            border-right: 1px solid #e5e7eb;
        }

        .datos tr:last-child td {
            border-bottom: none;
        }

        .campo {
            margin: 0;
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
            word-wrap: break-word;
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
    ```

</head>

<body>

    ```
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
        {{ $titulo ?? 'REPORTE DE INVESTIGADORES' }}
    </h1>


    <!-- =========================
     TOTAL DE REGISTROS
========================= -->

    <div class="resumen">
        Total de investigadores:
        <strong>{{ count($investigadores) }}</strong>
    </div>


    <!-- =========================
     INVESTIGADORES
========================= -->

    @foreach ($investigadores as $investigador)

    <div class="investigador">

        <!-- ENCABEZADO DEL INVESTIGADOR -->

        <div class="investigador-header">

            <table>
                <tr>

                    <td class="correlativo">
                        {{ $loop->iteration }}
                    </td>

                    <td class="nombre-investigador">
                        {{ trim(
                            ($investigador['nombre'] ?? '') .
                            ' ' .
                            ($investigador['apellido'] ?? '')
                        ) }}
                    </td>

                </tr>
            </table>

        </div>


        <!-- DATOS DEL INVESTIGADOR -->

        <table class="datos">

            <!-- FILA 1 -->

            <tr>

                <td>
                    <div class="campo">

                        <span class="label">
                            Carnet
                        </span>

                        <span class="value">
                            {{ $investigador['carnet'] ?? 'No disponible' }}
                        </span>

                    </div>
                </td>


                <td>
                    <div class="campo">

                        <span class="label">
                            Facultad
                        </span>

                        <span class="value">
                            {{ $investigador['facultad']['nombre'] ?? 'No disponible' }}
                        </span>

                    </div>
                </td>

            </tr>


            <!-- FILA 2 -->

            <tr>

                <td>
                    <div class="campo">

                        <span class="label">
                            Tipo de investigador
                        </span>

                        <span class="value">
                            {{ $investigador['tipo_investigador']['nombre'] ?? 'No disponible' }}
                        </span>

                    </div>
                </td>


                <td>
                    <div class="campo">

                        <span class="label">
                            Categoría de investigador
                        </span>

                        <span class="value">
                            {{ $investigador['categoria_investigador']['nombre'] ?? 'No disponible' }}
                        </span>

                    </div>
                </td>

            </tr>


            <!-- FILA 3 -->

            <tr>

                <td>
                    <div class="campo">

                        <span class="label">
                            Correo electrónico
                        </span>

                        <span class="value">
                            {{ $investigador['correo'] ?? 'No disponible' }}
                        </span>

                    </div>
                </td>


                <td>
                    <div class="campo">

                        <span class="label">
                            Teléfono
                        </span>

                        <span class="value">
                            {{ $investigador['telefono'] ?? 'No disponible' }}
                        </span>

                    </div>
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
    ```

</body>

</html>