<?php
namespace OneApp8;

/**
 * Class Config
 */
class Config
{
    public static function get(string $key)
    {
        $config = [];
        $cwd = getcwd();
        $config['main.1app8_rest_base_url'] = 'https://api.test.sparkling18.com/v1/server';
        $config['main.1app8_rest_pci_base_url'] = 'https://api.test.sparkling18.com/v1/server-pci';
        $config['main.rest_public_server_key'] = $cwd . '/keys/internalPublicKey_PHP-SDK.der';
        $config['main.rest_private_key'] = $cwd . '/keys/sdk-private-key.pem';
        $config['main.1app8_rest_key_id'] = '48';

        return $config[$key];
    }
}
