<?php
/**
 * Created by PhpStorm.
 * User: gchandramouli
 * Date: 12/12/18
 * Time: 12:53 PM
 */

define("ERROR", "error");
define("RESULT", "result");


define("PRODID", "prodids");


define("B2B_EMAIL_PRODID", "b2bemail");
define("EMAIL_PRODID", "email");
define("CFG_MAX_RECORDS", "cfg_maxrecs");

define("K", 'k');
define("KV_KEY",'u89y7t6rfyty-vis-kv-p');
define("CFG_OUTPUT", "cfg_output");
define("OUTPUT_STATS2", "stats2");
define("CFG_FOCUS", "cfg_focus");
define("FIRST_NAME", "d_first");
define("LAST_NAME", "d_last");
define("PHONE", "d_phone");
define("CITY", "d_city");
define("STATE", "d_state");
define("ZIP", "d_zip");
define("BUSINESS_NAME", "d_busname");
define("EMAIL", "d_email");
define("ADDRESS", "d_fulladdr");
/* define("TICKER", "d_ticker"); */
define("DOMAIN", "d_domain");
define("TITLE", "d_title");


// define("RAW_MATCH_CODE", "#RawMatchCodes");
// define("EMAIL_ADDRESS", "EmailAddr");

// define("OUTPUT_BUSINESS_NAME", "BusName");
define("OUTPUT_ADDRESS_1", "address1");
define("OUTPUT_ADDRESS_2", "address2");
define("OUTPUT_CITY", "city");
define("OUTPUT_STATE", "state");
define("OUTPUT_ZIP", "zip");
//define("OUTPUT_TIME_STAMP", "TimeStamp");
define("OUTPUT_COUNTRY","country");
define("OUTPUT_PHONE","phone");
define("OUTPUT_DOMAIN","website");
define("OUTPUT_FULL_TIME_EMPLOYEES","fullTimeEmployees");

define("CMD","cmd");
define("READ_ALL","readall");
define("TICKER","ticker");
define("DATA","data");


class TestRunner
{
    protected $url;
    protected $result;
    protected $startTime;
    protected $endTime;
    protected $output;
    protected $passed;

    public $input_output_mapping;
    public $output_input_mapping;


    protected function csv_to_array($filename = '', $delimiter = ',')
    {
        //if(!file_exists($filename) || !is_readable($filename))
        //   return FALSE;

        $header = null;
        $data = [];//array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = array_map('trim', $row);
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    function CallAPI($method, &$url, $data = false)
    {

        $response = [];

        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        //print_r($url);


        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        if ($result === false) {
            $response[ERROR] = "CURL Error: " . curl_error($curl);

        }


        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            $response[ERROR] = "HTTP Error: " . $responseCode;
        }


        curl_close($curl);

        //print_r($response[ERROR]);
        $response[RESULT] = json_decode($result, true, 10);
        //print_r($result);

        return $response;
    }


    protected function runTest(&$url, $params)
    {
        return $this->CallAPI("GET", $url, $params);

    }

    protected function getParams($record, $inputParamCombination)
    {
        $params = [
            CMD => READ_ALL
        ];

        foreach ($inputParamCombination as $param) {
            $params[$param] = $record[$param];
        }

        return $params;
    }


    public function getEmptyTestRecord($outputFields)
    {
        $test['url'] = ' ';
        // $test['num-results'] = ' ';
        foreach ($outputFields as $outputField) {
            $test[$outputField] = ' ';
        }
        return $test;
    }

    //
    public function runTests(
        $url,
        $inputParamCombinations,
        $outputFields,
        $inputFileName
    ) {
        $data = $this->csv_to_array($inputFileName);

        $csvFileName = 'output' . time() . '.csv';
        $fp = fopen($csvFileName, 'w');

        $test = $this->getEmptyTestRecord($outputFields);

        fputcsv($fp, array_keys($test));
        foreach ($data as $record) {
            foreach ($inputParamCombinations as $inputParamCombination) {
                //print_r($record);
                $tempURL = $url;
                $params = $this->getParams($record, $inputParamCombination);
                print_r($params);
                $test = $this->getEmptyTestRecord($outputFields, $record);
                $response = $this->runTest($tempURL, $params);

                 print_r($response);

                $test['url'] = '=HYPERLINK("' . urldecode($tempURL) . '")';


                if (!isset($response[ERROR])){
                    $output = $response[RESULT];

                    /* the last entry is the profile */
                    $index = count($output) - 1;
                    /*
                    foreach($output as $item)
                    {
                        if($item["type"] == "profile")
                        {
                            print_r($item);
                            break;

                        }
                    }
                    */


                    print_r($output[$index]);
                    foreach ($outputFields as $outputField) {
                        if (isset($output[$index][DATA][$outputField])) {
                            print_r($output[$index][DATA][$outputField]);
                            $test[$outputField] = $output[$index][DATA][$outputField];
                        } else {
                            $test[$outputField] = '';
                        }

                    }


                }
                print_r($test);
                fputcsv($fp, $test);

            }
            // break;
        }

        fclose($fp);
        print_r("The report generated in : " . $csvFileName);
    }
}

$main_url = "https://crawl.versium.com/finance.php";
$inputParamCombinations = [
    [TICKER]
   ];

// $fileName = "sp500.csv";
// $fileName = "kv_public.csv";
// $fileName = "List-NASDAQ.csv";
$fileName = "all.csv";
$outputFields = [
    OUTPUT_DOMAIN,
    OUTPUT_ADDRESS_1,
    OUTPUT_ADDRESS_2,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_ZIP,
    OUTPUT_COUNTRY,
    OUTPUT_FULL_TIME_EMPLOYEES
];


$testRunner = new TestRunner();

$testRunner->runTests($main_url, $inputParamCombinations, $outputFields, $fileName);