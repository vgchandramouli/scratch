<?php

namespace Helper;

# use Codeception\Lib\Interfaces\RequiresPackage;

class TestConfig extends \Codeception\Module # implements  RequiresPackage
{
	  protected $config = [
        'ckey' => '',
        'bkey' => ''
    ];

    protected $expected_version = '1.0';

    public function getConsumerKey(){
        return $this->config['ckey'];
    }

    public function getBusinessKey(){
        return $this->config['bkey'];
    }

    public function callAPIAndValidateJSON($I,$url,$params=null)
    {
        $I->sendGet($url,$params);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'versium' => [
                'version' => $this->expected_version
            ]
        ]);

        $json = json_decode($I->grabResponse(),true,10);

        $I->assertGreaterThanOrEqual(0,$json['versium']['num_results'],"Number of results is negative");

        if(!empty($params['max_recs']))
            $I->assertLessThanOrEqual($params['max_recs'],$json['versium']['num_results'],"Number of results is greater than max records");

        /* Adding comment to see if this is seen in html output */
        $I->comment($I->grabResponse());

        $json = json_decode($I->grabResponse(),true,10);

        // If the number of results is greater than zero, verify if the match code is present in the records
        $num_results = $json['versium']['num_results'];
        if($num_results > 0)
        {
            $results = $json['versium']['results'];
            $I->assertEquals($num_results,count($results),"Number of results is same as the records in results");

            foreach($results as $result) {

                if(!empty($params['campaign']))
                {
                    $I->assertNotEmpty($result['first'],'First name is  not empty');
                    $I->assertNotEmpty($result['last'],'Last name is  not empty');
                    switch($params['campaign']) {

                        case 'CELLPHONE':
                           $I->assertNotEmpty($result['phone'],'Phone field is not empty');
                           $I->assertEquals($result['line_type'],'MOBILE','Phone type is mobile');
                           break;
                        case 'EMAIL':
                            $I->assertNotEmpty($result['email'],'E-mail field is not empty');
                            $I->assertNotEmpty($result['ip'],'IP field is not empty');
                            $I->assertNotEmpty($result['url_source'],'URL source is not empty');
                            break;
                         case 'LANDLINE':
                             $I->assertNotEmpty($result['phone'],'Phone field is not empty');
                             $I->assertEquals($result['line_type'],'LANDLINE','Phone type is landline');
                             break;
                        case 'MAIL':
                            $I->assertNotEmpty($result['address'],'address field is not empty');
                            $I->assertNotEmpty($result['city'],'address field is not empty');
                            $I->assertNotEmpty($result['state'],'address field is not empty');
                            $I->assertNotEmpty($result['zip'],'address field is not empty');
                            $I->assertNotEmpty($result['country'],'address field is not empty');
                            break;
                        case 'ONLINE':
                            break;
                        case 'PHONE':
                            $I->assertNotEmpty($result['phone'],'Phone field is not empty');
                            $I->assertContains($result['line_type'],['MOBILE','LANDLINE']);
                            break;

                    }

                }
            }
        }

        /* match code output test */
        if(!in_array(strtolower($url),['/email','/phone','/ipinfo','/ip','/consumeridentityscore','/consumerstandardization'] )) {
        $params['match_code_output'] = 'True';
        $I->sendGet($url, $params);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $json = json_decode($I->grabResponse(), true, 10);
// If the number of results is greater than zero, verify if the match code is present in the records
        $num_results = $json['versium']['num_results'];
        if ($num_results > 0) {
            $results = $json['versium']['results'];
            $I->assertEquals($num_results, count($results), "Number of results is same as the records in results");

            foreach ($results as $result) {
                $I->assertNotEmpty($result['match_codes'], 'Match code is not empty');
            }
        }
    }
        $params['output'] = 'Json';
        $this->validateCaseSensitiveParameters($I,$url,$params);

        /* xml test */
        //$I->wantToTest('If the XML output');
        $params['output'] = 'xMl';
        $I->sendGet($url,$params);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsXml();
        $this->validateCaseSensitiveParametersXML($I,$url,$params);

           }

    public function validateCaseSensitiveParameters($I,$url,$params) {

        $new_params = $params;

        for ($index=0,$size = count($params);$index<$size;$index++) {

            $key = array_keys($params)[$index];
            $new_key = ucfirst($key);

            //remove the current key value pair
            unset($new_params[$key]);

            // add the uppercase first key with the original value
            $new_params[$new_key] = $params[$key];

            $I->sendGet($url, $new_params);
            if($key == 'key'){
                $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
            }
            else {
                $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
            }
            $json = json_decode($I->grabResponse(), true, 10);

            $unsupported_option_error_msg_format = "Unsupported option - This API service does not support the '%s' option. Please remove or correct it and try again";
            $err_msg = sprintf($unsupported_option_error_msg_format, $new_key);

            $error_index = 0;
            if($key == 'key') {
                $error_index = 1;
                $err_msg = $err_msg . ".";
            }
            $I->assertEquals($json['versium']['errors'][$error_index], $err_msg);

            //retain the original key and value
            unset($new_params[$new_key]);
            $new_params[$key] = $params[$key];
        }
    }


    public function validateCaseSensitiveParametersXML($I,$url,$params) {

        $new_params = $params;

        for ($index=0,$size = count($params);$index<$size;$index++) {


            $key = array_keys($params)[$index];
            if($key == 'output')
                continue;

            $new_key = ucfirst($key);

            //remove the current key value pair
            unset($new_params[$key]);

            // add the uppercase first key with the original value
            $new_params[$new_key] = $params[$key];

            $I->sendGet($url, $new_params);
            if($key == 'key'){
                $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
            }
            else {
                $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);

            }
            $I->seeResponseIsXml();

            /*
            $json = json_decode($I->grabResponse(), true, 10);

            $unsupported_option_error_msg_format = "Unsupported option - This API service does not support the '%s' option. Please remove or correct it and try again";
            $err_msg = sprintf($unsupported_option_error_msg_format, $new_key);

            $error_index = 0;
            if($key == 'key') {
                $error_index = 1;
                $err_msg = $err_msg . ".";
            }
            $I->assertEquals($json['versium']['errors'][$error_index], $err_msg);
             */

            //retain the original key and value
            unset($new_params[$new_key]);
            $new_params[$key] = $params[$key];
        }
    }

   }

