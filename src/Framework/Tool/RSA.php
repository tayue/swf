<?php


namespace Framework\Tool;

/**
 * RSA 非对称加密算法
 * @package Framework\Tool
 * 在传输重要信息时, 一般会采用对称加密和非对称加密相结合的方式, 而非使用单一加密方式. 一般先通过 AES 加密数据,
 * 然后通过 RSA 加密 AES 密钥, 然后将加密后的密钥和数据一起发送. 接收方接收到数据后, 先解密 AES 密钥,
 * 然后使用解密后的密钥解密数据.
 */
class RSA
{
    // 生成新的公钥和私钥对资源
    public static function createKeys()
    {
        extension_loaded('openssl') or die('未加载 openssl');
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 1204,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ];
        $res = openssl_pkey_new($config);
        // 获取公钥, 生成公钥资源
        $public_key = $private_key = null;
        $public_key = openssl_pkey_get_details($res)['key'];
        // 获取私钥, 生成私钥资源
        openssl_pkey_export($res, $private_key);
        openssl_free_key($res);
        return compact('public_key', 'private_key');
    }


    // 加密
    public static function encrypt($plaintext, $public_key)
    {
        $ciphertext = null;
        $public_res = openssl_pkey_get_public($public_key);
        if ($public_res) {
            openssl_public_encrypt($plaintext, $ciphertext, $public_res);
        }
        return $ciphertext;
    }

    // 解密
    public static function decrypt($ciphertext, $private_key)
    {
        $plaintext = null;
        $private_res = openssl_pkey_get_private($private_key);
        if ($private_res) {
            openssl_private_decrypt($ciphertext, $plaintext, $private_res);
        }
        return $plaintext;
    }
}