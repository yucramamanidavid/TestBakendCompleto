<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta Electrónica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 40px;
        }

        .logo {
            display: block;
            margin: 0 auto 20px;
            height: 70px;
        }

        .header-box {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-box h1 {
            font-size: 20px;
            margin: 5px 0;
            color: #1d4ed8;
        }

        .info-line {
            font-size: 14px;
            margin: 3px 0;
        }

        .section {
            margin-top: 25px;
        }

        .section-title {
            background-color: #e0e7ff;
            padding: 6px 10px;
            font-weight: bold;
            border-left: 4px solid #4338ca;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
        }

        .info-table td {
            padding: 4px 0;
        }

        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.detail th, table.detail td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        table.detail th {
            background-color: #f3f4f6;
            text-align: left;
        }

        .total-box {
            margin-top: 15px;
            width: 100%;
            border-collapse: collapse;
        }

        .total-box td {
            padding: 8px;
        }

        .total-label {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            font-size: 12px;
            text-align: center;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">

    <div class="header-box">
        <h1>Boleta Electrónica</h1>
        <p class="info-line"><strong>Serie:</strong> {{ $boleta->serie }} - <strong>Número:</strong> {{ $boleta->numero }}</p>
        <p class="info-line"><strong>Fecha de emisión:</strong> {{ $fecha_emision }}</p>
    </div>

    <div class="section">
        <div class="section-title">Datos del Emisor</div>
        <table class="info-table">
            <tr>
                <td><strong>Razón Social:</strong></td>
                <td>{{ $boleta->emprendedor->business_name ?? 'No disponible' }}</td>
            </tr>
            <tr>
                <td><strong>RUC:</strong></td>
                <td>{{ $boleta->emprendedor->ruc ?? '---' }}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td>{{ $boleta->emprendedor->district ?? '---' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Datos del Cliente</div>
        <table class="info-table">
            <tr>
                <td><strong>Nombre:</strong></td>
                <td>{{ $boleta->cliente->name ?? 'No disponible' }}</td>
            </tr>
            <tr>
                <td><strong>DNI:</strong></td>
                <td>{{ $boleta->cliente->document_id ?? '---' }}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td>{{ $boleta->cliente->address ?? '---' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalle de la Compra</div>
        <table class="detail">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $detalle }}</td>
                    <td>{{ $reserva->quantity }}</td>
                    <td>S/ {{ number_format($precio_unitario, 2) }}</td>
                    <td>S/ {{ number_format($boleta->monto_total, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="total-box">
        <tr>
            <td class="total-label" colspan="3">Total a pagar:</td>
            <td><strong>S/ {{ number_format($boleta->monto_total, 2) }}</strong></td>
        </tr>
    </table>

    <div class="footer">
        Gracias por su compra. Esta boleta es válida sin firma ni sello según la Resolución SUNAT N° 007-99/SUNAT.
    </div>

</body>
</html>
