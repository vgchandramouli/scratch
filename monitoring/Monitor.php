<?php
/**
 * Created by PhpStorm.
 * User: gchandramouli
 * Date: 12/12/18
 * Time: 12:53 PM
 */

define("AVG_TIME_PER_RECORD","average_time_per_record_in_milliseconds");

define("BUSINESS_INFO","/businessinfo");

define("END_TIME","execution_end_time");
define("ERROR","error");
define("ERRORS",'errors');

define("FAILED_COUNT","failed_count");
define("FAILED_RECORDS","failed_records");
define("KEY",'key');

define("NO_RESPONSE_COUNT","no_response_count");
define("NO_RESPONSE_RECORDS","no_response_records");

define("PASSED_COUNT","passed_count");
define("PASSED_RECORDS","passed_records");
define("RESULT","result");

define("START_TIME","execution_start_time");

define("TEST_RECORDS",'test_records');
define("TICKER",'ticker');
define("TIME_TAKEN_IN_MILLI_SECONDS","time_taken_in_milliseconds");
define("TOTAL_RECORDS_CHECKED",'number_of_records');



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

        $response =[];

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

        if($result === false)
        {
            $response[ERROR] = "CURL Error: " . curl_error($curl);

        }

        /*
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            $response[ERROR] = "HTTP Error: " . $responseCode;
        }
        */

        curl_close($curl);


        //print_r($response[ERROR]);
        $response[RESULT]=json_decode($result, true, 10);
        //print_r($result);

        return $response;
    }


    protected function runTest(&$url, $params)
    {
        return $this->CallAPI("GET", $url, $params);

    }

    protected function getParams($record)
    {
        $params = [
            K => '96408630-a6c8-4530-9060-fd8fe576979c',
            //KEY => '96408630-a6c8-4530-9060-fd8fe576979',
            TICKER => $record['Ticker']
        ];

        return $params;
    }


    public function runTests($url, $api,$inputFileName,&$report)
    {
        $data = $this->csv_to_array($inputFileName);
        $report[TOTAL_RECORDS_CHECKED] = count($data);



        foreach ($data as $record) {
            $params = $this->getParams($record);

            //print_r($params);

            $test = [];


            $test[START_TIME] = microtime(true);

            $tempurl = $url.$api;
            $response = $this->runTest($tempurl, $params);


            $test[END_TIME] = microtime(true);

            $test[TIME_TAKEN_IN_MILLI_SECONDS] = round(($test[END_TIME] - $test[START_TIME]),3)*1000;
            $report[AVG_TIME_PER_RECORD] = $report[AVG_TIME_PER_RECORD] + $test[TIME_TAKEN_IN_MILLI_SECONDS];
            $test['url'] = urldecode($tempurl);

            echo "Fetching data for : ";
            print_r($test['url']);
            echo "\n";

            if(empty($response[ERROR])) {
                //we got a response back from server

                $test['result'] = $response[RESULT];

                //print_r($response);
                //print_r($result);
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
                array_push($report[TEST_RECORDS][NO_RESPONSE_RECORDS], $test);
            }



            //break;

        }



        $report[PASSED_COUNT] = count($report[TEST_RECORDS][PASSED_RECORDS]);
        $report[FAILED_COUNT] = count($report[TEST_RECORDS][FAILED_RECORDS]);
        $report[NO_RESPONSE_COUNT] = count($report[TEST_RECORDS][NO_RESPONSE_RECORDS]);

        $report[AVG_TIME_PER_RECORD]  = round($report[AVG_TIME_PER_RECORD] / count($data),3);

    }
}

$report = [];
$report[START_TIME] = microtime(true);

$report[FAILED_COUNT] = 0;
$report[PASSED_COUNT] = 0;
$report[NO_RESPONSE_COUNT] = 0;

$report[AVG_TIME_PER_RECORD] = 0;
$report[TEST_RECORDS] = [PASSED_RECORDS => [],FAILED_RECORDS=> [],NO_RESPONSE_RECORDS=>[]];


//$main_url = "https://api.versium.com/v1.0";
$main_url = "https://api.versium.com/v1.0";
$api = BUSINESS_INFO;

$fileName = "ticker_truth_set.csv";

$testRunner = new TestRunner();

$report['api'] = substr($api,1);

$testRunner->runTests($main_url , $api, $fileName,$report);

$report[END_TIME] = microtime(true);

array_multisort($report,SORT_ASC);
$json_output = json_encode($report,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

//print_r($json_output);



//write json to file
if (file_put_contents("output.json", $json_output))
    echo "JSON file created successfully...\n";
else
    echo "Oops! Error creating json file...\n";
