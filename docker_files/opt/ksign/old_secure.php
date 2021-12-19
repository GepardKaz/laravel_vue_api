<?php
include "__load.php";

KalkanCrypt_Init();
$tsaurl = "http://tsp.pki.gov.kz:80";
KalkanCrypt_TSASetUrl($tsaurl);

if(isset($argv[1])) {
    $b64 = $argv[1];
    $pem = base64_decode($b64);

    // data
    $iin = '';
    $cn = '';
    $fn = '';
    $ln = '';
    $gn = '';
    $email = '';
    $company = '';
    $bin = '';
    $dt_start = '';
    $dt_end = '';
    $eku = '';
    $sa = '';

    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_COMMONNAME, $pem, $cn);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_GIVENNAME, $pem, $gn);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_SERIALNUMBER, $pem, $iin);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_EMAIL, $pem, $email);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_ORG_NAME, $pem, $company);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SUBJECT_ORGUNIT_NAME, $pem, $bin);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_NOTBEFORE, $pem, $dt_start);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_NOTAFTER, $pem, $dt_end);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_EXT_KEY_USAGE, $pem, $eku);
    KalkanCrypt_X509CertificateGetInfo($KC_CERTPROP_SIGNATURE_ALG, $pem, $sa);

    $cn = explode(' ', $cn);
    $fn = $cn[1];
    $ln = $cn[0];

    $flags_validate = $KC_USE_OCSP;
    //$validPath = "http://ocsp.pki.gov.kz/";
    $validPath = "http://192.168.0.93/ocsp/";
    $outInfo = "";

    if(strstr($sa, 'signatureAlgorithm=sha256WithRSAEncryption(1.2.840.113549.1.1.11)')){
       KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_CA, 'root_rsa.crt');
       KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_INTERMEDIATE, 'nca_rsa.crt' );
    }else{
      KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_CA, 'root_gost.crt');
      KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_INTERMEDIATE, 'nca_gost.crt' );
    }

    //KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_CA, 'root_gost.crt');
    //KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_INTERMEDIATE, 'nca_gost.crt' );
    $err = KalkanCrypt_X509ValidateCertificate( $pem, $flags_validate, $validPath, 0, $outInfo);
    // $err = 0;
    // $result = 'hello man';

    if ($err > 0){
        $result = 'Error in Validate :'. KalkanCrypt_GetLastErrorString();
    } else {
        $result = $outInfo;
    }

    $r = array(
        'iin' => str_replace('serialNumber=IIN', '', $iin),
        'fn' => str_replace('', '', $fn),
        'ln' => str_replace('CN=', '', $ln),
        'gn' => str_replace('GN=', '', $gn),
        'email' => str_replace('emailAddress=', '', $email),
        'company' => str_replace('O=', '', $company),
        'bin' => str_replace('OU=BIN', '',  $bin),
        'dts' => str_replace('notBefore=', '',  $dt_start),
        'dte' => str_replace('notAfter=', '',  $dt_end),
        'eku' => str_replace('extendedKeyUsage=', '',  $eku),
        'sa' => str_replace('extendedKeyUsage=', '',  $sa),
	'validate' => $result
    );

    $j = base64_encode(json_encode($r));

    echo $j;
}

KalkanCrypt_Finalize();

?>
