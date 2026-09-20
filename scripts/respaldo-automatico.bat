@echo off
REM Respaldo automatico de la base de datos del Sitio Web IEHAA.
REM Lo ejecuta el Programador de tareas de Windows (ver documentacion del modulo Respaldos).
REM Solo respalda si ya toca segun la frecuencia configurada en el panel (Respaldos).
REM Para forzar un respaldo inmediato: agregar --forzar al final de la linea de artisan.

cd /d "%~dp0.."

if exist "C:\php\php.exe" (
    "C:\php\php.exe" artisan iehaa:respaldar --auto
) else (
    php artisan iehaa:respaldar --auto
)
