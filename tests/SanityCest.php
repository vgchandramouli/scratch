<?php


class SanityCest
{
    // TODO: Create a consumer class with these fields
    protected $email = 'ASHBINGS@GMAIL.COM';
    protected $first_name = 'ASHLEY';
    protected $last_name = 'BINGEL';
    protected $us_country_code = 'US';
    protected $eu_country_code = 'UK';
    protected $phone = '7707109553';
    protected $address = '62 MAGNOLIA PLACE CT';
    protected $city = 'SENOIA';
    protected $state = 'GA';
    protected $zip = '30276';


    // TODO: create a business contact class with these fields
    protected $business_contact_first_name = 'SATYA';
    protected $business_contact_last_name = 'NADELLA';
    protected $business_contact_phone = '4258828080';
    protected $business_contact_address = '1 Microsoft way';
    protected $business_contact_city = 'Redmond';
    protected $business_contact_state = 'WA';
    protected $business_contact_zip = '98052';
    protected $business_contact_business_name = 'Microsoft';
    protected $business_contact_domain_name = 'Microsoft.com';
    protected $business_contact_email = 'satyan@microsoft.com';


    protected $max_recs = 3;


    protected $email_info_api = '/eMail';
    protected $phone_lookup_api = '/phONE';
    protected $address_lookup_api = '/ADDreSs';
    protected $ip_lookup_api = '/iP';

    protected $consumer_identity_score_api = '/consumerIDentityscore';
    protected $consumer_api = '/conSUMer';
    protected $consumer_info_api = '/consumerINfo';
    protected $consumer_match_api = '/maTch';
    protected $consumer_standardization_api ='/consumerstanDARdization';

