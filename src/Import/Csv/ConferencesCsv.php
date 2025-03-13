<?php

namespace App\Import\Csv;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Import\Csv\Validation\ConferencesCsvValidation;
use App\Service\ConferenceService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Pusher\Pusher;
use Symfony\Component\Form\FormFactoryInterface;

// TODO: add Abstract import class so you can add import of other files, not just csv files

class ConferencesCsv
{
    private FormFactoryInterface $form;
    private ConferenceService $conferenceService;
    private EntityManagerInterface $em;
    private Pusher $pusher;
    private ConferencesCsvValidation $csvValidation;

    public function __construct(
        FormFactoryInterface     $form,
        ConferenceService        $conferenceService,
        EntityManagerInterface   $em,
        Pusher                   $pusher,
        ConferencesCsvValidation $csvValidation
    )
    {
        $this->form = $form;
        $this->conferenceService = $conferenceService;
        $this->em = $em;
        $this->pusher = $pusher;
        $this->csvValidation = $csvValidation;
    }

    public function import(array $csvData)
    {
        $validationResult = $this->csvValidation->validate($csvData);

        if (!empty($validationResult)) {
            $this->sendErrorPushMessage($validationResult);

            return;
        }

        $this->em->getConnection()->beginTransaction();

        // TODO: check if this approach is good\right
        foreach ($csvData as $data) {
            $conference = new Conference();
            $form = $this->form->create(ConferenceType::class, $conference);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->conferenceService->saveFormChanges($conference,
                    [
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                    ]
                );
            } else {
                $this->em->getConnection()->rollBack();
                $this->importDone = false;

                return;
                // TODO: change on notification with errors or add errors in a DB for future representing
                /*throw new Exception(
                    $this->conferenceService->getFormErrors($form),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );*/
            }
        }

        $this->em->commit();

        $this->sendSuccessPushMessage();
    }

    public function getCsvData(string $filepath): array
    {
        $csvReader = new Csv();
        $spreadsheet = $csvReader->load($filepath);
        $data = [];

        foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Включаємо пусті клітинки
            $rowData = [];

            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();

                if (is_string($value)) {
                    $value = trim($value);
                }

                $rowData[] = $value;
            }

            $data[] = $rowData;
        }

        return $this->formatCsvData($data);
    }

    private function formatCsvData(array $csvData): array
    {
        $titles = $csvData[0];
        $newData = [];
        $iterator = 0;


        foreach ($csvData as $row) {
            $iterator += 1;

            if ($iterator === 1) {
                continue;
            }

            $rowData = [];

            foreach ($row as $key => $item) {
                $rowData[$titles[$key]] = $item;
            }

            $newData[] = $rowData;
        }

        return $newData;
    }

    private function sendSuccessPushMessage(string $message = "New conferences imported successfully")
    {
        $this->pusher->trigger(
            'notification',
            'success-import',
            $message
        );
    }

    private function sendErrorPushMessage(string $errorMessage)
    {
        $this->pusher->trigger(
            'notification',
            'error-import',
            $errorMessage
        );
    }
}
