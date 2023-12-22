<?php

namespace App\Services;

use App\Domain\Line;
use App\Domain\LineBreakdown;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ExcelExportService
{
    public function __construct(
        protected DbLineRepository $lineRepository,
    ) {
    }

    protected Spreadsheet $spreadsheet;
    protected Worksheet $activeWorksheet;

    protected int $currentLine = 2;
    protected int $from = 0;
    protected int $step = 50;

    public function export()
    {
        $this->spreadsheet = IOFactory::load('template GRAND LIVRE.xlsx');

        $this->activeWorksheet = $this->spreadsheet->getActiveSheet();

        $this->insertLines();

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('export.xlsx');
    }

    protected function loadLines(): array
    {
        $breakdowns = [LineBreakdown::PAYPAL_FEES, LineBreakdown::SOGECOM_FEES, LineBreakdown::INTERNAL_TRANSFER];
        $qb = $this->lineRepository->getQueryBuilder()
          ->where('l.breakdown IS NULL OR l.breakdown NOT IN (:breakdown)')
          ->setParameter('breakdown', $breakdowns)
          ->orderBy('l.date', 'ASC')
          ->setFirstResult($this->from)
          ->setMaxResults($this->step);

        return $qb->getQuery()->getResult();
    }

    protected function insertLines()
    {
        while ($lines = $this->loadLines()) {
            foreach ($lines as $line) {
                $this->insertLine($line);
                $this->currentLine++;
            }
            $this->from += $this->step;
        }
    }

    protected function insertLine(Line $line)
    {
        $this->activeWorksheet->setCellValue('A' . $this->currentLine, $line->getType());
        $this->activeWorksheet->setCellValue('B' . $this->currentLine, $line->getDate()->format('d/m/Y'));
        $this->activeWorksheet->setCellValue('C' . $this->currentLine, $line->getName());
        $this->activeWorksheet->setCellValue('D' . $this->currentLine, $line->getLabel());
        $this->activeWorksheet->setCellValue('E' . $this->currentLine, self::formatCurrency($line->getDebit()));
        $this->activeWorksheet->setCellValue('F' . $this->currentLine, self::formatCurrency($line->getCredit()));
        $this->activeWorksheet
            ->setCellValue('G' . $this->currentLine, self::formatCurrency($line->breakdownPlaneRenewal));
        $this->activeWorksheet
            ->setCellValue('H' . $this->currentLine, self::formatCurrency($line->breakdownCustomerFees));
        $this->activeWorksheet
            ->setCellValue('I' . $this->currentLine, self::formatCurrency($line->breakdownRSANavContribution));
        $this->activeWorksheet
            ->setCellValue('J' . $this->currentLine, self::formatCurrency($line->breakdownRSAContribution));
        $this->activeWorksheet
            ->setCellValue('K' . $this->currentLine, self::formatCurrency($line->breakdownFollowUpNav));
        $this->activeWorksheet
            ->setCellValue('L' . $this->currentLine, self::formatCurrency($line->breakdownPenRefund));
        $this->activeWorksheet
            ->setCellValue('M' . $this->currentLine, self::formatCurrency($line->breakdownMeeting));
        $this->activeWorksheet
            ->setCellValue('N' . $this->currentLine, self::formatCurrency($line->breakdownPaypalFees));
        $this->activeWorksheet
            ->setCellValue('O' . $this->currentLine, self::formatCurrency($line->breakdownSogecomFees));
        $this->activeWorksheet
            ->setCellValue('P' . $this->currentLine, self::formatCurrency($line->breakdownOsac));
        $this->activeWorksheet
            ->setCellValue('Q' . $this->currentLine, self::formatCurrency($line->breakdownOther));
        $this->activeWorksheet
            ->setCellValue('R' . $this->currentLine, self::formatCurrency($line->breakdownDonation));
        $this->activeWorksheet
            ->setCellValue('S' . $this->currentLine, self::formatCurrency($line->breakdownVibrationDebit));
        $this->activeWorksheet
            ->setCellValue('T' . $this->currentLine, self::formatCurrency($line->breakdownVibrationCredit));
    }

    protected static function formatCurrency($value)
    {
        if (empty($value)) {
            return '';
        }
        return number_format($value, 2, ',', ' ') . ' €';
    }
}
