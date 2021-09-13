<?php
include "__load.php";

KalkanCrypt_Init();
$tsaurl = "http://tsp.pki.gov.kz:80";
KalkanCrypt_TSASetUrl($tsaurl);

if(isset($argv[1])) {
    $hash = $argv[1];
    
    $container = "RSA256_8685ca18f0d0e0ffb988699c108201bdd104e888.p12";
    // $container = "GOSTKNCA_HEAD.p12";
    // $password = "Asdf1234";
    $password = "kankaP69!";
    $alias = "";
    $storage = $KCST_PKCS12;
    KalkanCrypt_LoadKeyStore($storage, $password, $container, $alias);

    $outSign = "";
    $outVerifyInfo = "";
    $outCert = "";
    $err = KalkanCrypt_SignData($alias, 518, $hash, $outSign);
    // $err = KalkanCrypt_VerifyData($alias, 518, $hash, 0, $outSign, $outData, $outVerifyInfo, $outCert);

    $j = array();

    if(!$err) {
        $j['sign'] = $outSign;
    } else {
        $j['sign'] = 'FAILED';
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

    KalkanCrypt_getCertFromCMS($outSign, 1, 518, $outCert);
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

    // echo json_encode($j);
    echo base64_encode(json_encode($j));
}

KalkanCrypt_Finalize();

?>
