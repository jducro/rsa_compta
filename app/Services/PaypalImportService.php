<?php

namespace App\Services;

use App\Models\Line;
use App\Models\LineBreakdown;

final class PaypalImportService
{
    public function import($handle): void
    {
        \DB::beginTransaction();

        $fileLine = 1;

        while (($rawLine = fgets($handle)) !== false) {
            if ($fileLine++ === 1) {
                $line = str_getcsv($rawLine, ",");
                if (count($line) !== 41) {
                    throw new \Exception("Not a Paypal CSV file");
                }
                continue;
            }
            $data = str_getcsv($rawLine, ",");

            $mainHash = $this->computeHash($rawLine, 'main');
            if (!Line::where('import_hash', $mainHash)->exists()) {
                $main = $this->createLine($data);
                $main->import_hash = $mainHash;
                $main->save();
            }

            $feesHash = $this->computeHash($rawLine, 'fees');
            if (!Line::where('import_hash', $feesHash)->exists()) {
                $fees = $this->createFeesLine($data);
                if ($fees) {
                    $fees->import_hash = $feesHash;
                    $fees->save();
                }
            }
        }

        \DB::commit();
    }

    public function createLine(array $data): Line
    {
        $line = new Line();
        $timezone = new \DateTimeZone($data[2] ?? 'Europe/Paris');
        $line->date = \DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0] . ' ' . $data[1], $timezone)
            ?: throw new \RuntimeException('Invalid date: ' . $data[0]);
        $line->name = $data[3];
        $line->type = "PAYPAL";
        $line->amount = $this->toFloat($data[7]);
        $line->label = $data[15];
        if ($line->amount >= 120) {
            // Supérieur à 120€, c'est un renouvellement d'avion
            $line->breakdown = [LineBreakdown::PLANE_RENEWAL];
            $line->breakdown_plane_renewal = 120;
            $line->breakdown_customer_fees = $line->amount - 120;
            if ($line->breakdown_customer_fees > 0) {
                $line->breakdown = array_merge($line->breakdown ?? [], [LineBreakdown::CUSTOMER_FEES]);
            }
        } elseif ($line->amount > 0) {
            // Inférieur à 120€, c'est une contribution RSA
            $line->breakdown = [LineBreakdown::RSA_NAV_CONTRIBUTION];
            $line->breakdown_rsa_nav_contribution = $line->amount;
        } else {
            // Montant négatif, c'est un transfert vers la SG
            $line->breakdown = [LineBreakdown::INTERNAL_TRANSFER];
            $line->breakdown_internal_transfer = $line->amount;
        }
        $line->description = $data[26];

        return $line;
    }

    public function createFeesLine(array $data): Line | null
    {
        $line = new Line();
        $timezone = new \DateTimeZone($data[2] ?? 'Europe/Paris');
        $line->date = \DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0] . ' ' . $data[1], $timezone)
            ?: throw new \RuntimeException('Invalid date: ' . $data[0]);
        $line->type = "PAYPAL";
        $line->name = $data[3];
        $line->amount = $this->toFloat($data[8]);
        $line->breakdown = [LineBreakdown::PAYPAL_FEES];
        $line->breakdown_paypal_fees = $line->amount;
        if ($line->amount == 0) {
            return null;
        }
        return $line;
    }

    /**
     * Compute a SHA-256 hash over the raw CSV line and a row-type suffix so
     * that the main line and the fees line produced from the same source row
     * each get a unique, stable identifier.
     */
    private function computeHash(string $rawLine, string $suffix): string
    {
        return hash('sha256', $rawLine . $suffix);
    }

    private function toFloat(string $value): float
    {
        return floatval(strtr($value, [',' => '.', ' ' => '', "\xc2\xa0" => '']));
    }
}
