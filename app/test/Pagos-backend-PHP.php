<?php

require_once "../models/Cotizacion.php";
$cotizacion = new Cotizacion();

function Pago($tasaInteres, $numPagos, $montoPrestamo)
{
  // Verificar si la tasa de interés es 0
  if ($tasaInteres == 0) {
    return $montoPrestamo / $numPagos;
  }

  // Calcular el pago utilizando la fórmula de anualidad
  $pago = ($montoPrestamo * $tasaInteres) / (1 - pow(1 + $tasaInteres, -$numPagos));
  return $pago;
}

//Crearemos un generador de tabla de pagos de acuerdo al formato de cronograma de YONDA
//Valores que tienen que ser enviados desde el FRONT END

if (isset($_POST['operation'])) {

  //Genera todo el cuadro de cotización en la vista: 
  //views/cotizaciones/registrar-cotizacion
  if ($_POST['operation'] == 'construirCronograma') {
    $fechaRecibida = $_POST['fechaInicio'];
    $importeTotal = floatval($_POST['importeTotal']);
    $cuotaInicial = floatval($_POST['cuotaInicial']);
    $nroCuota = floatval($_POST['nroCuota']);
    $tasa = floatval($_POST['tasa'])/100;

    $tasaMensual = pow((1 + $tasa), (1 / 12)) - 1;
    //$tasaMensual = 0.0426;
    $montoFinanciar = $importeTotal - $cuotaInicial;

    //Cuota mensual
    $cuota = round(Pago($tasaMensual, $nroCuota, $montoFinanciar), 2);

    //Algorimot Ver. 1
    $fechaInicio = new DateTime($fechaRecibida);

    //Fila 0
    //Se imprime la fecha de inicio
    echo "
      <tr>
        <td>0</td>
        <td>{$fechaInicio->format('d-m-Y')}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{$montoFinanciar}</td>
      </tr>
    ";

    $saldoCapital = $montoFinanciar;

    //Variables para los totales en el pie de la tabla
    $sumatoriaInteres = 0;
    $sumatoriaAbonoCapital = 0;
    $sumatoriaCuota = 0;

    //Fila 1 hasta el número de cuotas enviado
    for ($i = 1; $i <= $nroCuota; $i++) {
      $fechaInicio->modify('+1 month');
      $nuevaFecha = $fechaInicio->format('d-m-Y');

      $totalInteres = $tasaMensual * $saldoCapital;
      $abonoCapital = $cuota - $totalInteres;
      $saldoCapital -= $abonoCapital;

      $baseImponible = $totalInteres / 1.18;
      $igv = $baseImponible * 0.18;

      if ($i == $nroCuota) {
        $abonoCapital += $saldoCapital;
        $saldoCapital = 0;
      }

      //Sumatorias
      $sumatoriaInteres += $totalInteres;
      $sumatoriaAbonoCapital += $abonoCapital;
      $sumatoriaCuota += $cuota;

      $igvPrint = number_format($igv, 2, '.', ',');
      $baseImponiblePrint = number_format($baseImponible, 2, '.', ',');
      $totalInteresPrint = number_format($totalInteres, 2, '.', ',');
      $abonoCapitalPrint = number_format($abonoCapital, 2, '.', ',');
      $cuotaPrint = number_format($cuota, 2, '.', ',');
      $saldoCapitalPrint = number_format($saldoCapital, 2, '.', ',');

      echo "
      <tr>
        <td>$i</td>
        <td>{$nuevaFecha}</td>
        <td>{$baseImponiblePrint}</td>
        <td>{$igvPrint}</td>
        <td>{$totalInteresPrint}</td>
        <td>{$abonoCapitalPrint}</td>
        <td>{$cuotaPrint}</td=>
        <td>{$saldoCapitalPrint}</td>
      </tr>
      ";
    } //for

    $sumatoriaInteresPrint = number_format($sumatoriaInteres, 2, '.', ',');
    $sumatoriaAbonoCapitalPrint = number_format($sumatoriaAbonoCapital, 2, '.', ',');
    $sumatoriaCuotaPrint = number_format($sumatoriaCuota, 2, '.', ',');

    echo "
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong>{$sumatoriaInteresPrint}</strong></td>
        <td><strong>{$sumatoriaAbonoCapitalPrint}</strong></td>
        <td><strong>{$sumatoriaCuotaPrint}</strong></td>
        <td></td>
      </tr>
    ";
  } //construirCronograma

}
