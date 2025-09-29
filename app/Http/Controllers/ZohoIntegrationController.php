<?php

namespace App\Http\Controllers;

use App\Models\ZohoIntegration;
use App\Services\ZohoCrmService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ZohoIntegrationController extends Controller
{
    protected $zohoService;

    public function __construct(ZohoCrmService $zohoService)
    {
        $this->zohoService = $zohoService;
    }

    public function index(): RedirectResponse
    {
        $authUrl = $this->zohoService->generateAuthUrl();
        
        return redirect($authUrl);
    }

    public function callback(Request $request): View
    {
        try {
            if (!$request->has('code')) {
                return view('zoho-integration.error', [
                    'error' => 'Code not received'
                ]);
            }

            $tokenData = $this->zohoService->getTokenData($request->get('code'));

            if (!$tokenData) {
                return view('zoho-integration.error', [
                    'error' => 'Failed to get access tokens'
                ]);
            }

            ZohoIntegration::create([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? '',
                'expires_in' => $tokenData['expires_in'] ?? 3600,
            ]);

            return view('zoho-integration.success', [
                'message' => 'Integration with Zoho CRM successfully configured!'
            ]);

        } catch (\Exception $e) {
            Log::error('Zoho Integration Callback Error: ' . $e->getMessage());
            
            return view('zoho-integration.error', [
                'error' => 'Error configuring integration: ' . $e->getMessage()
            ]);
        }
    }

    public function status(): View
    {
        $integration = ZohoIntegration::getActive();
        
        return view('zoho-integration.status', [
            'integration' => $integration,
            'isActive' => $integration && !$integration->isTokenExpired()
        ]);
    }

    public function refreshTokens(Request $request)
    {
        $this->zohoService->refreshAccessToken();

        return response()->json([
            'success' => 'Tokens refreshed successfully',
        ], 200);
    }
}