    protected $business_info_api = '/busiNESSinfo';
    protected $business_person_contact_api = '/busiNESScontact';
    protected $ip_info_api = '/ipINfo';

    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }


    protected function campaignProvider()
    {
        return [
            ['campaign_type' => 'CELlPHONE'],
            ['campaign_type' => 'EmAIL'],
            ['campaign_type' => 'LaNDLINE'],
            ['campaign_type' => 'mAIL'],
            ['campaign_type' => 'oNLINE'],
            ['campaign_type' => 'PhONE']
        ];

    }

    protected function businessCampaignProvider()
    {
        return [
            ['campaign_type' => 'EmAIL'],
            ['campaign_type' => 'MaIL'],
            ['campaign_type' => 'OnLINE']
        ];
    }


    protected function businessInfoProvider()
    {
        return [
            ['ticker' => 'MSFT', 'name' => 'Microsoft', 'domain' => 'microsoft.com', 'ip' => '23.96.52.53'],
            ['ticker' => 'AMZN', 'name' => 'Amazon', 'domain' => 'amazon.com', 'ip' => '176.32.98.166'],
            ['ticker' => 'AAPL', 'name' => 'Apple', 'domain' => 'apple.com', 'ip' => '17.142.160.59']
        ];
    }

    protected function EUCountriesDataProvider()
    {
        return [
            ['code' => 'BE'],//Belgium
            ['code' => 'BG'],//Bulgaria
            ['code' => 'CZ'],
            ['code' => 'DK'],
            ['code' => 'DE'],
            ['code' => 'EE'],
            ['code' => 'IE'],
            ['code' => 'EL'],
            ['code' => 'ES'],
            ['code' => 'FR'],
            ['code' => 'HR'],
            ['code' => 'IT'],
            ['code' => 'CY'],
            ['code' => 'LV'],
            ['code' => 'LT'],
            ['code' => 'LU'],
            ['code' => 'HU'],
            ['code' => 'MT'],
            ['code' => 'NL'],
            ['code' => 'AT'],
            ['code' => 'PL'],
            ['code' => 'PT'],
            ['code' => 'RO'],
            ['code' => 'SI'],
            ['code' => 'SK'],
            ['code' => 'FI'],
            ['code' => 'SE'],
            ['code' => 'UK']
        ];
    }

    // tests
    /**
     * @dataProvider EUCountriesDataProvider
     */
    public function EUCountriesEmailLookup(ApiTester $I, \Codeception\Example $country)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'email' => $this->email,
            'country' => $country['code'],
            'max_recs' => $this->max_recs
        ];


        $I->callAPIAndValidateJSON($I, $this->email_info_api, $params);
    }

    /**
     * @dataProvider campaignProvider
     */
    public function EmailLookup(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'email' => $this->email,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];


        $I->callAPIAndValidateJSON($I, $this->email_info_api, $params);
    }

    /**
     * @dataProvider campaignProvider
     */
    public function PhoneLookup(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'phone' => '9075296730',//$this->phone,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->phone_lookup_api, $params);
    }


    /**
     * @dataProvider campaignProvider
     */
    public function AddressLookupCityState(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'address' => $this->address,
            'state' => $this->state,
            'city' => $this->city,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->address_lookup_api, $params);
    }

    /**
     * @dataProvider campaignProvider
     */
    public function AddressLookupZip(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'address' => $this->address,
            'zip' => $this->zip,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->address_lookup_api, $params);
    }


    public function ConsumerIdentityScore(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'zip' => $this->zip,
            // unsupported 'campaign' => $campaign['campaign_type'],
            // unsupported 'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_identity_score_api, $params);
    }

    public function ConsumerStandardization(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'address' => $this->address

            // unsupported 'campaign' => $campaign['campaign_type'],
            // unsupported 'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_standardization_api, $params);
    }


    /**
     * @dataProvider campaignProvider
     */
    public function ConsumerContactPhoneEmail(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_api, $params);
    }

    /**
     * @dataProvider campaignProvider
     */
    public function ConsumerContactAddressCityState(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_api, $params);
    }

    /**
     * @dataProvider campaignProvider

    public function ConsumerContactAddressCityStateCaseSensitiveParametersExpectError(ApiTester $I, \Codeception\Example $campaign)
    {
        $max_recs = 'max_recs';
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'campaign' => $campaign['campaign_type'],
            $max_recs => $this->max_recs
        ];

        $new_params = $params;

        for ($i=1,$size = count($params);$i<$size;$i++) {

            $key = array_keys($params)[$i];
            $new_key = ucfirst($key);

            //remove the current key value pair
            unset($new_params[$key]);

            // add the uppercase first key with the original value
            $new_params[$new_key] = $params[$key];

            $I->sendGet($this->consumer_api, $new_params);
            $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
            $json = json_decode($I->grabResponse(), true, 10);

            $unsupported_option_error_msg_format = "Unsupported option - This API service does not support the '$new_key' option. Please remove or correct it and try again";
            $err_msg = sprintf($unsupported_option_error_msg_format, $max_recs);

            $I->assertEquals($json['versium']['errors'][0], $err_msg);

            //retain the original key and value
            unset($new_params[$new_key]);
            $new_params[$key] = $params[$key];
        }
    }
*/

    /**
     * @dataProvider campaignProvider
     */
    public function ConsumerContactAddressZip(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'address' => $this->address,
            'zip' => $this->zip,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_api, $params);
    }

    /**
     * @dataProvider campaignProvider
     */
    public function ConsumerContactCityState(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'city' => $this->city,
            'state' => $this->state,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_api, $params);
    }

    /**
     * @dataProvider campaignProvider
     */
    public function ConsumerContactState(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'state' => $this->state,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_api, $params);
    }

    /**
     * @dataProvider campaignProvider
     */
    public function ConsumerContactZip(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'zip' => $this->zip,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_api, $params);
    }


    public function ConsumerInfoPhone(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'phone' => $this->phone,
            // unsupported 'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_info_api, $params);
    }

    public function ConsumerInfoEmail(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'email' => $this->email,
            // unsupported 'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_info_api, $params);
    }

    public function ConsumerInfoAddressCityState(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            // unsupported 'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_info_api, $params);
    }

    public function ConsumerInfoAddressZip(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'address' => $this->address,
            'zip' => $this->zip,
            // unsupported 'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_info_api, $params);
    }

    public function ConsumerInfoZip(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'zip' => $this->zip,
            // unsupported 'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_info_api, $params);
    }

    public function ConsumerInfoCityState(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'city' => $this->city,
            'state' => $this->state,
            // unsupported 'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_info_api, $params);
    }

    public function ConsumerInfoState(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'state' => $this->state,
            // unsupported 'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_info_api, $params);
    }


    public function ConsumerMatchFirstName(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'match_first' => $this->first_name
            // unsupported 'campaign' => $campaign['campaign_type'],
            // unsupported 'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_match_api, $params);
    }

    public function ConsumerMatchLastName(ApiTester $I)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->first_name,
            'last' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'match_first' => $this->last_name
            // unsupported 'campaign' => $campaign['campaign_type'],
            // unsupported 'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->consumer_match_api, $params);
    }

    /**
     * @dataProvider businessInfoProvider
     */
    public function BusinessInfoTicker(ApiTester $I, \Codeception\Example $business)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'ticker' => $business['ticker'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_info_api, $params);
    }

    /**
     * @dataProvider businessInfoProvider
     */
    public function BusinessInfoBusinessName(ApiTester $I, \Codeception\Example $business)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'business' => $business['name'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_info_api, $params);
    }

    /**
     * @dataProvider businessInfoProvider
     */
    public function BusinessInfoDomainName(ApiTester $I, \Codeception\Example $business)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'domain' => $business['domain'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_info_api, $params);
    }

    /**
     * @dataProvider businessInfoProvider
     */
    public function BusinessInfoIp(ApiTester $I, \Codeception\Example $business)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'ip' => $business['ip']/*,
            'max_recs' => $this->max_recs*/
        ];

        $I->callAPIAndValidateJSON($I, $this->ip_info_api, $params);
    }

    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoBusiness(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'business' => $this->business_contact_business_name,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }

    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoDomain(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'domain' => $this->business_contact_domain_name,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }

    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoState(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'state' => $this->business_contact_state,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }

    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoZip(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'zip' => $this->business_contact_zip,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }


    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoPhone(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'phone' => $this->business_contact_phone,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }


    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoEmail(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'email' => $this->business_contact_email,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }

    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoAddressCityState(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'address' => $this->business_contact_address,
            'city' => $this->business_contact_city,
            'state' => $this->business_contact_state,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }

    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoAddressZip(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'address' => $this->business_contact_address,
            'zip' => $this->business_contact_zip,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }

    /**
     * @dataProvider businessCampaignProvider
     */
    public function BusinessContactInfoCityState(ApiTester $I, \Codeception\Example $campaign)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'first' => $this->business_contact_first_name,
            'last' => $this->business_contact_last_name,
            'city' => $this->business_contact_city,
            'state' => $this->business_contact_state,
            'campaign' => $campaign['campaign_type'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->business_person_contact_api, $params);
    }

    /**
     * @dataProvider businessInfoProvider
     */
    public function IpLookup(ApiTester $I, \Codeception\Example $business)
    {
        $params = [
            'key' => $I->getBusinessKey(),
            'ip' => $business['ip'],
            'max_recs' => $this->max_recs
        ];

        $I->callAPIAndValidateJSON($I, $this->ip_lookup_api, $params);
    }

}
