<?php
class JWTConfig {
    // JWT密钥，请在生产环境中修改此值
    private static $secretKey = 'eW91ci1zZWNyZXQta2V5LWhlcmUtZG8tbm90LXVzZS1pbi1wcm9k';
    private static $algorithm = 'HS256';
    
    /**
     * 获取JWT密钥
     */
    public static function getSecretKey() {
        return self::$secretKey;
    }
    
    /**
     * 获取加密算法
     */
    public static function getAlgorithm() {
        return self::$algorithm;
    }
    
    /**
     * 生成JWT令牌
     */
    public static function encode(array $payload): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $payload = json_encode($payload);
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * 验证JWT令牌
     */
    public static function decode(string $jwt): ?array {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            return null;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;
        
        // 验证签名
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }
        
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        
        // 检查令牌是否过期
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
    
    /**
     * Base64 URL安全编码
     */
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL安全解码
     */
    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>