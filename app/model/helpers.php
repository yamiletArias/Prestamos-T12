<?php
require_once '../../database/database.php';
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

?>