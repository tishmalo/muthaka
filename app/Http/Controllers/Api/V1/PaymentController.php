<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|size:3',
            'plan' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'phone_number' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        return ApiResponse::success([
            'payment' => $this->payments->initiate($request->user(), $validator->validated()),
        ], 'Payment initialized with fake provider', 201);
    }
}
