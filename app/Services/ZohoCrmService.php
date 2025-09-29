<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ZohoIntegration;
use App\Models\Manager;
use App\Models\Deal;

class ZohoCrmService
{
    private $clientId;
    private $clientSecret;
    private $scope;
    private $redirectUri;
    private $accessToken;
    private $refreshToken;
    private $baseUrl;
    private $expiresIn;

    public function __construct()
    {
        $this->clientId = config('services.zoho.client_id');
        $this->clientSecret = config('services.zoho.client_secret');
        $this->scope = config('services.zoho.scope');
        $this->redirectUri = url('/zoho-integration/callback');
        $this->baseUrl = 'https://www.zohoapis.eu/crm/v6';
        
        $this->loadTokensFromDatabase();
    }

    public function getAccountIdByEmail($accountData)
    {
        if($this->getAccountByEmail($accountData['Email'])) {
            return $this->getAccountByEmail($accountData['Email']);
        }
        
        return $this->createAccount($accountData);
    }

    private function loadTokensFromDatabase()
    {
        $integration = ZohoIntegration::getActive();
        
        if ($integration && !$integration->isTokenExpired()) {
            $this->accessToken = $integration->access_token;
            $this->refreshToken = $integration->refresh_token;
            $this->expiresIn = $integration->expires_in;
        } else {
            $this->refreshAccessToken();
        }
    }   

    public function generateAuthUrl()
    {
        return 'https://accounts.zoho.eu/oauth/v2/auth?response_type=code&client_id=' . $this->clientId . '&access_type=offline&redirect_uri=' . $this->redirectUri . '&scope=' . $this->scope;
    }

    /**
     * Get full token response including refresh token
     */
    public function getTokenData($code = null)
    {
        $response = Http::asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            $this->accessToken = $data['access_token'];
            return $data;
        }

        Log::error('Failed to get token data', ['response' => $response->body()]);
        return null;
    }

    /**
     * Get new access token using refresh token
     */
    public function refreshAccessToken()
    {
        try {
            $response = Http::asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $this->accessToken = $data['access_token'];

                $integration = ZohoIntegration::getActive();
                if ($integration) {
                    $integration->update([
                        'access_token' => $data['access_token'],
                        'updated_at' => now(),
                    ]);
                }

                Log::info('Zoho CRM access token refreshed successfully');
                return true;
            }

            Log::error('Failed to refresh Zoho CRM access token', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception while refreshing Zoho CRM access token', ['error' => $e->getMessage()]);
            return false;
        }
    }

    
    public function createAccount($accountData)
    {
        try {
          
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/Accounts', [
                'data' => [$accountData]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Account created successfully in Zoho CRM', ['account_id' => $result['data'][0]['details']['id']]);
                return $result['data'][0]['details']['id'];
            }

            if ($response->status() === 401) {
                if ($this->refreshAccessToken()) {
                    return $this->createAccount($accountData);
                }
            }

            Log::error('Failed to create account in Zoho CRM', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception while creating account in Zoho CRM', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
        * Create deal in Zoho CRM
     */
    public function createDeal($dealData)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/Deals', [
                'data' => [$dealData]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Deal created successfully in Zoho CRM', ['deal_id' => $result['data'][0]['details']['id']]);
                return $result['data'][0]['details']['id'];
            }

            if ($response->status() === 401) {
                if ($this->refreshAccessToken()) {
                    return $this->createDeal($dealData);
                }
            }

            Log::error('Failed to create deal in Zoho CRM', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception while creating deal in Zoho CRM', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Update account in Zoho CRM
     */
    public function updateAccount($accountId, $accountData)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ])->put($this->baseUrl . '/Accounts/' . $accountId, [
                'data' => [$accountData]
            ]);

            if ($response->successful()) {
                Log::info('Account updated successfully in Zoho CRM', ['account_id' => $accountId]);
                return true;
            }

            if ($response->status() === 401) {
                if ($this->refreshAccessToken()) {
                    return $this->updateAccount($accountId, $accountData);
                }
            }

            Log::error('Failed to update account in Zoho CRM', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception while updating account in Zoho CRM', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get account by email
     */
    public function getAccountByEmail($email)
    {
        $accessToken = $this->accessToken;
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            ])->get($this->baseUrl . '/Accounts/search', [
                'criteria' => "Email:equals:$email"
            ]);

            if ($response->successful()) {
                $result = $response->json();

                if (!empty($result['data'])) {
                    return $result['data'][0]['id'];
                }
            }

            if ($response->status() === 401) {
                if ($this->refreshAccessToken()) {
                    return $this->getAccountByEmail($email);
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while searching account in Zoho CRM', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function SelectManagerForSource(string $source) : Manager
    {
        if ($source === 'Source 1') {
            return Manager::where('email', 'manager4@gmail.com')->first();
        }

        if ($source === 'Source 2') {
            return Manager::whereIn('email', ['manager1@gmail.com','manager2@gmail.com'])
                ->orderBy('deals_count', 'asc')
                ->orderBy('id', 'asc')
                ->first();
        }

        return Manager::orderBy('deals_count', 'asc')->first();
    }
}
