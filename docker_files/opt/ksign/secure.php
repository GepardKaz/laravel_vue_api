
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

    $flags_validate_ocsp = $KC_USE_OCSP;
    //$flags_validate_crl = $KC_USE_CRL;

    $validPath_ocsp = "http://ocsp.pki.gov.kz/";
    //$validPath_ocsp = "http://192.168.0.107:62545/ocsp/";
    $outInfo_ocsp = "";

    //$validPath_crl = "";
    //$outInfo_crl_ = "";
    //$outInfo_crl_d = "";

    if(strstr($sa, 'signatureAlgorithm=sha256WithRSAEncryption(1.2.840.113549.1.1.11)')){
      // $validPath_crl = "nca_rsa.crl";
       KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_CA, 'root_rsa.crt');
       KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_INTERMEDIATE, 'nca_rsa.crt' );

       //$err_crl = KalkanCrypt_X509ValidateCertificate( $pem, $flags_validate_crl, $validPath_crl, 0, $outInfo_crl);

         //if ($err_crl > 0){
           // $result_crl =  'Error in Validate :'. KalkanCrypt_GetLastErrorString();
        // } else {
          //  $result_crl = $outInfo_crl;
            //  $err_crl_d = KalkanCrypt_X509ValidateCertificate( $pem, $flags_validate_crl, 'nca_d_rsa.crl', 0, $outInfo_crl_d);

          // if($err_crl_d > 0) {
            //  $result_crl_d = 'Error in Validate :'. KalkanCrypt_GetLastErrorString();
          // }else{
            //  $result_crl_d = $outInfo_crl_d;
           //}
        //}

    }else{

      //$validPath_crl = "nca_gost.crl";
      KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_CA, 'root_gost.crt');
       KalkanCrypt_X509LoadCertificateFromFile($KC_CERT_INTERMEDIATE, 'nca_gost.crt' );

      //$err_crl_gost = KalkanCrypt_X509ValidateCertificate( $pem, $flags_validate_crl, $validPath_crl, 0, $outInfo_crl);

        // if ($err_crl_gost > 0){
           // $result_crl =  'Error in Validate :'. KalkanCrypt_GetLastErrorString();
         //} else {
            //$result_crl = $outInfo_crl;
            //$err_crl_gost_d = KalkanCrypt_X509ValidateCertificate( $pem, $flags_validate_crl, 'nca_d_gost.crl', 0, $outInfo_crl_d);

          // if($err_crl_gost_d > 0) {
           //   $result_crl_d = 'Error in Validate :'. KalkanCrypt_GetLastErrorString();
          // }else{
           //   $result_crl_d = $outInfo_crl_d;
         //  }
       // }
    }

    $err_ocsp = KalkanCrypt_X509ValidateCertificate( $pem, $flags_validate_ocsp, $validPath_ocsp, 0, $outInfo_ocsp);

    if ($err_ocsp > 0){
        $result_ocsp = 'Error in Validate :'. KalkanCrypt_GetLastErrorString();
    } else {
        $result_ocsp = $outInfo_ocsp;
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
        'sa' => str_replace('signatureAlgorithm=', '',  $sa),
        //'validate_crl' => $result_crl,
        //'validate_crl_d' => $result_crl_d,
        'validate_ocsp' => $result_ocsp
    );

    $j = base64_encode(json_encode($r));

    echo $j;
}

KalkanCrypt_Finalize();

?>
