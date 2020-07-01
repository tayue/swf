<?php


namespace Framework\Tool;

/**
 * AES对称加密算法
 * @package Framework\Tool
 */
class AES
{
    public function __construct()
    {
        // 是否启用了openssl扩展
        extension_loaded('openssl') or die('未启用 OPENSSL 扩展');
    }

    public static function encrypt($plaintext, $key, $method = 'aes-128-cbc')
    {
        if (!in_array($method, openssl_get_cipher_methods())) {
            die('不支持该加密算法!');
        }
        // options为1, 不需要手动填充
        //$plaintext = self::padding($plaintext);
        // 获取加密算法要求的初始化向量的长度
        $ivlen = openssl_cipher_iv_length($method);
        // 生成对应长度的初始化向量. aes-128模式下iv长度是16个字节, 也可以自由指定.
        $iv = openssl_random_pseudo_bytes($ivlen);
        // 加密数据
        $ciphertext = openssl_encrypt($plaintext, $method, $key, 1, $iv);
        $hmac = hash_hmac('sha256', $ciphertext, $key, false);

        return base64_encode($iv . $hmac . $ciphertext);
    }

    public static function decrypt($ciphertext, $key, $method = 'aes-128-cbc')
    {
        $ciphertext = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($method);
        $iv = substr($ciphertext, 0, $ivlen);
        $hmac = substr($ciphertext, $ivlen, 64);
        $ciphertext = substr($ciphertext, $ivlen + 64);
        $verifyHmac = hash_hmac('sha256', $ciphertext, $key, false);
        if (hash_equals($hmac, $verifyHmac)) {
            $plaintext = openssl_decrypt($ciphertext, $method, $key, 1, $iv) ?? false;
            // 加密时未手动填充, 不需要去填充
            //if($plaintext)
            //{
            //    $plaintext = self::unpadding($plaintext);
            //    echo $plaintext;
            //}

            return $plaintext;
        } else {
            die('数据被修改!');
        }
    }

    private static function padding(string $data): string
    {
        $padding = 16 - (strlen($data) % 16);
        $chr = chr($padding);
        return $data . str_repeat($chr, $padding);
    }

    private static function unpadding($ciphertext)
    {
        $chr = substr($ciphertext, -1);
        $padding = ord($chr);

        if ($padding > strlen($ciphertext)) {
            return false;
        }

        if (strspn($ciphertext, $chr, -1 * $padding, $padding) !== $padding) {
            return false;
        }

        return substr($ciphertext, 0, -1 * $padding);
    }
}