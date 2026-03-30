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
                "tipoDocumento" => $dte->customer->tipo_documento,
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

    public function generarEstructura03(Dte $dte)
{
    $dte->load(['customer', 'items', 'company']);
    
    $cuerpoDocumento = $this->mapearItemsCCF($dte->items);
    
    $totalGravada = 0;
    foreach ($cuerpoDocumento as $item) {
        $totalGravada += $item['ventaGravada'];
    }

    $totalGravada = round($totalGravada, 2);
    $totalIVA = round($totalGravada * 0.13, 2);
    $totalPagar = round($totalGravada + $totalIVA, 2);

    return [
        "identificacion" => [
            "version" => 3,
            "ambiente" => "00",
            "tipoDte" => "03",
            "numeroControl" => $dte->numero_control,
            "codigoGeneracion" => $dte->codigo_generacion,
            "tipoModelo" => 1,
            "tipoOperacion" => 1,
            "fecEmi" => $dte->fecha_emision->format('Y-m-d'),
            "horEmi" => $dte->fecha_emision->format('H:i:s'),
            "tipoMoneda" => "USD",
            "tipoContingencia" => null, // REQUERIDO
            "motivoContin" => null      // REQUERIDO
        ],
        "documentoRelacionado" => null, // REQUERIDO
        "emisor" => [
            "nit" => $dte->company->nit,
            "nrc" => $dte->company->nrc,
            "nombre" => $dte->company->nombre,
            "codActividad" => $dte->company->cod_actividad,
            "descActividad" => $dte->company->desc_actividad,
            "nombreComercial" => $dte->company->nombre_comercial,
            "tipoEstablecimiento" => $dte->company->tipo_establecimiento ?? "02",
            "direccion" => [
                "departamento" => $dte->company->departamento ?? "02",
                "municipio" => $dte->company->municipio ?? "01",
                "complemento" => $dte->company->direccion_complemento ?? $dte->company->direccion ?? "Direccion de Establecimiento"
            ],
            "telefono" => $dte->company->telefono,
            "correo" => $dte->company->email,
            "codEstableMH" => null,    // REQUERIDO
            "codEstable" => null,      // REQUERIDO
            "codPuntoVentaMH" => null, // REQUERIDO
            "codPuntoVenta" => null    // REQUERIDO
        ],
        "receptor" => [
            "nit" => str_replace('-', '', $dte->customer->num_documento), // Limpiar guiones
            "nrc" => str_replace('-', '', $dte->customer->nrc), // Limpiar guiones
            "nombre" => $dte->customer->nombre,
            "nombreComercial" => $dte->customer->nombre_comercial ?? $dte->customer->nombre,
            "codActividad" => $dte->customer->cod_actividad,
            "descActividad" => $dte->customer->desc_actividad,
            "direccion" => [
                "departamento" => $dte->customer->departamento ?? "02",
                "municipio" => $dte->customer->municipio ?? "15",
                "complemento" => $dte->customer->direccion_complemento ?? $dte->customer->direccion ?? "San Salvador"
            ],
            "telefono" => $dte->customer->telefono,
            "correo" => $dte->customer->email,
        ],
        "otrosDocumentos" => null, // REQUERIDO
        "ventaTercero" => null,    // REQUERIDO
        "cuerpoDocumento" => $cuerpoDocumento,
        "resumen" => [
            "totalNoSuj" => 0.0,
            "totalExenta" => 0.0,
            "totalGravada" => $totalGravada,
            "subTotalVentas" => $totalGravada,
            "descuNoSuj" => 0.0,
            "descuExenta" => 0.0,
            "descuGravada" => 0.0,
            "porcentajeDescuento" => 0.0,
            "totalDescu" => 0.0,
            "subTotal" => $totalGravada,
            "ivaRete1" => 0.0,
            "ivaPerci1" => 0.0,
            "reteRenta" => 0.0,
            "montoTotalOperacion" => $totalPagar,
            "totalNoGravado" => 0.0,
            "totalPagar" => $totalPagar,
            "totalLetras" => $this->numeroALetras($totalPagar),
            "saldoFavor" => 0.0,       // REQUERIDO
            "condicionOperacion" => 1,
            "pagos" => [               // REQUERIDO
                [
                    "codigo" => "01",
                    "montoPago" => $totalPagar,
                    "referencia" => null,
                    "plazo" => null,
                    "periodo" => null
                ]
            ],
            "numPagoElectronico" => null, // REQUERIDO
            "tributos" => [
                [
                    "codigo" => "20",
                    "descripcion" => "IVA",
                    "valor" => $totalIVA
                ]
            ]
        ],
        "extension" => null, // REQUERIDO
        "apendice" => null   // REQUERIDO
    ];
}

        private function mapearItemsCCF($items)
        {
            return $items->map(function ($item, $key) {
                $precioConIVA = (float)$item->precio_unitario;
                $cantidad = (float)$item->cantidad;
                
                $baseSinIVA = round($precioConIVA / 1.13, 2);
                $ventaGravada = round($baseSinIVA * $cantidad, 2);

                return [
                    "numItem" => $key + 1,
                    "tipoItem" => 1,
                    "numeroDocumento" => null,
                    "cantidad" => $cantidad,
                    "codigo" => (string)$item->id,
                    "codTributo" => null,
                    "uniMedida" => 59,
                    "descripcion" => $item->descripcion,
                    "precioUni" => $baseSinIVA,
                    "montoDescu" => 0.0,
                    "ventaNoSuj" => 0.0,
                    "ventaExenta" => 0.0,
                    "ventaGravada" => $ventaGravada,
                    "tributos" => ["20"],
                    "psv" => 0.0,       // REQUERIDO
                    "noGravado" => 0.0  // REQUERIDO
                ];
            })->toArray();
        }

        public function generarEstructuraInvalidacion(Dte $dte, $motivo, $nombreResponsable = "ROBIN ANTONIO CASTILLO SAAVEDRA", $documentoResponsable = "032267824")
{
    // Limpiamos documentos de guiones
    $docResponsableClean = str_replace('-', '', $documentoResponsable);
    $docReceptorClean = str_replace('-', '', $dte->customer->nit ?? $dte->customer->num_documento);

    return [
        "identificacion" => [
            "version" => 2,
            "ambiente" => "00",
            "codigoGeneracion" => strtoupper(\Illuminate\Support\Str::uuid()->toString()), // UUID nuevo para la anulación
            "fecAnula" => now()->format('Y-m-d'),
            "horAnula" => now()->format('H:i:s'),
        ],
        "emisor" => [
            "nit" => $dte->company->nit,
            "nombre" => $dte->company->nombre,
            "tipoEstablecimiento" => "02", // O "01" según tu configuración
            "nomEstablecimiento" => $dte->company->nombre_comercial ?? "Casa Matriz",
            "codEstableMH" => null,
            "codEstable" => "0001", 
            "codPuntoVentaMH" => null,
            "codPuntoVenta" => "0001",
            "telefono" => $dte->company->telefono,
            "correo" => $dte->company->email,
        ],
        "documento" => [
            "tipoDte" => $dte->tipo_dte,
            "codigoGeneracion" => $dte->codigo_generacion, // El original del DTE a anular
            "selloRecibido" => $dte->sello_recepcion,     // El original
            "numeroControl" => $dte->numero_control,     // El original
            "fecEmi" => $dte->fecha_emision->format('Y-m-d'),
            "montoIva" => null, // El ejemplo lo manda como null
            "codigoGeneracionR" => null, // null según el ejemplo
            "tipoDocumento" => $dte->customer->tipo_documento ?? "36",
            "numDocumento" => $docReceptorClean,
            "nombre" => $dte->customer->nombre,
            "telefono" => $dte->customer->telefono ?? null,
            "correo" => $dte->customer->email ?? null,
        ],
        "motivo" => [
            "tipoAnulacion" => 2,
            "motivoAnulacion" => $motivo,
            "nombreResponsable" => $nombreResponsable,
            "tipDocResponsable" => "36", // NIT
            "numDocResponsable" => $docResponsableClean,
            "nombreSolicita" => $dte->customer->nombre,
            "tipDocSolicita" => $dte->customer->tipo_documento ?? "36",
            "numDocSolicita" => $docReceptorClean,
        ]
    ];
}

