<?php
require_once '../model/helpers.php';

if (isset($_GET['operation'])) {
    switch ($_GET['operation']) {
        case 'creaCronograma':

            $fechaRecibida = $_GET['fechaRecibida'];
            $fechaInicio = new DateTime($fechaRecibida);

            $monto = floatval($_GET['monto']);
            $tasa = floatval($_GET['tasa'])/ 100;
            $numeroCuotas = intval($_GET['numeroCuotas']);

            $cuota = round(Pago($tasa, $numeroCuotas, $monto), 2);

            echo "
            <tr>
              <td>0</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td>{$monto}</td>
            </tr>";

            $saldoCapital = $monto;
            $sumatoriaInteres = 0;

            for ($i = 1; $i <= $numeroCuotas; $i++) {
                $interesPeriodo = $saldoCapital * $tasa;
                $abonoCapital = $cuota - $interesPeriodo;
                $saldoCapitalTemp = $saldoCapital - $abonoCapital;

                if ($i == $numeroCuotas) {
                    $saldoCapitalTemp = 0.00; // Para evitar redondeos
                }

                $sumatoriaInteres += $interesPeriodo;

                $interesPeriodoPrint = number_format($interesPeriodo, 2, '.', ',');
                $abonoCapitalPrint = number_format($abonoCapital, 2, ".", ',');
                $cuotaPrint = number_format($cuota, 2, ".", ",");
                $saldoCapitalTempPrint = number_format($saldoCapitalTemp, 2, ".", ",");

                echo "
                <tr>
                  <td>{$i}</td>
                  <td>{$fechaInicio->format('d-m-Y')}</td>
                  <td>{$interesPeriodoPrint}</td>
                  <td>{$abonoCapitalPrint}</td>
                  <td>{$cuotaPrint}</td>
                  <td>{$saldoCapitalTempPrint}</td>
                </tr>";

                $fechaInicio->modify('+1 month');
                $saldoCapital = $saldoCapitalTemp;
            }

            $sumatoriaInteresPrint = number_format($sumatoriaInteres, 2, ".", ",");

            echo "
            <tr>
              <td></td>
              <td></td>
              <td><strong>{$sumatoriaInteresPrint}</strong></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>";

            break;
    }
}
