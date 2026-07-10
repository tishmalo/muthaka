<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function current(Request $request)
    {
        return ApiResponse::success([
            'subscription' => $this->payments->currentSubscription($request->user()),
            'is_premium' => $request->user()->isPremium(),
        ]);
    }
}