public function generarEstructuraNotaCredito(Dte $dteOriginal, $itemsModificados, $motivo)
{
    return [
        "identificacion" => [
            "version" => 3,
            "tipoDte" => "05",
            // ... correlativos nuevos ...
        ],
        "documentoRelacionado" => [
            [
                "tipoDocumento" => "03", // Siempre 03 porque afecta a un CCF
                "tipoGeneracion" => 1,  // 1 = Electrónico
                "numeroDocumento" => $dteOriginal->codigo_generacion,
                "fechaEmision" => $dteOriginal->fecha_emision->format('Y-m-d')
            ]
        ],
        "emisor" => [ /* Tus datos */ ],
        "receptor" => [ /* Datos del cliente del CCF original */ ],
        "ventaTercero" => null,
        "cuerpoDoc" => $itemsModificados, // Los productos devueltos o rebajados
        "resumen" => [
            "totalNoGravado" => 0,
            "totalGravada" => $totalRebaja,
            "subTotal" => $totalRebaja,
            "montoSujetoPercepcion" => 0,
            "ivaPerci1" => 0,
            "ivaRetel1" => 0,
            "retencionRenta" => 0,
            "montoTotalOperacion" => $totalRebaja + ($totalRebaja * 0.13),
            "totalLetras" => $this->numeroALetras($totalConIva),
            // ...
        ]
    ];
}


}