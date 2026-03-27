<?php
namespace App\Services;

use App\Models\Dte;
use Illuminate\Support\Str;

class DteService
{
    public function generarEstructura01(Dte $dte)
    {
      $dte->load(['customer', 'items', 'company']);
    
    // 1. Generamos el cuerpo primero
    $cuerpoDocumento = $this->mapearItems($dte->items);
    
    // 2. Inicializamos las variables con los nombres que usa tu bloque 'return'
    $totalGravadaCalculada = 0;
    $totalIvaCalculado = 0;
    $totalExentaCalculada = 0;

    foreach ($cuerpoDocumento as $item) {
        $totalGravadaCalculada += $item['ventaGravada'];
        $totalIvaCalculado += $item['ivaItem'];
        $totalExentaCalculada += $item['ventaExenta'];
    }

    $totalPagar = round($totalGravadaCalculada , 2);
        return [
            
            "identificacion" => [
                "version" => 1,
                "ambiente" => "00",
                "tipoDte" => "01",
                "numeroControl" => $dte->numero_control,
                "codigoGeneracion" => $dte->codigo_generacion,
                "tipoModelo" => 1,
                "tipoOperacion" => 1,
                "fecEmi" => $dte->fecha_emision->format('Y-m-d'),
                "horEmi" => $dte->fecha_emision->format('H:i:s'),
                "tipoMoneda" => "USD",
                "tipoContingencia" => null,
                "motivoContin" => null
            ],
            "documentoRelacionado" => null,
            "emisor" => [
                "nit" => $dte->company->nit,
                "nrc" => $dte->company->nrc,
                "nombre" => $dte->company->nombre,
                "codActividad" => "96092",
                "descActividad" => "Servicios n.c.p.",
                "nombreComercial" => $dte->company->nombre_comercial,
                "tipoEstablecimiento" => "02",
                "direccion" => [
                    "departamento" => "02",
                    "municipio" => "01",
                    "complemento" => $dte->company->direccion ?: "Direccion de Establecimiento" 
                ],
                "telefono" => $dte->company->telefono,
                "correo" => $dte->company->email,
                "codEstableMH" => null,
                "codEstable" => null,
                "codPuntoVentaMH" => null,
                "codPuntoVenta" => null
            ],
            "receptor" => [
                "tipoDocumento" => $dte->customer->tipo_documento == '36' ? '36' : '37',
                "numDocumento" => $dte->customer->num_documento,
                "nrc" => null,
                "nombre" => $dte->customer->nombre,
                "codActividad" => "41001",
                "descActividad" => "Otros",
                "direccion" => [
                    "departamento" => "02",
                    "municipio" => "15",
                    "complemento" => $dte->customer->direccion ?: "San Salvador"
                ],
                "telefono" => $dte->customer->telefono,
                "correo" => $dte->customer->email,
            ],
            "otrosDocumentos" => null,
            "ventaTercero" => null,
            "cuerpoDocumento" => $cuerpoDocumento,
            "resumen" => [
                "totalNoSuj" => 0.0,
                "totalExenta" => round($totalExentaCalculada, 2),
                "totalGravada" => round($totalGravadaCalculada, 2),
                "subTotalVentas" => round($totalGravadaCalculada + $totalExentaCalculada, 2),
                "descuNoSuj" => 0.0,
                "descuExenta" => 0.0,
                "descuGravada" => 0.0,
                "porcentajeDescuento" => 0.0,
                "totalDescu" => 0.0,
                "subTotal" => round($totalGravadaCalculada + $totalExentaCalculada, 2),
                "ivaRete1" => 0.0,
                "reteRenta" => 0.0,
                "montoTotalOperacion" => $totalPagar,
                "totalNoGravado" => 0.0,
                "totalPagar" => $totalPagar,
                "totalLetras" => $this->numeroALetras($totalPagar),
                "totalIva" => round($totalIvaCalculado, 2),
                "saldoFavor" => 0.0,
                "condicionOperacion" => 1,
                "pagos" => [
                    [
                        "codigo" => "01",
                        "montoPago" => $totalPagar,
                        "referencia" => null,
                        "plazo" => null,
                        "periodo" => null
                    ]
                ],
                "numPagoElectronico" => null,
                "tributos" => null // CAMBIO: Hacienda 01 no requiere el desglose en este array si ya va en totalIva
            ],
            "extension" => null,
            "apendice" => null
        ];
    }

  private function mapearItems($items)
{
    return $items->map(function ($item, $key) {
        $precioConIva = round((float)$item->precio_unitario, 2);
        $cantidad = (float)$item->cantidad;
        
        // Venta Gravada = Precio con IVA * Cantidad (Tal cual lo que paga el cliente)
        $ventaGravada = round($precioConIva * $cantidad, 2);
        
        // IVA Item = El cálculo inverso que Hacienda espera (Base Imponible * 0.13)
        // Se calcula: (Total / 1.13) * 0.13
        $ivaItem = round(($ventaGravada / 1.13) * 0.13, 2);
        
        // Precio Uni = El precio que se muestra (Con IVA para Consumidor Final)
        $precioUni = $precioConIva; 

        return [
            "numItem" => $key + 1,
            "tipoItem" => 1,
            "numeroDocumento" => null,
            "cantidad" => $cantidad,
            "codigo" => null,
            "codTributo" => null,
            "uniMedida" => 59,
            "descripcion" => $item->descripcion,
            "precioUni" => (float)$precioUni, 
            "montoDescu" => 0,
            "ventaNoSuj" => 0,
            "ventaExenta" => 0,
            "ventaGravada" => (float)$ventaGravada, // <--- Con IVA incluido
            "tributos" => null, 
            "psv" => 0,
            "ivaItem" => (float)$ivaItem, // <--- El cálculo que "espera" el validador
            "noGravado" => 0
        ];
    })->toArray();
}
    private function numeroALetras($numero)
     {
        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE', 'VEINTE'];
        $decenas = ['', '', 'VEINTI', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINCIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($numero == 0) return 'CERO DOLARES CON 00/100';

        $entero = floor($numero);
        $centavos = round(($numero - $entero) * 100);

        $fmt = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        $letras = strtoupper($fmt->format($entero));

        // Ajustes de concordancia típicos de facturación en ES
        $letras = str_replace('UNO', 'UN', $letras);
        
        // Formato final exigido por MH: TEXTO + DOLARES + CENTAVOS/100
        return $letras . ' DOLARES CON ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100';
    }

}