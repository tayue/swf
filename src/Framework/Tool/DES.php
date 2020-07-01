<?php

namespace Framework\Tool;

/**
 * DES对称加密算法
 * @package Framework\Tool
 */
class DES
{
    public static function encrypt($plaintext, $key, $method = 'DES-CBC')
    {
        // 生成加密所需的初始化向量, 加密时缺失iv会抛出一个警告
        $ivlen = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivlen);
        // 按64bit一组填充明文
        //$plaintext = self::padding($plaintext);
        // 加密数据. 如果options参数为0, 则不再需要上述的填充操作. 如果options参数为1, 也不需要上述的填充操作, 但是返回的密文未经过base64编码. 如果options参数为2, 虽然PHP说明是自动0填充, 但实际未进行填充, 必须需要上述的填充操作进行手动填充. 上述手动填充的结果和options为0和1是自动填充的结果相同.
        $ciphertext = openssl_encrypt($plaintext, $method, $key, 1, $iv);
        // 生成hash
        $hash = hash_hmac('sha256', $ciphertext, $key, false);

        return base64_encode($iv . $hash . $ciphertext);

    }

    public static function decrypt($ciphertext, $key, $method = 'DES-CBC')
    {
        $ciphertext = base64_decode($ciphertext);
        // 从密文中获取iv
        $ivlen = openssl_cipher_iv_length($method);
        $iv = substr($ciphertext, 0, $ivlen);
        // 从密文中获取hash
        $hash = substr($ciphertext, $ivlen, 64);
        // 获取原始密文
        $ciphertext = substr($ciphertext, $ivlen + 64);
        // hash校验
        if (hash_equals($hash, hash_hmac('sha256', $ciphertext, $key, false))) {
            // 解密数据
            $plaintext = openssl_decrypt($ciphertext, $method, $key, 1, $iv) ?? false;
            // 去除填充数据. 加密时进行了填充才需要去填充
            //if($plaintext)
            //{
            //    $plaintext = self::unpadding($plaintext);
            //    echo $plaintext;
            //}
            return $plaintext;
        }

        return '解密失败';
    }

    // 按64bit一组填充数据
    private static function padding($plaintext)
    {
        $padding = 8 - (strlen($plaintext) % 8);
        $chr = chr($padding);

        return $plaintext . str_repeat($chr, $padding);
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