<?php declare(strict_types=1);

namespace CourseHub\Common\Domain;

use CourseHub\Common\Domain\Types\RequiredUuid;
use Firebase\JWT\JWT;

class Jwks
{
    private static array $samplePrivateKey = [
        'key' => '-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCpNrzQJcvi9wWB
SR+aIjBSJMiyMv0fYPa5oVHDUwD3BHoZYvOtcDXbUZ6PVpW5JiD59cJJKMdn1FGX
39KK1IoRy7Wls8/BGxVl3pH4Z02HCRmnuyJMVY8NOIBxvjjheaMwdUYYsub9KlRe
faasR0JB7TCOBf6PKj0sHiLv0oYoDuirwfoZ6Z4i3R9x8jdSWASuakmgVHviir0y
uZHef7xZSroRJKWugAyltC99z7/CiuzsXUUvUEtu32YQNp5f+pLawNcelhhCrfGa
krjs5B68h+pRX4N3zjB7kV02qPG/tQTnai7fsZZSp4aXXA5jBxAMdadlgHsQdfp9
VOB4bC4zAgMBAAECggEACfFweN6vs1+GkElGtRzrL/h/XpFGdHCTLuc+0bZv6RP3
RMSewwND9uY95fhVhfKqn2WEjB38UaqVPNJnIcdJcbBBtUHypEs+H6VLeIhBcvzi
6AtEAa5JYKKAUa620vD92NwbSVpB3oB9QqwpfhgV2Z/haYcKpUj7HTuMHAGH5pSZ
LGmZfJpu5fOxZmEeLgsBMNKlpzRQKkaFdL5nbsxKIrNSzPr6Q7NxrXIyYpYiY6tw
2GPIM93ft/kf9j+p1e8b7/3D1eHWPNeZq5xNVwUlb78ySzWa/ZtLahAvm3G6nbmG
70FE1gohVS181LFyyhaHU8k6IpFvw27EvlOGm+RgaQKBgQDfKB8hMQaR778WJNUz
lw1U4GKUX15U+Kh0L1Q7NFBETyPGdu28IUl4eaCmLiX6hsHqsu4uSopRgDNaZKrJ
2+yippVYZix9+AT5vcOVaUrlxS/SsbljTS5NVLaeSBT5fJQgLT1oRAwcCSIk23Nc
EZag2XjzdYbJFeCjXmBviG8UxwKBgQDCHjVeHVeMEwlWvcGSlA75vOPYfvxxU+8z
DvjWay81GUc5x/cDKpTzLIzM3Y4vNa5AQgi+PNlqOImdfoAO6KL8j8s/uW5yj2hA
tNvWx3A+KI+Y5JdrzWfptYn/xVcj1ylbzPliNVd57zEW3qhQwwakoAmrxkG7wnEq
YEh4r/kXNQKBgHqb6L6r2RhFyY+5HQsDa2e/AWrUG1hEZwmvF3CQBaoCcX2Ryn0b
LOrsqL+li2hishjpxsPYYLb24UBBHzVZiGK1dEjlmnx70QAGq3dkKqqj9Oqmi11s
AoyqhsvgfuW09Q3YzWyENsMQoZnumSt03nXyBup9IFlLk+ku6X1eUOnxAoGBAJx7
u6vN3YhWxD1/pK8j2dqq0cXxwFEfR4GSfThkmV6lrQxATbjot8A8VpjDtd4K2RiF
4wRwixec0hr7IgJiuRj+Vetd8VI6luEqqtm+VpZVHkUzaXmG0VDOfpuIGtISng2Z
9COeOpFaJ+l2vpq0TQOi24j0voK8oXt2uJVJznRNAoGBAK25we9b+HmCi3U4NBe4
BVgZTYgwUdY2aYrsv0y1u5ZvUQvwWnNaeLUTlO49Z1OlwojR3hH7vvm8pudKUVoW
+FQX4Di9wBgV3r9+jsSgTiIvj/Em4rc4cUW2o/FAOOcUVIm8u+wogiy5tHBBf5cI
T6MBTSclULZY4CbN2nNcDHsF
-----END PRIVATE KEY-----',
        'kid' => '26f55c9e8f10efcf1119'
    ];

    public static function generatePrivateKeys(): array
    {
        /**
         * Will be used in easylearn production to generate keys and save in the database
         */
        $kid = bin2hex(openssl_random_pseudo_bytes(10));
        $config = array(
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privatekey);

        return [
            "key" => $privatekey,
            "kid" => $kid
        ];
    }

    public static function getPrivateKey(): array
    {
        return self::$samplePrivateKey;
    }


    public static function getJwks() {
        $jwks = array('keys' => array());

        $privatekey = self::getPrivateKey(); /** get this from the database later  */
        $res = openssl_pkey_get_private($privatekey['key']);
        $details = openssl_pkey_get_details($res);

        $jwk = array();
        $jwk['kty'] = 'RSA';
        $jwk['alg'] = 'RS256';
        $jwk['kid'] = $privatekey['kid'];
        $jwk['e'] = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');
        $jwk['n'] = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $jwk['use'] = 'sig';

        $jwks['keys'][] = $jwk;
        return $jwks;
    }

    public function verifyWithKeySet($jwtparam, $keyseturl, RequiredUuid $clientid) {

        $keyset = file_get_contents($keyseturl);
        $keysetarr = json_decode($keyset, true);
        // JWK::parseKeySet uses RS256 algorithm by default.
        $keys = Jwks::parseKeySet($keysetarr);
        $jwt = JWT::decode($jwtparam, $keys);

        return $jwt;
    }

}