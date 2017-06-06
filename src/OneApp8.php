<?php

namespace OneApp8;

use OneApp8\Config;
use OneApp8\HttpClient\CurlClient;
use OneApp8\Model\Register;

use phpseclib\Crypt\RSA;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Base;

class OneApp8 {

    var $signDate;
    var $simmetricKey;
    var $simmetricKeyEncrypted;
    var $body = '';
    var $bodyEncrypted = '';
    var $signingString;
    var $signingStringEncrypted;
    var $host;
    var $headers = [];

    var $responseHeaders = [];
    var $responseSimmetricKey;
    var $responseSigninString;


    var $register = null;

    public function __construct()
    {
        $this->host = parse_url(Config::get('main.1app8_rest_base_url'))['host'];
    }


    public function makeRequest($url, $method, $data)
    {
        $this->signDate = date('r');

        $this->simmetricKey = $this->generateSimmetricKey();
        $this->simmetricKeyEncrypted = $this->generateSimmetricKeyEncrypted();

        if($data)
        {
            $this->body = json_encode($data);
            $this->bodyEncrypted = $this->encryptBody();
            echo "Encrypted body: $this->bodyEncrypted\n";
        }
        $this->signingString = $this->generateSigninString($url, $method);

        $this->signingStringEncrypted = $this->encryptSigninString();
        $this->headers = $this->generateHeaders();

        $result = $this->execRequest($url, $method, $data);
        $this->responseHeaders = $this->getResponseHeaders($result['header']);
        $this->responseSigninString = $this->generateResponseSigninString($url, $method);

        $this->responseSimmetricKey = $this->decryptResponseSimmetricKey();


        $this->responseBodyDecrypted = $this->decryptResponseBody($result['body']);
        if (!$this->verifyResponseSigninString()) {
            return false;
        }

        return json_decode($this->responseBodyDecrypted);
    }

    public function execRequest($url, $method, $data)
    {
        /* $ch = \curl_init(); */
        $client = CurlClient::getInstance();

        if(count($this->headers))
        {
            $tmpHeaders = [];

            foreach($this->headers as $k => $v)
            {
                $tmpHeaders[] = $k . ':' . $v;
            }

            $tmpHeaders[] = 'Content-Type: application/json';

            /* print_r($tmpHeaders); */
            $url = Config::get('main.1app8_rest_base_url') . '/' . $url;
            return $client->request($method, $url, $this->headers, null, $this->bodyEncrypted);

            curl_setopt($ch,CURLOPT_HTTPHEADER,$tmpHeaders);
        }

        switch(strtoupper($method))
        {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                break;

            case 'PUT':
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;

            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;

            case 'GET':
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));

