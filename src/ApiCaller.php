<?php
namespace OneApp8;

use OneApp8\Config;
use OneApp8\HttpClient\CurlClient;

use phpseclib\Crypt\RSA;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Base;

/**
 * Class ApiCaller
 *
 * @package OneApp8
 * @version 1.0-2017-06-05
 */
class ApiCaller
{
    private $connection = null;
    private $host = null;

    public function __construct()
    {
        $this->connection = CurlClient::getInstance();
        $this->host = parse_url(Config::get('main.1app8_rest_base_url'))['host'];
    }

    public function request($method, $url, $data)
    {
        $signDate = date('r');
        $symmetricKey = $this->generatesymmetricKey($method);
        $symmetricKeyEncrypted = $this->generatesymmetricKeyEncrypted($symmetricKey);

        $bodyAsJson = null;
        $bodyAsJsonEncrypted = null;
        if ($data) {
            $bodyAsJson = json_encode($data);
            $bodyAsJsonEncrypted = $this->encryptBody($symmetricKey, $bodyAsJson);
        }

        $signingString = $this->generateSigningString($method, $url, $signDate, $bodyAsJsonEncrypted, $symmetricKeyEncrypted);
        $signingStringEncrypted = $this->encryptSigningString($signingString);
        $headers = $this->generateHeaders($signDate, $signingStringEncrypted, $symmetricKeyEncrypted);

        $result = $this->execRequest($method, $url, $headers, $bodyAsJsonEncrypted);
        print_r($result);
        if (strtoupper($method) == 'DELETE') {
            return;
        }
        $responseHeaders = $this->getResponseHeaders($result['header']);
        $responseSigninString = $this->generateResponseSigninString($url, $method, $responseHeaders);
        $responseSymmetricKey = $this->decryptResponseSymmetricKey($responseHeaders);
        $responseBodyDecrypted = $this->decryptResponseBody($result['body'], $responseSymmetricKey);

        if (!$this->verifyResponseSigninString($responseHeaders, $responseSigninString)) {
            return false;
        }

        return json_decode($responseBodyDecrypted, true);
    }

    protected function verifyResponseSigninString($responseHeaders, $responseSigninString)
    {
        preg_match_all('#\"(.*?)\"#', $responseHeaders['Authorization'], $foo);

        if (!isset($foo[1][3])) {
            return false;
        }

        $signature = base64_decode($foo[1][3]);

        $rsa = new RSA();
        $rsa->setHash("sha256");
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $rsa->loadKey(file_get_contents(Config::get('main.rest_public_server_key')));

        return $rsa->verify($responseSigninString, $signature);
    }

    protected function decryptResponseBody($body, $responseSymmetricKey)
    {
        $aes = new AES(Base::MODE_CTR);
        $aes->setIV(hex2bin('04030705370321020501060208080300'));
        $aes->setKey($responseSymmetricKey);

        return $aes->decrypt(base64_decode($body));
    }

    protected function decryptResponseSymmetricKey($responseHeaders)
    {
        $rsa = new RSA();
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $rsa->setMGFHash('sha1');
        $rsa->setHash('sha256');
        $rsa->loadKey(file_get_contents(Config::get('main.rest_private_key')));

        return $rsa->decrypt(base64_decode($responseHeaders['key']));
    }

    protected function execRequest($method, $url, $headers, $data)
    {
        $client = $this->connection;

        if (count($headers)) {
            $tmpHeaders = [];

            foreach ($headers as $k => $v) {
                $tmpHeaders[] = $k . ':' . $v;
            }

            $tmpHeaders[] = 'Content-Type: application/json';

            $url = preg_match('/creditcards$/', $url) ? Config::get('main.1app8_rest_pci_base_url')."/$url" : Config::get('main.1app8_rest_base_url')."/$url";
        }

        return $client->request($method, $url, $headers, null, $data);
    }

    protected function generateResponseSigninString($url, $method, $responseHeaders)
    {
        $string = '(request-line): ' . strtolower($method) . ' /v1/server/' . strtolower($url) . "\n";
        $string .= 'host: ' . $responseHeaders['host'] . "\n";
        $string .= 'sign-date: ' . $responseHeaders['sign-date'] . "\n";
        $string .= 'content-length: ' . $responseHeaders['Content-Length'] . "\n";
        if ('DELETE' != strtoupper($method)) {
            $string .= 'key: ' . $responseHeaders['key'];
        }

        return $string;
    }

    protected function getResponseHeaders($headers)
    {
        $headers = explode('<br />', nl2br($headers));

        if (count($headers)) {
            $tmp = [];

            foreach ($headers as $row) {
                $foo = explode(':', $row);

                if (count($foo) == 1) {
                    $tmp[] = $foo[0];
                } else {
                    $key = trim($foo[0]);
                    $values = array_shift($foo);
                    $tmp[$key] = trim(implode(':', $foo));
                }
            }

            $headers = $tmp;
        }

        return $headers;
    }

    protected function generateHeaders($signDate, $signingStringEncrypted, $symmetricKeyEncrypted)
    {
        return [
                'Authorization' => 'Signature keyId="' . Config::get('main.1app8_rest_key_id') . '",algorithm="rsa-sha256",headers="(request-line) host sign-date content-length key", signature="' . $signingStringEncrypted .'"',
                'sign-date' => $signDate,
                'key' => $symmetricKeyEncrypted
            ];
    }

    protected function encryptSigningString($signingString)
    {
        $rsa = new RSA();
        $rsa->setHash("sha256");
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $rsa->loadKey(file_get_contents(Config::get('main.rest_private_key')));

        return base64_encode($rsa->sign($signingString));
    }

    protected function generateSigningString($method, $url, $signDate, $bodyEncrypted, $symmetricKeyEncrypted)
    {
        $path = '/v1/server/';
        if (preg_match('/creditcards$/', $url)) {
            $path = '/v1/server-pci/';
        }
        $string = '(request-line): ' . strtolower($method) . ' ' . $path . strtolower($url) . "\n";
        $string .= 'host: ' . $this->host . "\n";
        $string .= 'sign-date: ' . $signDate . "\n";
        $string .= 'content-length: ' . strlen($bodyEncrypted) . "\n";
        $string .= 'key: ' . $symmetricKeyEncrypted;

        return $string;
    }

    protected function encryptBody($symmetricKey, $body)
    {
        $aes = new AES(Base::MODE_CTR);
        $aes->setIV(hex2bin('04030705370321020501060208080300'));
        $aes->setKey($symmetricKey);

        return base64_encode($aes->encrypt($body));
    }

    protected function generateSymmetricKey($method)
    {
        $method = 'AES-128-CBC';
        $ivlen = openssl_cipher_iv_length($method);
        // Will be set to true by the function if the algorithm used was cryptographically secure
        $isCryptoStrong = false;

        return openssl_random_pseudo_bytes($ivlen, $isCryptoStrong);
    }

    protected function generateSymmetricKeyEncrypted($symmetricKey)
    {
        $rsa = new RSA();
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $rsa->setMGFHash('sha1');
        $rsa->setHash('sha256');
        $rsa->loadKey(file_get_contents(Config::get('main.rest_public_server_key')));

        return base64_encode($rsa->encrypt($symmetricKey));
    }

// END class ApiCaller
}
