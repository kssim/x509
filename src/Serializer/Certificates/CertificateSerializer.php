<?php

namespace Mdanter\X509\Serializer\Certificates;

use Mdanter\X509\Certificates\Certificate;
use Mdanter\X509\Certificates\CertificateInfo;
use Mdanter\X509\Serializer\Certificates\Extensions\AbstractExtensions;
use Mdanter\X509\Serializer\Signature\DerSignatureSerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\X509\Serializer\Certificates\Certificate\Parser;
use Mdanter\X509\Serializer\Certificates\Certificate\Formatter;

class CertificateSerializer
{
    const HEADER = '-----BEGIN CERTIFICATE-----';
    const FOOTER = '-----END CERTIFICATE-----';
    const ECPUBKEY_OID = '1.2.840.10045.2.1';
    const UTCTIME_FORMAT = 'Y-m-d\tH:i:s';

    /**
     * @param CertificateSubjectSerializer $subSerializer
     * @param DerPublicKeySerializer $pubKeySerializer
     * @param DerSignatureSerializer $sigSerializer
     * @param AbstractExtensions|null $extension
     */
    public function __construct(CertificateSubjectSerializer $subSerializer, DerPublicKeySerializer $pubKeySerializer, DerSignatureSerializer $sigSerializer, AbstractExtensions $extension = null)
    {
        $this->parser = new Parser($pubKeySerializer, $sigSerializer);
        $this->formatter = new Formatter($subSerializer, $pubKeySerializer, $sigSerializer, $extension);
    }

    /**
     * @param Certificate $certificate
     * @return string
     */
    public function serialize(Certificate $certificate)
    {
        $payload = $this->formatter->getCertificateASN($certificate)->getBinary();
        $base64 = base64_encode($payload);
        $content = trim(chunk_split($base64, 64, PHP_EOL)).PHP_EOL;

        return self::HEADER . PHP_EOL
        . $content
        . self::FOOTER . PHP_EOL;
    }

    /**
     * @param CertificateInfo $info
     * @return string
     */
    public function getSignatureData(CertificateInfo $info)
    {
        return $this->formatter->getCertInfoAsn($info)->getBinary();
    }

    /**
     * @param string $certificate
     * @return Certificate
     * @throws \Exception
     * @throws \FG\ASN1\Exception\ParserException
     */
    public function parse($certificate)
    {
        return $this->parser->parse($certificate);
    }
}