                break;
        }

        if(in_array(strtoupper($method), ['POST', 'PUT', 'DELETE']))
        {
            if ($this->bodyEncrypted)
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->bodyEncrypted);
            }
        }

        $url = Config::get('main.1app8_rest_base_url') . '/' . $url;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);
        print_r($info);
        print_r($error);
        print_r($response);

        return [
            'header' => $header,
            'body' => $body,
            'info' => $info,
            'error' => $error
        ];
    }

    public function generateSimmetricKey()
    {
        $method = 'AES-128-CBC';
        $ivlen = openssl_cipher_iv_length($method);
        $isCryptoStrong = false; // Will be set to true by the function if the algorithm used was cryptographically secure
        return openssl_random_pseudo_bytes($ivlen, $isCryptoStrong);
    }

    public function generateSimmetricKeyEncrypted()
    {
        /* $rsa = new \Crypt_RSA(); */
        $rsa = new RSA();
        /* $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1); */
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $rsa->setMGFHash('sha1');
        $rsa->setHash('sha256');
        $rsa->loadKey(file_get_contents(Config::get('main.rest_public_server_key')));
        return base64_encode($rsa->encrypt($this->simmetricKey));
    }

    public function encryptBody()
    {
        /* $aes = new \Crypt_AES(CRYPT_AES_MODE_CTR); */
        $aes = new AES(Base::MODE_CTR);
        $aes->setIV(hex2bin('04030705370321020501060208080300'));
        $aes->setKey($this->simmetricKey);
        return base64_encode($aes->encrypt($this->body));
    }

    public function generateSigninString($url, $method)
    {
        $string = '(request-line): ' . strtolower($method) . ' /v1/server/' . strtolower($url) . "\n";
        $string .= 'host: ' . $this->host . "\n";
        $string .= 'sign-date: ' . $this->signDate . "\n";
        $string .= 'content-length: ' . strlen($this->bodyEncrypted) . "\n";
        $string .= 'key: ' . $this->simmetricKeyEncrypted;
        return $string;
    }

    public function encryptSigninString()
    {
        /* $rsa = new \Crypt_RSA(); */
        $rsa = new RSA();
        $rsa->setHash("sha256");
        /* $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1); */
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $rsa->loadKey(file_get_contents(Config::get('main.rest_private_key')));
        return base64_encode($rsa->sign($this->signingString));
    }

    public function generateHeaders()
    {
        return [
                'Authorization' => 'Signature keyId="' . Config::get('main.1app8_rest_key_id') . '",algorithm="rsa-sha256",headers="(request-line) host sign-date content-length key", signature="' . $this->signingStringEncrypted .'"',
                'sign-date' => $this->signDate,
                'key' => $this->simmetricKeyEncrypted
            ];
    }

    public function getResponseHeaders($headers)
    {
        $headers = explode('<br />', nl2br($headers));

        if(count($headers))
        {
            $tmp = [];

            foreach($headers as $row)
            {
                $foo = explode(':', $row);

                if(count($foo) == 1)
                {
                    $tmp[] = $foo[0];
                }
                else
                {
                    $key = trim($foo[0]);
                    $values = array_shift($foo);
                    $tmp[$key] = trim(implode(':', $foo));
                }
            }

            $headers = $tmp;
        }

        return $headers;
    }


    public function decryptResponseSimmetricKey()
    {
        $rsa = new RSA();
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $rsa->setMGFHash('sha1');
        $rsa->setHash('sha256');
        $rsa->loadKey(file_get_contents(Config::get('main.rest_private_key')));

        /* print_r(base64_decode($this->responseHeaders['key'])); */
        return $rsa->decrypt(base64_decode($this->responseHeaders['key']));
    }

    public function decryptResponseBody($body)
    {
        /* $aes = new \Crypt_AES(CRYPT_AES_MODE_CTR); */
        $aes = new AES(Base::MODE_CTR);
        $aes->setIV(hex2bin('04030705370321020501060208080300'));
        $aes->setKey($this->responseSimmetricKey);
        return $aes->decrypt(base64_decode($body));
    }

    public function generateResponseSigninString($url, $method)
    {
        echo "\nHEADERS:\n";
        print_r($this->responseHeaders);
        $string = '(request-line): ' . strtolower($method) . ' /v1/server/' . strtolower($url) . "\n";
        $string .= 'host: ' . $this->responseHeaders['host'] . "\n";
        $string .= 'sign-date: ' . $this->responseHeaders['sign-date'] . "\n";
        $string .= 'content-length: ' . $this->responseHeaders['Content-Length'] . "\n";
        $string .= 'key: ' . $this->responseHeaders['key'];
        return $string;
    }

    public function verifyResponseSigninString()
    {
        preg_match_all('#\"(.*?)\"#', $this->responseHeaders['Authorization'], $foo);

        if(!isset($foo[1][3]))
            return false;

        $signature = base64_decode($foo[1][3]);

        /* $rsa = new \Crypt_RSA(); */
        $rsa = new RSA();
        $rsa->setHash("sha256");
        /* $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1); */
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $rsa->loadKey(file_get_contents(Config::get('main.rest_public_server_key')));

        return $rsa->verify($this->responseSigninString, $signature);
    }

    public static function generateBoardingPageSignature($data)
    {
        /* $rsa = new \Crypt_RSA(); */
        $rsa = new RSA();
        $rsa->setHash("sha256");
        /* $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1); */
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $rsa->loadKey(file_get_contents(Config::get('main.rest_public_server_key')));

        return $rsa->verify($this->responseSigninString, $signature);
    }

    // utility methods
    public function getRegister()
    {
        $registers = $this->makeRequest('registers', 'get', []);

        if(isset($registers->items) && count($registers->total)){
            $this->register = array_pop($registers->items);
            for ($i = 0; $i < count($this->register); $i++) {
                $reg = new Register();
                $reg->id = $this->register->id;
                print_r($reg);
            }
        }
        return $this->register;
    }

    public function generateOrder($customerPayment)
    {
        if(!$this->register)
            $this->getRegister();

        $order = new \stdClass;
        $order->externalId = $customerPayment->id;
        $order->created = date('c');
        $order->updated = date('c');
        $order->actions = [];
        $order->description = 'Consumi dal ' . date('d-m-Y', strtotime($customerPayment->period_start)) . ' al ' . date('d-m-Y', strtotime($customerPayment->period_end));
        $order->notes = '-';

        $order->total = new \stdClass;
        $order->total->currency = 'EUR';
        $order->total->amount = str_replace([',','.'], '', $customerPayment->amount);
        $order->total->decimalDigits = 2;

        $order->register = new \stdClass;
        $order->register->id = $this->register->id;
        $order->register->name = $this->register->name;
        $order->register->url = '';

        $generatedOrder = $this->makeRequest('orders', 'post', $order);

        if($generatedOrder)
        {
          $customerPayment->object_order = json_encode($generatedOrder);
          $customerPayment->save();
        }

        return $generatedOrder;
    }

    public function tryPayment($orderId, $walletId)
    {
        $payment = new \stdClass;
        $payment->order = new \stdClass;
        $payment->order->id = (string)$orderId;
        $payment->walletId = (string)$walletId;

        return $this->makeRequest('payments/wallet/moto', 'post', $payment);
    }
}
