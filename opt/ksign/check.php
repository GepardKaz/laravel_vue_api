<?php
include "__load.php";

KalkanCrypt_Init();
$tsaurl = "http://tsp.pki.gov.kz:80";
KalkanCrypt_TSASetUrl($tsaurl);

if(isset($argv[1]) && isset($argv[2])) {
    $hash = $argv[1];
    $sign = $argv[2];
    
    $outData = "";
    $outVerifyInfo = "";
    $outCert = "";
    $err = KalkanCrypt_VerifyData('', 2162, $hash, 0, $sign, $outData, $outVerifyInfo, $outCert);
    // KalkanCrypt_getCertFromCMS($sign, 1, 2162, $outCert);
    // KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_SERIALNUMBER, $outCert, $iin);

    $r = str_replace(array("\r", "\t", "\n"), '', $outVerifyInfo);

    $j = array();

    if(strstr($r, 'CMS Verify - OK')) {
        $j['status'] = 'SUCCESS';
    } else {
        $j['status'] = 'FAILED';
    }

    // data
    $iin = '';
    $cn = '';
    $fn = '';
    $ln = '';
    $gn = '';
    $company = '';
    $bin = '';
    $dt_start = '';
    $dt_end = '';

    KalkanCrypt_getCertFromCMS($sign, 1, 2162, $outCert);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_SERIALNUMBER, $outCert, $iin);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_COMMONNAME, $outCert, $cn);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_GIVENNAME, $outCert, $gn);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_SERIALNUMBER, $outCert, $iin);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_ORG_NAME, $outCert, $company);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_ORGUNIT_NAME, $outCert, $bin);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_NOTBEFORE, $outCert, $dt_start);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_NOTAFTER, $outCert, $dt_end);

    $j['iin'] = str_replace('serialNumber=IIN', '', $iin);
    $j['bin'] = str_replace('OU=BIN', '',  $bin);
    $j['company'] = str_replace('O=', '', $company);
    $j['fio'] = str_replace('CN=', '', $cn).' '.str_replace('GN=', '', $gn);
    $j['dt'] = str_replace(array(' ALMT', 'notBefore='), '', $dt_start).' - '.str_replace(array(' ALMT', 'notAfter='), '', $dt_end);

    echo base64_encode(json_encode($j));
}

KalkanCrypt_Finalize();

?>