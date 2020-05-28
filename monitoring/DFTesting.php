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

             'cfg_ploc' => '1',
            // 'cfg_namefrqskip' => '1'
             'cfg_namefrq' => '1'
            // 'cfg_minwscorepctgmax' => '20'
            // 'cfg_query-limit' => '80'
            // 'cfg_table-limit' => '200'
            // 'cfg_explain-limit' => '400'
           // 'cfg_msa-search' => '1'
           // 'cfg_loc-search' => '40'
            //'cfg_light' => '1'
            //'cfg_heavy' => '1'
            //'cfg_requery' => '1'
            //'cfg_tablereq' => 'consus'

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

        // $csvFileName = $prodid.'_'.$cfg_mc.'_'.time().'.csv';
        $csvFileName = time().'.csv';
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

                                if(strcasecmp($test[$this->output_input_mapping[$outputField]], $test['Output' . $outputField]) == 0)
                                {
                                    $test['match'.$outputField] = 1;
                                }

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
# $main_url = "https://api2b.versium.com/q2.php?vkey=3b5ac175f8938aec76944acdc8314ec9&prodids=email,b2bemail,finapp,whois,lipeople&cfg_output=consus,fraudsigid,basic,email,validemail6,tscores,stats2,tablelist&cfg_light=1&cfg_namefrq=1&cfg_mc=INDIV;PINDIV3;PINDIV4;LF0,AHN0,CS;LF0,AHN0,Z0;LF0,AS0,CS;LF0,AS0,Z0;LF,D,S&jobid=61&k=xg2vvo5u9cl4-batch&cfg_required=%23RawMatchCodes";
# $main_url = "https://api2b.versium.com/q2.php?
# DONE vkey=3b5ac175f8938aec76944acdc8314ec9&
# DONE prodids=email,b2bemail,finapp,whois,lipeople&
# DONE cfg_output=consus,fraudsigid,basic,email,validemail6,tscores,stats2,tablelist&
#cfg_light=1& Not required?
#cfg_namefrq=
# DONEcfg_mc=INDIV;PINDIV3;PINDIV4;LF0,AHN0,CS;LF0,AHN0,Z0;LF0,AS0,CS;LF0,AS0,Z0;LF,D,S&
#jobid=61& not required ?
#k=xg2vvo5u9cl4-batch& Not required?
#cfg_required=%23RawMatchCodes";
$fileName = "truth_2019.csv";

# $fileName = "truth_2019-small.csv";
//$prodIds = "auto";



$inputParamCombinations = [
    [FIRST_NAME, LAST_NAME, ADDRESS, CITY, STATE, ZIP]
];
$outputFields = [RAW_MATCH_CODE, OUTPUT_EMAIL_ADDRESS,OUTPUT_PHONE];



$focus = 'person';

$testRunner = new TestRunner();
// $prodIds_to_test = ['auto','amacaipc','amacaicc','amacaipw','email','cell','consus','finapp','va','telcowp','wparch'/*,'voter','voter2','youngwp,'*/,'tssn'];

$prodIds_to_test = ['basic,telcowp,cell,cell2,auto,consus,amacaipc,wparch,amacaicc'];
$cfg_mcs = ['INDIV;PINDIV3;PINDIV4;LF0,AHN0,CS;LF0,AHN0,Z0;LF0,AS0,CS;LF0,AS0,Z0;LF,D,S']; // indiv
// $cfg_mcs = ['INDIV;HHLD;H0,L;O0,L;IP0,L;E0,L;P0,L;PINDIV3;PINDIV4;LF0,AHN0,CS;LF0,AHN0,Z0;LF0,AS0,CS;LF0,AS0,Z0;LF,D,S']; //hhld

$cfg_output = 'consus,fraudsigid,basic,email,validemail6,tscores,stats2,tablelist';

# $testRunner->runTests($main_url, $prodIds, $cfg_output, $inputParamCombinations, $outputFields,$fileName);
$maxresults = 1;

foreach ($prodIds_to_test as $prodId) {
    foreach($cfg_mcs as $cfg_mc) {
        $testRunner->runTests($main_url, $prodId, $cfg_mc, $cfg_output, $inputParamCombinations, $outputFields, $fileName,
            $focus,
            $maxresults);
    }
}
