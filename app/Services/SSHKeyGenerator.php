<?php

namespace App\Services;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\EC;

class SSHKeyGenerator
{
    /**
     * Gerar chave RSA 4096 bits
     *
     * @param string $comment
     * @param string|null $passphrase
     * @return array
     */
    public function generateRSAKey(string $comment = '', ?string $passphrase = null): array
    {
        $rsa = RSA::createKey(4096);

        if ($passphrase) {
            $privateKey = $rsa->withPassword($passphrase)->toString('PKCS8');
        } else {
            $privateKey = $rsa->toString('PKCS8');
        }

        $publicKey = $rsa->getPublicKey()->toString('OpenSSH', ['comment' => $comment]);
        $fingerprint = $this->generateFingerprint($publicKey);

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
            'fingerprint' => $fingerprint,
            'type' => 'RSA',
            'bits' => 4096
        ];
    }

    /**
     * Gerar chave ED25519
     *
     * @param string $comment
     * @param string|null $passphrase
     * @return array
     */
    public function generateED25519Key(string $comment = '', ?string $passphrase = null): array
    {
        $ec = EC::createKey('Ed25519');

        if ($passphrase) {
            $privateKey = $ec->withPassword($passphrase)->toString('PKCS8');
        } else {
            $privateKey = $ec->toString('PKCS8');
        }

        $publicKey = $ec->getPublicKey()->toString('OpenSSH', ['comment' => $comment]);
        $fingerprint = $this->generateFingerprint($publicKey);

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
            'fingerprint' => $fingerprint,
            'type' => 'ED25519',
            'bits' => 256
        ];
    }

    /**
     * Gerar fingerprint SHA256
     *
     * @param string $publicKey
     * @return string
     */
    private function generateFingerprint(string $publicKey): string
    {
        $parts = explode(' ', $publicKey);
        $key = $parts[1] ?? '';
        
        if (empty($key)) {
            throw new \Exception('Chave pública inválida');
        }

        $raw = base64_decode($key);
        $hash = hash('sha256', $raw, true);
        return 'SHA256:' . rtrim(base64_encode($hash), '=');
    }

    /**
     * Importar chave SSH existente
     *
     * @param string $privateKey
     * @param string $name
     * @param string|null $passphrase
     * @return array
     */
    public function importKey(string $privateKey, string $name, ?string $passphrase = null): array
    {
        try {
            if ($passphrase) {
                $key = \phpseclib3\Crypt\PublicKeyLoader::load($privateKey, $passphrase);
            } else {
                $key = \phpseclib3\Crypt\PublicKeyLoader::load($privateKey);
            }

            $publicKey = $key->getPublicKey()->toString('OpenSSH', ['comment' => $name]);
            $fingerprint = $this->generateFingerprint($publicKey);

            // Determinar tipo de chave
            $type = 'RSA';
            $bits = 2048;
            
            if ($key instanceof RSA) {
                $type = 'RSA';
                $bits = $key->getLength();
            } elseif ($key instanceof EC) {
                $type = 'ED25519';
                $bits = 256;
            }

            return [
                'private_key' => $privateKey,
                'public_key' => $publicKey,
                'fingerprint' => $fingerprint,
                'type' => $type,
                'bits' => $bits,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Falha ao importar chave SSH: ' . $e->getMessage());
        }
    }
}
