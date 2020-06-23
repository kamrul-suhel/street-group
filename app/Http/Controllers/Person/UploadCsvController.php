<?php

namespace App\Http\Controllers\Person;

use App\Http\Controllers\Controller;
use App\Http\Requests\UplaodCsvRequest;
use App\Person;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;

class UploadCsvController extends Controller
{
    use ApiResponder;

    /**
     * @param UplaodCsvRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCsv(UplaodCsvRequest $request)
    {
        $file = $request->file('file');
        $csvToArray = $this->parseCsv($file);
        $persons = $this->getAllPerson($csvToArray);

        // Make collection
        $persons = collect($persons);
        $flatten = $persons->flatten(1);
        $persons = $flatten->all();

        return $this->successResponse(['persons' => $persons]);
    }


    /**
     * Parse csv & make as in array
     * @param $file
     * @return array
     */
    private function parseCsv($file)
    {
        $row = 1;
        $totalPerson = [];
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                // Escape first column, reason it is title
                if ($row == 1) {
                    $row++;
                    continue;
                }
                $row++;
                for ($c = 0; $c < $num; $c++) {
                    $totalPerson[] = $data[$c];
                }
            }
            fclose($handle);
        }

        // Remove empty string
        $totalPerson = array_filter($totalPerson);

        return $totalPerson;
    }

    /**
     * @param $persons
     * @return array
     */
    private function getAllPerson($persons)
    {
        $data = [];
        foreach ($persons as $person) {
            $data[] = $this->getPerson($person);
        }

        return $data;
    }

    /**
     * @param $person
     * @return array
     */
    private function getPerson($person)
    {
        // convert into lowercase
        $person = strtolower($person);

        $titles = Person::TITLE;
        $totalTitle = [];
        $totalPerson = [];

        foreach ($titles as $title) {
            // Convert title into lowercase
            $title = strtolower($title);

            // Check is current title is exists in person
            if (strpos($person, $title) !== false) {
                $totalTitle[] = $title;
                // remove title from string
                $person = preg_replace('/' . $title . '/', '', $person);
            }
        }

        // Replace special character &, and
        $person = $this->cleanString($person);

        // make array remaining string
        $person = explode(' ', $person);
        // Remove any empty string
        $person = array_values(array_filter($person));
        foreach ($totalTitle as $title) {
            $totalPerson[] = [
                'title' => ucfirst($title), // make first letter uppercase
                'first_name' => isset($person[0]) ? ucfirst($person[0]) : null,
                'last_name' => isset($person[1]) ? ucfirst($person[1]) : null,
                'initial' => null
            ];
        }

        return $totalPerson;
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    private function cleanString($string)
    {
        $patterns[0] = '/and/';
        $patterns[1] = '/&/';
        $string = preg_replace($patterns, '', $string);
        return $string;
    }

}
