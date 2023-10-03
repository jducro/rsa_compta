<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;
use App\Domain\Line;

final class SGImportService
{
  public function __construct(
    private EntityManager $em
  ) {

  }

  public function import ($handle): void
  {
    $this->em->getConnection()->beginTransaction();

    $lineNumber = 1;

    $fileLine = fgets($handle);
    while ($fileLine !== false) {
      if ($lineNumber++ <= 7) {
        if ($lineNumber === 3) {
          $data = str_getcsv($fileLine, ";");
          if ($data[0] !== "FR76 3000 3031 2200 0501 3271 922") {
            throw new \Exception("Not a SG CSV file");
          }
        }
        $fileLine = fgets($handle);
        continue;
      }
      $data = str_getcsv($fileLine, ";");
      $this->createLine($data, $handle, $fileLine);
    }

    $this->em->flush();
    $this->em->getConnection()->commit();
  }

  public function createLine(array $data, $handle, &$fileLine): Line
  {
    $line = new Line();
    $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[5] . "12:00:00", new \DateTimeZone('Europe/Paris')));
    $line->setAmount($this->toFloat($data[2] == "" ? $data[3] : $data[2]));
    $line->setLabel($data[6]);
    
    $description = $data[1]."\n";

    $fileLine = fgets($handle);
    $data = str_getcsv($fileLine, ";");
    while ($data[0] == '' && !empty($data[1])) {
      $description .= $data[1]."\n";
      $fileLine = fgets($handle);
      $data = str_getcsv($fileLine, ";");
    }

    $line->setDescription($description);

    $this->em->persist($line);
    
    return $line;
  }

  private function toFloat(string $value): float
  {
    return floatval(strtr(str_replace(' EUR', '', $value), [',' => '.', ' ' => '', ' ' => '']));
  }
}