<?php

namespace App\Http\Controllers;

use App\Services\SSHKeyGenerator;
use App\Models\SSHKey;
use App\Helpers\EncryptionHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SSHKeyController extends Controller
{
    private SSHKeyGenerator $keyGenerator;
    private EncryptionHelper $encryption;

    public function __construct()
    {
        $this->keyGenerator = new SSHKeyGenerator();
        $this->encryption = new EncryptionHelper();
    }

    /**
     * Gerar nova chave SSH
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:rsa,ed25519',
            'comment' => 'nullable|string|max:255',
            'passphrase' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $name = $request->input('name');
            $type = $request->input('type');
            $passphrase = $request->input('passphrase');
            $comment = $request->input('comment', '');

            // Gerar chave
            if ($type === 'ed25519') {
                $keyData = $this->keyGenerator->generateED25519Key($comment, $passphrase);
            } else {
                $keyData = $this->keyGenerator->generateRSAKey($comment, $passphrase);
            }

            // Criptografar chave privada antes de salvar
            $encryptedPrivateKey = $this->encryption->encrypt($keyData['private_key']);

            // Salvar no banco de dados
            $sshKey = SSHKey::create([
                'user_id' => $userId,
                'name' => $name,
                'type' => $keyData['type'],
                'bits' => $keyData['bits'],
                'public_key' => $keyData['public_key'],
                'private_key_encrypted' => $encryptedPrivateKey,
                'fingerprint' => $keyData['fingerprint'],
                'comment' => $comment,
                'has_passphrase' => !empty($passphrase),
            ]);

            return response()->json([
                'success' => true,
                'key' => [
                    'id' => $sshKey->id,
                    'name' => $sshKey->name,
                    'type' => $sshKey->type,
                    'bits' => $sshKey->bits,
                    'public_key' => $sshKey->public_key,
                    'fingerprint' => $sshKey->fingerprint,
                    'created_at' => $sshKey->created_at->format('d/m/Y H:i:s'),
                ],
                'message' => 'Chave SSH gerada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar chave SSH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar chave SSH existente
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'private_key' => 'required|string',
            'passphrase' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $name = $request->input('name');
            $privateKey = $request->input('private_key');
            $passphrase = $request->input('passphrase');

            // Importar e validar chave
            $keyData = $this->keyGenerator->importKey($privateKey, $name, $passphrase);

            // Criptografar chave privada
            $encryptedPrivateKey = $this->encryption->encrypt($keyData['private_key']);

            // Salvar no banco
            $sshKey = SSHKey::create([
                'user_id' => $userId,
                'name' => $name,
                'type' => $keyData['type'],
                'bits' => $keyData['bits'],
                'public_key' => $keyData['public_key'],
                'private_key_encrypted' => $encryptedPrivateKey,
                'fingerprint' => $keyData['fingerprint'],
                'comment' => $name,
                'has_passphrase' => !empty($passphrase),
            ]);

            return response()->json([
                'success' => true,
                'key' => [
                    'id' => $sshKey->id,
                    'name' => $sshKey->name,
                    'type' => $sshKey->type,
                    'bits' => $sshKey->bits,
                    'fingerprint' => $sshKey->fingerprint,
                ],
                'message' => 'Chave SSH importada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar chave SSH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar chaves do usuário
     */
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $keys = SSHKey::getByUserId($userId);

            return response()->json([
                'success' => true,
                'keys' => $keys
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar chaves: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter chave pública
     */
    public function getPublicKey(int $keyId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $key = SSHKey::getById($keyId, $userId);

            if (!$key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'public_key' => $key->public_key,
                'fingerprint' => $key->fingerprint
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter chave: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar chave
     */
    public function destroy(int $keyId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $deleted = SSHKey::deleteKey($keyId, $userId);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave não encontrada ou não pode ser deletada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chave deletada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar chave: ' . $e->getMessage()
            ], 500);
        }
    }
}
