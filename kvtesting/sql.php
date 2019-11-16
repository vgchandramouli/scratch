<?php
$servername = "127.0.0.1";
$username = "root";
$password = "iGH4.fLu";
$dbname = "StarniumHoldings";

define ('TOTAL_RECORD_COUNT','totalRecordCount');
define ('UNIQUE_DOMAIN_KEY_COUNT','uniqueDomainKeyCount');
define ('UNIQUE_CORP_DOMAIN_COUNT','uniqueCorpDomainCount');
define ('UNIQUE_CORP_NAME_COUNT','uniqueCorpNameCount');
define ('UNIQUE_EMAIL_COUNT','uniqueEmailCount');
define ('UNIQUE_SIC_COUNT','uniqueSICCount');
define ('TOP_DOMAINS','topDomains');
define ('TOP_COMPANIES','topCompanies');
define ('SIC_DISTRIBUTION','SICDistribution');
define ('LAST_NAME_FIRST_NAME_MATCH_COUNT','lastNameFirstNameMatchCount');
define ('BAD_CHAR_IN_EMAIL_RECORD_COUNT','badCharacterInEmailRecordCount');

function executeQuery($conn,$query)
{
	echo "\nExecuting the query:\n $query";
	$result = $conn->query($query);
	echo "\nDone.\n";
	return $result;

}

function executeQueryGetAssocArray($conn,$query)
{

	$result = executeQuery($conn,$query);
	$rows = array();
	while($r = $result->fetch_assoc())
		$rows[] = $r;
	return $rows;
}

function executeQueryCount($conn,$sql,$fieldName)
{
	$query = sprintf($sql,$fieldName);
	$result = executeQuery($conn,$query);
	// print_r($result);

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		print_r($row);
		return $row[$fieldName];
	}
	return -999;
}

function getDistinctQueryForKey($key)
{
	return sprintf('select count(distinct(`%s`)) as %%s from `KittiversePINDIV`',$key);

}

function getTotalRecordCount($conn)
{
	$query = 'select count(*) as %s from `KittiversePINDIV`';
	return executeQuerycount($conn,$query,TOTAL_RECORD_COUNT);
}

function getUniqueDomainKeyCount($conn)
{
	$query = getDistinctQueryForKey('dk');
	//'select count(distinct(`dk`)) as %s from `KittiversePINDIV`';
	return executeQuerycount($conn,$query,UNIQUE_DOMAIN_KEY_COUNT);
}

function getUniqueCorpDomainCount($conn)
{
	$query = getDistinctQueryForKey('CorpDomain');
	return executeQuerycount($conn,$query,UNIQUE_CORP_DOMAIN_COUNT);
}

function getUniqueCorpNameCount($conn)
{
	$query = getDistinctQueryForKey('CorpName');
	return executeQuerycount($conn,$query,UNIQUE_CORP_NAME_COUNT);
}

function getUniqueEmailCount($conn)
{
	$query = getDistinctQueryForKey('EmailAddr');
	return executeQuerycount($conn,$query,UNIQUE_EMAIL_COUNT);
}

function getUniqueSICCount($conn)
{
	$query = getDistinctQueryForKey('SIC');
	return executeQuerycount($conn,$query,UNIQUE_SIC_COUNT);
}

function getListOfDomainsGreaterThan10KRecords($conn)
{
	$query = 'select `CorpDomain`,count(`CorpDomain`) as ct '.  
		 'from (select `CorpDomain` from `KittiversePINDIV`) as dt '.
		 'group by `CorpDomain` '.
		 'having ct > "9999" '.
		 'order by ct DESC ';

	return executeQueryGetAssocArray($conn,$query);

}

function getListOfCorpNamesGreaterThan10KRecords($conn)
{
	$query = 'select `CorpName`,count(`CorpName`) as ct '.  
		 'from (select `CorpName` from `KittiversePINDIV`) as dt '.
		 'group by `CorpName` '.
		 'having ct > "9999" '.
		 'order by ct DESC ';

	return executeQueryGetAssocArray($conn,$query);
	/*
	$result = executeQuery($conn,$query);

	$rows = array();
	while($r = $result->fetch_assoc())
		$rows[] = $r;
	return $rows;*/
}

function getSICDistribution($conn)
{
	$query = 'select `SIC`,`Industry`,count(`CorpDomain`) as ct '.
		 'from (select distinct(`CorpDomain`),`SIC`,`Industry` from `KittiversePINDIV`) as dt '.
		 'group by `SIC`,`Industry` '.
		 'order by ct desc';
	
	return executeQueryGetAssocArray($conn,$query);

}

function getLastNameFirstNameMatchCount($conn)
{
	$query = 'select count(*) as %s from `KittiversePINDIV` where left(`FirstName`, 4) = left(`LastName`,4)';
	return executeQuerycount($conn,$query,LAST_NAME_FIRST_NAME_MATCH_COUNT);
}

function getBadCharacterInEmailRecordCount($conn)
{
	$query = 'select count(*) as %s from `KittiversePINDIV` where `EmailAddr` like "%%?%%"';
	return executeQuerycount($conn,$query,BAD_CHAR_IN_EMAIL_RECORD_COUNT);
}

function safe_json_encode($value, $options = 0, $depth = 512, $utfErrorFlag = false) {
    $encoded = json_encode($value, $options, $depth);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return $encoded;
        case JSON_ERROR_DEPTH:
            return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
        case JSON_ERROR_SYNTAX:
            return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_UTF8:
            $clean = utf8ize($value);
            if ($utfErrorFlag) {
                return 'UTF8 encoding error'; // or trigger_error() or throw new Exception()
            }
            return safe_json_encode($clean, $options, $depth, true);
        default:
            return 'Unknown error'; // or trigger_error() or throw new Exception()

    }
}

function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } else if (is_string ($mixed)) {
        return utf8_encode($mixed);
    }
    return $mixed;
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$db = array();

// get the total record count
$db[TOTAL_RECORD_COUNT] = getTotalRecordCount($conn);
$db[UNIQUE_DOMAIN_KEY_COUNT] = getUniqueDomainKeyCount($conn);
$db[UNIQUE_CORP_DOMAIN_COUNT] = getUniqueCorpDomainCount($conn);
$db[UNIQUE_CORP_NAME_COUNT] = getUniqueCorpNameCount($conn);
$db[UNIQUE_EMAIL_COUNT] = getUniqueEmailCount($conn);
$db[UNIQUE_SIC_COUNT] = getUniqueSICCount($conn);
$db[TOP_DOMAINS] = getListOfDomainsGreaterThan10KRecords($conn);
$db[TOP_COMPANIES] = getListOfCorpNamesGreaterThan10KRecords($conn);
$db[SIC_DISTRIBUTION] = getSICDistribution($conn);
$db[LAST_NAME_FIRST_NAME_MATCH_COUNT] = getLastNameFirstNameMatchCount($conn);
$db[BAD_CHAR_IN_EMAIL_RECORD_COUNT] = getBadCharacterInEmailRecordCount($conn);

print_r($db);

/*
$fp = fopen('db.json', 'w');
fwrite($fp, json_encode($db));
fclose($fp);
 */
file_put_contents('new.json',safe_json_encode($db,JSON_PRETTY_PRINT));

$conn->close();
?>
