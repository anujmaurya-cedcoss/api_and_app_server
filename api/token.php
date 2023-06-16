<?php

namespace User\Token;

use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

function generateToken($app_key, $client_secret)
{
    // Defaults to 'sha512'
    $signer = new Hmac();

    // Builder object
    $builder = new Builder($signer);

    $now = new \DateTimeImmutable();
    $issued = $now->getTimestamp();
    $notBefore = $now->modify('-1 minute')->getTimestamp();
    $expires = $now->modify('+30 minute')->getTimestamp();
    $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2';

    $str = $app_key . ':' . $client_secret;
    // Setup
    $builder
        ->setExpirationTime($expires) // exp
        ->setIssuedAt($issued) // iat
        ->setIssuer('https://phalcon.io') // iss
        ->setNotBefore($notBefore) // nbf
        ->setSubject($str) // sub
        ->setPassphrase($passphrase) // password
    ;

    // Phalcon\Security\JWT\Token\Token object
    $tokenObject = $builder->getToken();

    // The token
    return $tokenObject->getToken();
}

function verifyToken($tokenReceived)
{
    $parser = new Parser();

    // Phalcon\Security\JWT\Token\Token object
    $tokenObject = $parser->parse($tokenReceived);
    // Phalcon\Security\JWT\Validator object
    
    $validator = new Validator($tokenObject, 100); // allow for a time shift of 100
    $expires = $tokenObject->getClaims()->getPayload()['iat'];
    // Throw exceptions if those do not validate
    $validator
        ->validateExpiration($expires);
    
    return $tokenObject->getClaims()->getPayload()['sub'];
}
