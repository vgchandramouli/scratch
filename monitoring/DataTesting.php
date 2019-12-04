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
define("AUTO_PRODID", "auto");


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
define("CFG_MC", "cfg_mc");
define("CFG_PLOC","cfg_ploc");
define("CFG_NAMEFRQ","cfg_namefrq");
define("CFG_NEGMC","cfg_negmc");
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
define("OUTPUT_EMAIL_ADDRESS","EmailAddr");
define("OUTPUT_FIRST_NAME","FirstName");
define("OUTPUT_LAST_NAME","LastName");
define("OUTPUT_TITLE","Title");
define("OUTPUT_FETCH_TIME","FetchTime");
define("OUTPUT_CORP_NAME","CorpName");
define("OUTPUT_CORP_DOMAIN","CorpDomain");
define("OUTPUT_LI_PROFILE","LIProfileURL");
define("OUTPUT_SOURCE","Source");


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
            OUTPUT_TITLE => TITLE,
            OUTPUT_CORP_NAME => BUSINESS_NAME,
            OUTPUT_CORP_DOMAIN => DOMAIN
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
            KEY => '3b5ac175f8938aec76944acdc8314ec9',
            //,
             'cfg_ploc' => '1',
             'cfg_namefrq' => '1'
            // CFG_MC => "HHLD"
            // CFG_MC => "P0"
            // CFG_MC => "E0"
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
        $cfg_mc,
        $cfg_output,
        $inputParamCombinations,
        $outputFields,
        $inputFileName,
        $focus,
        $maxresults
    ) {
        $data = $this->csv_to_array($inputFileName);

        $csvFileName = $prodid.'_'.$cfg_mc.'_'.time().'.csv';
        $fp = fopen($csvFileName, 'w');

        $test = $this->getEmptyTestRecord($outputFields, null);


        fputcsv($fp, array_keys($test));

        foreach ($data as $record) {

            foreach ($inputParamCombinations as $inputParamCombination) {

                //print_r($record);
                $tempURL = $url;

                $params = $this->getParams($record, $inputParamCombination);
                $params[PRODID] = $prodid;
                $params[CFG_MC] = $cfg_mc;
                $params[CFG_OUTPUT] = $cfg_output;
                // $params[CFG_FOCUS] = $focus;
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



            }
            //break;
        }
        fclose($fp);
        print_r("The report generated in : ".$csvFileName);
    }


}


$main_url = "https://api2b.versium.com/q2.php";
$fileName = "truth_2019.csv";
//$prodIds = "auto";

$inputParamCombinations = [
    [FIRST_NAME, LAST_NAME, ADDRESS, CITY, STATE, ZIP]
    //,
    // [FIRST_NAME, LAST_NAME, DOMAIN]

];
$outputFields = [RAW_MATCH_CODE, OUTPUT_EMAIL_ADDRESS,OUTPUT_PHONE];

/*
$inputParamCombinations = [
    [PHONE]
    //,
    // [FIRST_NAME, LAST_NAME, DOMAIN]

];
$outputFields = [RAW_MATCH_CODE, OUTPUT_FIRST_NAME,OUTPUT_LAST_NAME,OUTPUT_ADDRESS,OUTPUT_CITY,OUTPUT_STATE,OUTPUT_ZIP];


$inputParamCombinations = [
    [EMAIL]
    //,
    // [FIRST_NAME, LAST_NAME, DOMAIN]

];
$outputFields = [RAW_MATCH_CODE, OUTPUT_FIRST_NAME,OUTPUT_LAST_NAME,OUTPUT_ADDRESS,OUTPUT_CITY,OUTPUT_STATE,OUTPUT_ZIP];
*/
$cfg_output = OUTPUT_STATS2 . ',' . 'basic,email';
$focus = 'person';

$testRunner = new TestRunner();
$prodIds_to_test = ['auto','amacaipc','amacaicc','amacaipw','email','cell','consus','finapp','va','telcowp','wparch'/*,'voter','voter2','youngwp,'*/,'tssn'];
$cfg_mcs = ['PINDIV'];

# $testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields,$fileName);
$maxresults = 1;

foreach ($prodIds_to_test as $prodId) {
    foreach($cfg_mcs as $cfg_mc) {
        $testRunner->runTests($main_url, $prodId, $cfg_mc, $cfg_output, $inputParamCombinations, $outputFields, $fileName,
            $focus,
            $maxresults);
    }
}

/*
$fileName = "oracle.csv";
$prodIds = B2B_EMAIL_PRODID;
$inputParamCombinations = [
    [FIRST_NAME, LAST_NAME, TICKER],
    [FIRST_NAME, LAST_NAME, DOMAIN]

];
$outputFields = [RAW_MATCH_CODE, EMAIL_ADDRESS];

$cfg_output = OUTPUT_STATS2 . ',' . $prodIds;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields,$fileName);



// $main_url = "https://api2b.versium.com/q2.php";
$main_url = "https://api2b-stg.versium.com/q2.php";
$fileName = "fec.csv";
// $fileName = "fe-small.csv";

$inputParamCombinations = [
    [FIRST_NAME, LAST_NAME, CITY, STATE, ZIP, BUSINESS_NAME, TITLE]
    //,
    // [FIRST_NAME, LAST_NAME, DOMAIN]

];


$prodIds = "kv";
$outputFields = [
    OUTPUT_FIRST_NAME,
    OUTPUT_LAST_NAME,
    OUTPUT_TITLE,
    RAW_MATCH_CODE,
    OUTPUT_CORP_NAME,
    OUTPUT_ADDRESS,
    OUTPUT_CITY,
    OUTPUT_STATE,
    OUTPUT_COUNTRY,
    OUTPUT_ZIP,
    OUTPUT_CORP_DOMAIN,
    OUTPUT_EMAIL_ADDRESSS,
    OUTPUT_PHONE,
    OUTPUT_TIME_STAMP,
    OUTPUT_LI_PROFILE,
    OUTPUT_SOURCE
];
$focus = 'person';
// $focus = 'business';
$cfg_output = OUTPUT_STATS2 . ',kv';
$maxresults = 1;
$testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields, $fileName, $focus,
    $maxresults);
*/