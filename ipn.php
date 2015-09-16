<?php
//header("Content-Type: text/plain");

// Instantiate Class and variables
//
include_once('classes/classes.inc.php');

// Initialise var
//
$arg       = (empty($_POST)) ? NULL : $_POST;
$siteId    = (isset($_POST["vads_site_id"])) ? $_POST["vads_site_id"] : NULL;
$signature = (isset($_POST["signature"])) ? $_POST["signature"] : NULL;

// If vads_site_id or databale doesn't exist die 
//
if (empty($siteId)) die("IPN triggered without valid Data");
if (!file_exists(DATABASE_FILE)) die("run InitScript");

// Get key prom parameters and validate signature
//
$iniValues = parse_ini_file("config/key.ini",true);
$id        = array_search($siteId, array_column($iniValues, 'site_id'));
$idKey     = array_keys($iniValues);
$key       = $iniValues[$idKey[$id]]['key_test']; //todo manage PRODUCTION mode too

// Signature check
//
$k = '';
ksort($arg);
foreach ($arg as $param => $val) {
    if(substr($param,0,5) == 'vads_') {
       $k .= $val."+";
    }
}
$hash = sha1($k.$key);

// Signature check result
//
$checked   = ($hash == $signature) ? 'true' : 'false';

// Database instanciation
//
$db = new SQLite3(DATABASE_FILE);

// Insert data
//
$db->exec("
  INSERT INTO ipn (status, vads_site_id, vads_url_check_src, vads_payment_src, vads_shop_name, vads_ctx_mode, vads_trans_uuid, vads_order_id, vads_order_info, vads_payment_config, vads_effective_creation_date, vads_operation_type, vads_trans_status, vads_result, vads_extra_result, vads_effective_amount, vads_currency, vads_contract_used, vads_auth_mode, vads_card_brand, vads_card_number, vads_payment_seq, vads_cust_email, vads_capture_delay, vads_presentation_date, vads_warranty_result, vads_risk_control, vads_validation_mode, vads_recurrence_status, vads_identifier_status, vads_identifier, vads_subscription, vads_sub_desc, vads_sub_effect_date, vads_sub_currency, vads_sub_amount, vads_sub_init_amount_number, vads_sub_init_amount, vads_contrib, vads_ext_info_donation, vads_ext_info_donation_recipient, vads_ext_info_donation_recipient_name, vads_ext_info_donation_merchant, full, signature, checked)

  VALUES (
    'NEW', '"
    .$arg['vads_site_id']."', '"
    .$arg['vads_url_check_src']."', '"
    .$arg['vads_payment_src']."', '"
    .$arg['vads_shop_name']."', '"
    .$arg['vads_ctx_mode']."', '"
    .$arg['vads_trans_uuid']."', '"
    .$arg['vads_order_id']."', '"
    .$arg['vads_order_info']."', '"
    .$arg['vads_payment_config']."', '"
    .$arg['vads_effective_creation_date']."', '"
    .$arg['vads_operation_type']."', '"
    .$arg['vads_trans_status']."', '"
    .$arg['vads_result']."', '"
    .$arg['vads_extra_result']."', '"
    .$arg['vads_effective_amount']."', '"
    .$arg['vads_currency']."', '"
    .$arg['vads_contract_used']."', '"
    .$arg['vads_auth_mode']."', '"
    .$arg['vads_card_brand']."', '"
    .$arg['vads_card_number']."', '"
    .$arg['vads_payment_seq']."', '"
    .$arg['vads_cust_email']."', '"

    .$arg['vads_capture_delay']."', '"
    .$arg['vads_presentation_date']."', '"
    .$arg['vads_warranty_result']."', '"
    .$arg['vads_risk_control']."', '"
    .$arg['vads_validation_mode']."', '"
    .$arg['vads_recurrence_status']."', '"
    .$arg['vads_identifier_status']."', '"
    .$arg['vads_identifier']."', '"
    .$arg['vads_subscription']."', '"
    .$arg['vads_sub_desc']."', '"
    .$arg['vads_sub_effect_date']."', '"
    .$arg['vads_sub_currency']."', '"
    .$arg['vads_sub_amount']."', '"
    .$arg['vads_sub_init_amount_number']."', '"
    .$arg['vads_sub_init_amount']."', '"
    .$arg['vads_contrib']."', '"
    .$arg['vads_ext_info_donation']."', '"
    .$arg['vads_ext_info_donation_recipient']."', '"
    .$arg['vads_ext_info_donation_recipient_name']."', '"
    .$arg['vads_ext_info_donation_merchant']."', '"


    .json_encode($arg)."', '"
    .$arg['signature']."', '"
    .$checked
    ."')");

// Error & Timeout managment
//
if ($arg['vads_ext_info_IpnError']=="TimeOut") {
  sleep(rand(37, 49));
} elseif (preg_match("/[1-5]{1}[0-2]{1}[0-9]{1}/",$arg['vads_ext_info_IpnError'])) {
  header_status($arg['vads_ext_info_IpnError']);
}


// Tel result
//
echo "IPN signature=$checked sqlite=[".$db->lastErrorMsg()."]  ";

//
// End

function header_status($statusCode) {
    static $status_codes = null;

    if ($status_codes === null) {
        $status_codes = array (
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );
    }

    if ($status_codes[$statusCode] !== null) {
        $status_string = $statusCode . ' ' . $status_codes[$statusCode];
        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);
    }
}
