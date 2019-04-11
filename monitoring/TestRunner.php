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

define("KEY", 'vkey');
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
define("TICKER", "d_ticker");
define("DOMAIN", "d_domain");
define("TITLE", "d_title");


define("RAW_MATCH_CODE", "#RawMatchCodes");
define("EMAIL_ADDRESS", "EmailAddr");

define("OUTPUT_BUSINESS_NAME", "BusName");
define("OUTPUT_ADDRESS", "Address");
define("OUTPUT_CITY", "City");
define("OUTPUT_STATE", "State");
define("OUTPUT_ZIP", "Zip");
define("OUTPUT_TIME_STAMP", "TimeStamp");
define("OUTPUT_COUNTRY","Country");
define("OUTPUT_PHONE","Phone");
define("OUTPUT_DOMAIN","Domain");
define("OUTPUT_EMAIL_ADDRESSS","EmailAddr");
define("OUTPUT_FIRST_NAME","FirstName");
define("OUTPUT_LAST_NAME","LastName");
define("OUTPUT_TITLE","Title");
define("OUTPUT_FETCH_TIME","FetchTime");



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

    public function __construct()
    {
        // some code here
        $this->input_output_mapping = [BUSINESS_NAME => OUTPUT_BUSINESS_NAME, ADDRESS => OUTPUT_ADDRESS];
        $this->output_input_mapping = [
            OUTPUT_BUSINESS_NAME => BUSINESS_NAME,
            OUTPUT_ADDRESS => ADDRESS,
            OUTPUT_CITY => CITY,
            OUTPUT_STATE => STATE,
            OUTPUT_ZIP => ZIP,
            OUTPUT_PHONE => PHONE,
            OUTPUT_DOMAIN => DOMAIN,
            OUTPUT_EMAIL_ADDRESS => EMAIL,
            OUTPUT_FIRST_NAME => FIRST_NAME,
            OUTPUT_LAST_NAME => LAST_NAME,
            OUTPUT_TITLE => TITLE
        ];
    }

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


    public function getEmptyTestRecord($outputFields, $record)
    {
        $test['url'] = ' ';
        $test['num-results'] = ' ';
        foreach ($outputFields as $outputField) {
            if (isset($this->output_input_mapping[$outputField])) {
                $test[$this->output_input_mapping[$outputField]] = ' ';
                $test['match'.$outputField] = 0;
                if ($record != null) {
                    if (isset($record[$this->output_input_mapping[$outputField]])) {
                        $test[$this->output_input_mapping[$outputField]] = $record[$this->output_input_mapping[$outputField]];
                    }
                }
            }
            $test['Output' . $outputField] = ' ';
        }
        return $test;
    }

    public function runTests(
        $url,
        $prodid,
        $cfg_output,
        $inputParamCombinations,
        $outputFields,
        $inputFileName,
        $focus,
        $maxresults
    ) {
        $data = $this->csv_to_array($inputFileName);

        $csvFileName = 'output'.time().'.csv';
        $fp = fopen($csvFileName, 'w');

        $test = $this->getEmptyTestRecord($outputFields, null);


        fputcsv($fp, array_keys($test));

        foreach ($data as $record) {

            foreach ($inputParamCombinations as $inputParamCombination) {

                //print_r($record);
                $tempURL = $url;

                $params = $this->getParams($record, $inputParamCombination);
                $params[PRODID] = $prodid;
                $params[CFG_OUTPUT] = $cfg_output;
                $params[CFG_FOCUS] = $focus;
                $params[CFG_MAX_RECORDS] = $maxresults;

                print_r($params);
                $test = $this->getEmptyTestRecord($outputFields, $record);
                $response = $this->runTest($tempURL, $params);

                //print_r($response);

                $test['url'] = '=HYPERLINK("' . urldecode($tempURL) . '")';
                $test['num-results'] = $response[RESULT]['Versium']['num-results'];


                if ($response[RESULT]['Versium']['is-first-rec-changed'] == 1 || $response[RESULT]['Versium']['first-rec-has-new-attributes'] == 1) {
                    $num_results = $response[RESULT]['Versium']['num-results'];

                    for ($i = 0; $i < $num_results; $i++) {
                        foreach ($outputFields as $outputField) {
                            print_r($response[RESULT]['Versium']['results'][$i]);
                            if (isset($response[RESULT]['Versium']['results'][$i][$outputField])) {
                                //$test[$outputField.strval($i+1)] = $response[RESULT]['Versium']['results'][$i][$outputField];
                                $test['Output' . $outputField] = $response[RESULT]['Versium']['results'][$i][$outputField];
                            } else {
                                //$test[$outputField.strval($i+1)] ='';
                                $test['Output' . $outputField] = ' ';
                            }
                        }
                    }
                }
                print_r($test);
                fputcsv($fp, $test);

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
        print_r("The report generated in : ".$csvFileName);
    }


}


$main_url = "https://api2b.versium.com/q2.php";
$fileName = "online-audience-b2c.csv";
$prodIds = "busdircb";
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
$outputFields = [RAW_MATCH_CODE, EMAIL_ADDRESS];

$cfg_output = OUTPUT_STATS2 . ',' . $prodIds;
//$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields,$fileName);


$fileName = "TSbptoptechfinal18.csv";
//$prodIds = "busdircb";
$prodIds = "busdiryp2";
$inputParamCombinations = [
    [FIRST_NAME, LAST_NAME, ADDRESS, ZIP, BUSINESS_NAME]
    //,
    // [FIRST_NAME, LAST_NAME, DOMAIN]

];



/*
$fileName = "truth_set.csv";
$prodIds = "busdircb";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP
];
// $focus = 'person';
$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',' . $prodIds;
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/


/*
$fileName = "truth_set.csv";
$prodIds = "busdiryp2";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP
];
// $focus = 'person';
$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',busdirdetails'.',busdetails2';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/

/*
$fileName = "truth_set.csv";
$prodIds = "licorp";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP
];
// $focus = 'person';
$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',busdirdetails'.',busdetails2';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/

/*
$fileName = "truth_set.csv";
$prodIds = "b2bpeople1";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP
];
$focus = 'person';
//$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',vstat,lipeople-full,lipexec,email,b2bemail';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/

/*
$fileName = "truth_set.csv";
$prodIds = "lipeople";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_FETCH_TIME
];
$focus = 'person';
//$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',vstat,lipeople-full,lipexec,email,b2bemail';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/

/*
$fileName = "truth_set.csv";
$prodIds = "b2bpeople2";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_FETCH_TIME
];
$focus = 'person';
//$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',vstat,lipeople-full,lipexec,email,b2bemail';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/

/*
$fileName = "truth_set.csv";
$prodIds = "b2bpeople3";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_FETCH_TIME
];
$focus = 'person';
//$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',vstat,lipeople-full,lipexec,email,b2bemail';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/

/*
$fileName = "truth_set.csv";
$prodIds = "kv";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP
];
//$focus = 'person';
$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',kv';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/

/*
$fileName = "truth_set.csv";
$prodIds = "b2bporg";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP
];
$focus = 'person';
//$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',b2bporg';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/

/*
$fileName = "truth_set.csv";
$prodIds = "b2bporg,b2bpeople1,b2bpeople2,b2bpeople3,lipeople";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP
];
$focus = 'person';
//$focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',vstat,lipeople-full,lipexec,email,b2bemail';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/


$fileName = "truth_set.csv";
$prodIds = "busdircb";
$outputFields = [
    RAW_MATCH_CODE,
    OUTPUT_BUSINESS_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_DOMAIN,
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP
];
$focus = 'person';
// $focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',vstat,lipeople-full,lipexec,email,b2bemail';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
