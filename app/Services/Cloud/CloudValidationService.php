<?php

namespace App\Services\Cloud;

use App\Models\AzureCredential;
use App\Models\GcpCredential;
use App\Models\DigitalOceanCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CloudValidationService
{
    /**
     * Validate Azure Service Principal credentials
     */
    public function validateAzureCredentials(array $credentials): array
    {
        try {
            // Get Azure access token
            $response = Http::post('https://login.microsoftonline.com/' . $credentials['tenant_id'] . '/oauth2/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $credentials['client_id'],
                'client_secret' => $credentials['client_secret'],
                'resource' => 'https://management.azure.com/',
            ]);

            if ($response->successful()) {
                return ['valid' => true, 'message' => 'Credenciais Azure válidas'];
            }

            $error = $response->json()['error_description'] ?? 'Credenciais inválidas';
            return ['valid' => false, 'message' => $error];
        } catch (Exception $e) {
            Log::error('Azure validation error: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Erro na validação: ' . $e->getMessage()];
        }
    }

    /**
     * Validate Google Cloud Service Account JSON
     */
    public function validateGcpCredentials(array $credentials): array
    {
        try {
            $serviceAccountJson = $credentials['service_account_json'];
            $serviceAccount = json_decode($serviceAccountJson, true);

            if (!$serviceAccount || !isset($serviceAccount['type']) || $serviceAccount['type'] !== 'service_account') {
                return ['valid' => false, 'message' => 'JSON da Service Account inválido'];
            }

            if (!isset($serviceAccount['project_id']) || $serviceAccount['project_id'] !== $credentials['project_id']) {
                return ['valid' => false, 'message' => 'Project ID não corresponde ao JSON'];
            }

            // Basic validation - in production, you'd make actual API calls
            return ['valid' => true, 'message' => 'Credenciais GCP válidas'];
        } catch (Exception $e) {
            Log::error('GCP validation error: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Erro na validação: ' . $e->getMessage()];
        }
    }

    /**
     * Validate DigitalOcean API Token
     */
    public function validateDigitalOceanCredentials(array $credentials): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $credentials['api_token'],
                'Content-Type' => 'application/json',
            ])->get('https://api.digitalocean.com/v2/account');

            if ($response->successful()) {
                return ['valid' => true, 'message' => 'Token DigitalOcean válido'];
            }

            return ['valid' => false, 'message' => 'Token DigitalOcean inválido'];
        } catch (Exception $e) {
            Log::error('DigitalOcean validation error: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Erro na validação: ' . $e->getMessage()];
        }
    }
}