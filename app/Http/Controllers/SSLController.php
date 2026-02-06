<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SSLCertificate;
use App\Services\SSLService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SSLController extends Controller
{
    public function __construct(
        private SSLService $sslService
    ) {}

    /**
     * Global SSL certificates index - shows certificates from all sites
     */
    public function globalIndex()
    {
        $currentTeam = Auth::user()->getCurrentTeam();
        
        $servers = $currentTeam->servers()
            ->with(['sites.sslCertificates'])
            ->get();
            
        return view('ssl.global-index', compact('servers'));
    }

    /**
     * Show SSL management page for a site
     */
    public function show(Site $site)
    {
        $site->load('sslCertificates');
        
        return view('ssl.show', compact('site'));
    }

    /**
     * Enable Let's Encrypt SSL for a site
     */
    public function enableLetsEncrypt(Site $site, Request $request)
    {
        $request->validate([
            'domains' => 'sometimes|array',
            'domains.*' => 'string|max:255',
        ]);

        $domains = $request->input('domains', []);
        
        $result = $this->sslService->enableLetsEncrypt($site, $domains);
        
        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        
        return back()->withErrors(['ssl' => $result['message']]);
    }

    /**
     * Upload custom SSL certificate
     */
    public function uploadCustom(Site $site, Request $request)
    {
        $request->validate([
            'certificate' => 'required|string',
            'private_key' => 'required|string',
            'certificate_chain' => 'nullable|string',
        ]);

        $result = $this->sslService->uploadCustomCertificate(
            $site,
            $request->input('certificate'),
            $request->input('private_key'),
            $request->input('certificate_chain')
        );
        
        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        
        return back()->withErrors(['ssl' => $result['message']]);
    }

    /**
     * Enable HTTPS redirect
     */
    public function enableHttpsRedirect(Site $site)
    {
        $success = $this->sslService->enableHttpsRedirect($site);
        
        if ($success) {
            return back()->with('success', 'HTTPS redirect enabled successfully');
        }
        
        return back()->withErrors(['ssl' => 'Failed to enable HTTPS redirect']);
    }

    /**
     * Disable HTTPS redirect
     */
    public function disableHttpsRedirect(Site $site)
    {
        $site->update(['force_https' => false]);
        
        return back()->with('success', 'HTTPS redirect disabled successfully');
    }

    /**
     * Renew SSL certificate
     */
    public function renewCertificate(SSLCertificate $certificate)
    {
        $result = $this->sslService->renewCertificate($certificate);
        
        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        
        return back()->withErrors(['ssl' => $result['message']]);
    }

    /**
     * Delete SSL certificate
     */
    public function deleteCertificate(SSLCertificate $certificate)
    {
        $site = $certificate->site;
        
        $result = $this->sslService->disableSSL($site);
        
        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        
        return back()->withErrors(['ssl' => $result['message']]);
    }

    /**
     * Get certificate information (API)
     */
    public function getCertificateInfo(SSLCertificate $certificate): JsonResponse
    {
        $info = $this->sslService->getCertificateInfo($certificate);
        
        return response()->json($info);
    }

    /**
     * Check all expiring certificates
     */
    public function checkExpiring(): JsonResponse
    {
        $this->sslService->checkExpiringCertificates();
        
        return response()->json(['message' => 'Certificate expiration check completed']);
    }
}