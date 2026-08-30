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
            margin: 25px 0 20px 0;
        }

        /* Caja de información */
        .info-box {
            border: 1px solid #003087;
            border-radius: 6px;
            padding: 10px 25px;
            margin: 20px 0;
            background-color: #f8f9fa;
        }

        /* Tabla para las dos columnas */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 8px 12px;
        }

        .info-item {
            margin-bottom: 5px;
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

        /* Footer */
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

    <!-- ENCABEZADO -->
    <div class="header">

        <div class="instituto">
            INSTITUTO DE ESTUDIOS HISTÓRICOS, ANTROPOLÓGICOS Y ARQUEOLÓGICOS
            <br>
            (IEHAA)
        </div>

    </div>


    <!-- TÍTULO -->
    <h1 class="titulo-reporte">
        {{ $titulo ?? 'TÍTULO DEL REPORTE' }}
    </h1>


    <!-- INFORMACIÓN DE LOS INVESTIGADORES -->
    @foreach ($investigadores as $investigador)

    <div class="info-box">

        <table class="info-table">

            <!-- FILA 1 -->
            <tr>

                <td>
                    <div class="info-item">
                        <div class="label">#</div>

                        <div class="value">
                            {{ $loop->iteration }}
                        </div>
                    </div>
                </td>

                <td>
                    <div class="info-item">
                        <div class="label">
                            Nombre completo
                        </div>

                        <div class="value">
                            {{ trim(
                                    ($investigador['nombre'] ?? '') .
                                    ' ' .
                                    ($investigador['apellido'] ?? '')
                                ) }}
                        </div>
                    </div>
                </td>

            </tr>


            <!-- FILA 2 -->
            <tr>

                <td>
                    <div class="info-item">

                        <div class="label">
                            Carnet
                        </div>

                        <div class="value">
                            {{ $investigador['carnet'] ?? 'No disponible' }}
                        </div>

                    </div>
                </td>

                <td>
                    <div class="info-item">

                        <div class="label">
                            Facultad
                        </div>

                        <div class="value">
                            {{ $investigador['facultad'] ?? 'No disponible' }}
                        </div>

                    </div>
                </td>

            </tr>
            <tr>

                <td>
                    <div class="info-item">

                        <div class="label">
                            Grado académico
                        </div>

                        <div class="value">
                            {{ $investigador['grado'] ?? 'No disponible' }}
                        </div>

                    </div>
                </td>

                <td>
                    <!-- Espacio vacío para mantener las dos columnas -->
                </td>

            </tr>

        </table>

    </div>

    @endforeach


    <!-- FOOTER -->
    <div class="footer">

        Universidad de El Salvador • IEHAA •
        Ciudad Universitaria, San Salvador, El Salvador
        -
        {{ now()->format('d \d\e F \d\e Y \a \l\a\s H:i') }}

    </div>

</body>

</html>