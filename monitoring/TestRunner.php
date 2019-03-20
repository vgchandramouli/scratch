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


define("KEY", 'vkey');
define("CFG_OUTPUT", "cfg_output");
define("OUTPUT_STATS2", "stats2");

define("FIRST_NAME","d_first");
define("LAST_NAME", "d_last");
define("PHONE", "d_phone");
define("CITY", "d_city");
define("STATE", "d_state");
define("ZIP", "d_zip");
define("BUSINESS_NAME", "d_busname");
define("EMAIL", "d_email");
define("ADDRESS", "d_fulladdr");
define("TICKER", "d_ticker");
define("DOMAIN", "d_domain");


class TestRunner
{
    protected $url;
    protected $result;
    protected $startTime;
    protected $endTime;
    protected $output;
    protected $passed;


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
            KEY => '3b5ac175f8938aec76944acdc8314ec9'
        ];

        foreach ($inputParamCombination as $param) {
            $params[$param] = $record[$param];
        }

        return $params;
    }


    public function runTests($url, $prodid, $cfg_output, $inputParamCombinations, $inputFileName)
    {
        $data = $this->csv_to_array($inputFileName);

        $csvFileName = 'example.csv';
        $fp = fopen($csvFileName, 'w');

        $test['url'] = '';
        $test['num-results'] = '';
        fputcsv($fp, array_keys($test));

        foreach ($data as $record) {
            foreach ($inputParamCombinations as $inputParamCombination) {
                print_r($record);
                $tempURL = $url;

                $params = $this->getParams($record, $inputParamCombination);
                $params[PRODID] = $prodid;
                $params[CFG_OUTPUT] = $cfg_output;

                print_r($params);
                $response = $this->runTest($tempURL, $params);

                //print_r($response);

                $test['url'] = urldecode($tempURL);
                $test['num-results'] = $response[RESULT]['Versium']['num-results'];
                //$test['result'] = $response[RESULT];

                print_r($test);
                fputcsv($fp, $test);


                $num_results = $response[RESULT]['Versium']['num-results'];

                //print_r($num_results);


                //break;
                /*
                            $tempurl = $url;
                            $response = $this->runTest($tempurl, $params);

                            $test['url'] = urldecode($tempurl);

                            echo "Fetching data for : ";
                            print_r($test['url']);
                            echo "\n";

                            if(empty($response[ERROR])) {
                                //we got a response back from server
                                $test['result'] = $response[RESULT];

                                print_r($response);
                                print_r($result);
                                $num_results = $response[RESULT]['versium']['num_results'];

                                //print_r($num_results);
                                if ($num_results == 1) {
                                    if (strtoupper($record['BusinessName']) === $response[RESULT]['versium']['results'][0]['business']) {

                                        array_push($report[TEST_RECORDS][PASSED_RECORDS], $test);
                                    } else {

                                        $test['FailureReason'] = "Data Mismatch";
                                        array_push($report[TEST_RECORDS][FAILED_RECORDS], $test);
                                    }
                                } elseif ($num_results == 0) {

                                    if (array_key_exists(ERRORS, $response[RESULT]['versium'])) {
                                        $test['FailureReason'] = "Error";

                                    } else {
                                        $test['FailureReason'] = "No Data";
                                    }
                                    array_push($report[TEST_RECORDS][FAILED_RECORDS], $test);

                                } else {

                                    $test['FailureReason'] = "Multiple Records";
                                    array_push($report[TEST_RECORDS][FAILED_RECORDS], $test);

                                }
                            }
                            else
                            {
                                //curl error has occurred
                                $test['FailureReason'] = $response[ERROR];
                                //array_push($report[TEST_RECORDS][NO_RESPONSE_RECORDS], $test);
                            }
                            //break;
                */

            }
            //break;
        }
        fclose($fp);
    }

}


$main_url = "https://api2b.versium.com/q2.php";
$fileName = "online-audience-b2c.csv";
$prodIds = EMAIL_PRODID;
$inputParamCombinations = [
    [FIRST_NAME, LAST_NAME],
    [FIRST_NAME, LAST_NAME, PHONE],
    [FIRST_NAME, LAST_NAME, ADDRESS]
];
$cfg_output = OUTPUT_STATS2 . ',' . $prodIds;

$testRunner = new TestRunner();
# $testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $fileName);


$fileName = "oracle.csv";
$prodIds = B2B_EMAIL_PRODID;
$inputParamCombinations = [
    [FIRST_NAME, LAST_NAME, TICKER],
    [FIRST_NAME, LAST_NAME, DOMAIN]

];
$cfg_output = OUTPUT_STATS2 . ',' . $prodIds;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $fileName);