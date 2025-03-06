<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class Export
{
    private Environment $template;

    public function __construct(Environment $template)
    {
        $this->template = $template;
    }

    // TODO: not sure that is good to return response in service, maybe need to change logic
    public function exportExcel(array $data, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $iteration = 0;

        foreach ($data as $title => $value) {
            if ($iteration === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            $sheet->setTitle($title);
            $sheet->fromArray($value);

            $iteration += 1;
        }

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $filename = $filename . '.xlsx';

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=$filename");
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    // TODO: not sure that is good to return response in service, maybe need to change logic
    public function exportPdf(array $data, string $template, string $filename, array $options = null): Response
    {
        $pdfOptions = new Options();

        $font = $options['font'] ?? 'Arial';
        $data['font'] = $font;

        $pdfOptions->set('defaultFont', $font);

        $dompdf = new Dompdf($pdfOptions);

        $html = $this->template->render($template, [
            'data' => $data,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=$filename",
            ]
        );
    }
}
