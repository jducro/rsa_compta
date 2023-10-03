<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;
use App\Domain\Line;
use App\Domain\LineBreakdown;

final class SogecomImportService
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
      $line = mb_convert_encoding($line, 'ISO-8859-1', 'UTF-8');
      $data = str_getcsv($line, ";");
      $this->em->persist($this->createLine($data));
      $fees = $this->createFeesLine($data);
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
    $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0], new \DateTimeZone('Europe/Paris')));
    $line->setName($data[3]);
    $line->setType("Sogecom");
    $line->setAmount($this->toFloat($data[1]));
    if ($line->getAmount() >= 100) {
      // Supérieur à 100€, c'est un renouvellement CDN
      $line->setBreakdown([LineBreakdown::PlaneRenewal]);
      $line->breakdownPlaneRenewal = 100;
      $line->breakdownCustomerFees = $line->getAmount() - 100;
      if ($line->breakdownCustomerFees > 0) {
        $line->addBreakdown(LineBreakdown::CustomerFees);
      }
      $line->setLabel('Renouvellement CDN');
    } else if ($line->getAmount() > 0) {
      // Inférieur à 100€, c'est une contribution RSA
      $line->setBreakdown([LineBreakdown::RSAContribution]);
      $line->breakdownRSAContribution = $line->getAmount();
      $line->setLabel('COTISATION RSA NAV '.$line->getDate()->format('Y'));
    } else {
      // Montant négatif, c'est un transfert vers la SG
      $line->setBreakdown([LineBreakdown::InternalTransfer]);
      $line->breakdownInternalTransfer = $line->getAmount();
      $line->setLabel('Virement vers la SG');
    }
    $line->setDescription($data[4]."\n".$data[5]);
    
    return $line;
  }

  public function createFeesLine(array $data): Line | null
  {
    $line = new Line();
    $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0], new \DateTimeZone('Europe/Paris')));
    $line->setType("Sogecom");
    $line->setName($data[3]);
    $line->setAmount($this->toFloat($data[6]) * -1);
    $line->setBreakdown([LineBreakdown::SogecomFees]);
    $line->breakdownSogecomFees = $line->getAmount();
    if ($line->getAmount() == 0) {
      return null;
    }
    return $line;
  }

  private function toFloat(string $value): float
  {
    return floatval(strtr(str_replace(' EUR', '', $value), [',' => '.', ' ' => '', ' ' => '']));
  }
}