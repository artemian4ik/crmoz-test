<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Manager;
use App\Services\ZohoCrmService;

class DealController extends Controller
{
    //

    public function index()
    {
        return view('deal.index');
    }

    public function create(Request $request)
    {
        $data = $request->all();

        if(!$data['customer']['first_name'] || !$data['customer']['last_name'] || !$data['customer']['email'] || !$data['deal']['name'] || !$data['deal']['source']) {
            return response()->json(['error' => 'All fields are required'], 400);
        }

        try {
            $customer = Customer::create([
                'first_name' => $data['customer']['first_name'],
                'last_name' => $data['customer']['last_name'],
                'email' => $data['customer']['email'],
            ]);

            $managerService = new ZohoCrmService();
            $manager = $managerService->SelectManagerForSource($data['deal']['source']);

            $deal = Deal::create([
                'name' => $data['deal']['name'],
                'customer_id' => $customer->id,
                'source' => $data['deal']['source']
            ]);

            $manager->incrementDealsCount();

            $accountId = $managerService->getAccountIdByEmail(
                [
                    'Email' => $customer->email,
                    'Account_Name' => $customer['first_name'] . ' ' . $customer['last_name']
                ]);

            $dealId = $managerService->createDeal(
                [
                    'Deal_Name' => $deal->name,
                    'Email' => $customer->email,
                    'Email_Manager' => $manager->email,
                    'Lead_Source' => $deal->source,
                    'Account_Name' => [
                        'id' => $accountId
                    ]
                ]);

            return response()->json([
                'success' => 'Deal created successfully',
                'data' => [
                    'id' => $deal->id,
                    'name' => $deal->name,
                    'customer' => $customer,
                    'manager' => $manager->email,
                    'source' => $deal->source
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create deal: ' . $e->getMessage()], 500);
        }
    }
    
}
