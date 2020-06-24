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
        // Make collection for valid person
        $validPersons = collect($persons['persons']);
        $validPersons = $validPersons->flatten(1)->filter(function($value){
            return $value['first_name'] != null || $value['last_name'] != null;
        })->values();

        return $this->successResponse(
            [
                'persons' => $validPersons
            ]
        );
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
            $modifyPersons = $this->getPerson($person);
            foreach ($modifyPersons as $key => $value) {
                if ($key == 'person') {
                    $data['persons'][] = $value;
                }
            }
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

//          Regular expression to exact match:  (?:^|\W)mrs(?:$|\W)
            $pattern = '/(?:^|\W)' . $title . '(?:$|\W)/';
            preg_match($pattern, $person, $matches); // expensive we can use strpos($person, $title) !== false

            // Check is current title is exists in person
            if (count($matches) > 0) {
                $totalTitle[] = $title;
                // remove title from string
                $person = preg_replace($pattern, ' ', $person);
            }
        }

        // Replace special character &, and
        $names = $this->getName($person);

        // make array remaining string
        foreach ($totalTitle as $title) {
            foreach ($names as $key => $value) {
                $totalPerson[] = [
                    'title' => $title,
                    'first_name' => $value['first_name'],
                    'last_name' => $value['last_name'],
                    'initial' => null
                ];
            }
        }

        return [
            'person' => $totalPerson
        ];
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    private function cleanString($string)
    {
        $patterns[1] = '/&/';
        $patterns[2] = '/\./';
        $string = preg_replace($patterns, '', $string);
        return $string;
    }


    private function getName($personString)
    {
        // check string has and, if yes then multiple

        $pattern = '/(?:^|\W)and(?:$|\W)/';
        preg_match($pattern, $personString, $matches);
        $totalPerson = [];

        if (count($matches) > 0) {
            // multiple person with and
            $personString = preg_replace($pattern, '_% ', $personString);
            $persons = explode('_%', $personString);
            foreach ($persons as $person) {
                $totalPerson[] = $this->getPersonArray($person);
            }

        } else {
            // Single person
            $totalPerson[] = $this->getPersonArray($personString);
        }

        return $totalPerson;
    }

    private function getPersonArray($personString)
    {
        // Clear any special character
        $personString = $this->cleanString($personString);
        $persons = explode(' ', $personString);
        // Remove any empty string
        $persons = array_values(array_filter($persons));
        $count = count($persons);
        // Set first name & last name
        $firstName = null;
        $lastName = null;
        if ($count >= 2) {
            $firstName = ucfirst($persons[0]);
            $lastName = ucfirst($persons[1]);
        } else {
            $lastName = isset($persons[0]) ? ucfirst($persons[0]) : null;
            $valid = false;
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }

}
