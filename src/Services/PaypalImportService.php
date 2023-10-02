<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;
use App\Domain\Line;
use App\Domain\LineBreakdown;

final class PaypalImportService
{
  public function __construct(
    private EntityManager $em
  ) {

  }

  public function import ($handle): void
  {
    $this->em->getConnection()->beginTransaction();

    $firstLine = true;

    while (($line = fgets($handle)) !== false) {
      if ($firstLine) {
        $firstLine = false;
        continue;
      }
      $line = str_getcsv($line, ",");
      $this->em->persist($this->createLine($line));
      $fees = $this->createFeesLine($line);
      if ($fees) {
        $this->em->persist($fees);
      }
    }

    $this->em->flush();
    $this->em->getConnection()->commit();
  }

  public function createLine(array $data): Line
  {
    $line = new Line();
    $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0] . ' ' . $data[1], new \DateTimeZone($data[2] ?? 'Europe/Paris')));
    $line->setName($data[3]);
    $line->setType("PAYPAL");
    $line->setAmount($this->toFloat($data[7]));
    $line->setLabel($data[15]);
    if ($line->getAmount() >= 100) {
      // Supérieur à 100€, c'est un renouvellement d'avion
      $line->setBreakdown([LineBreakdown::PlaneRenewal]);
    } else if ($line->getAmount() > 0) {
      // Inférieur à 100€, c'est une contribution RSA
      $line->setBreakdown([LineBreakdown::RSAContribution]);
    } else {
      // Montant négatif, c'est un transfert vers la SG
      $line->setBreakdown([LineBreakdown::InternalTransfer]);
    }
    $line->setDescription($data[26]);
    
    return $line;
  }

  public function createFeesLine(array $data): Line | null
  {
    $line = new Line();
    $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0] . ' ' . $data[1], new \DateTimeZone($data[2] ?? 'Europe/Paris')));
    $line->setType("PAYPAL");
    $line->setName($data[3]);
    $line->setAmount($this->toFloat($data[8]));
    $line->setBreakdown([LineBreakdown::PaypalFees]);
    if ($line->getAmount() == 0) {
      return null;
    }
    return $line;
  }

  private function toFloat(string $value): float
  {
    return floatval(strtr($value, [',' => '.', ' ' => '', ' ' => '']));
  }
}