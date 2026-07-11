<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InitiatePaymentRequest;
use App\Services\Payment\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function initiate(InitiatePaymentRequest $request)
    {
        return ApiResponse::success([
            'payment' => $this->payments->initiate($request->user(), $request->validated()),
        ], 'Payment initialized with fake provider', 201);
    }
}

