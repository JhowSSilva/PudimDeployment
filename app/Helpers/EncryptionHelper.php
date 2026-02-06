<?php

namespace App\Helpers;

class EncryptionHelper
{
    private string $encryptionKey;
    private string $cipher = 'aes-256-gcm';

    public function __construct()
    {
        // Usar a chave de criptografia do Laravel
        $this->encryptionKey = config('app.key');

        if (!$this->encryptionKey) {
            throw new \Exception('Chave de criptografia não configurada');
        }

        // Remove o prefix "base64:" se presente
        if (str_starts_with($this->encryptionKey, 'base64:')) {
            $this->encryptionKey = base64_decode(substr($this->encryptionKey, 7));
        }
    }

    /**
     * Criptografar dados
     *
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $tag = '';

        $encrypted = openssl_encrypt(
            $data,
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($encrypted === false) {
            throw new \Exception('Falha ao criptografar dados');
        }

        // Retornar IV + Tag + Dados criptografados (base64)
        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * Descriptografar dados
     *
     * @param string $encryptedData
     * @return string
     */
    public function decrypt(string $encryptedData): string
    {
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $tagLength = 16;

        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, $tagLength);
        $ciphertext = substr($data, $ivLength + $tagLength);

        $decrypted = openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new \Exception('Falha ao descriptografar dados');
        }

        return $decrypted;
    }
}
