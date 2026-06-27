<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Couple\CoupleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CoupleController extends Controller
{
    public function __construct(private readonly CoupleService $coupleService)
    {
    }

    public function invite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            return ApiResponse::success(
                $this->coupleService->createInvite($request->user(), $request->phone_number),
                'Invite created successfully',
                201
            );
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function accept(Request $request, string $code)
    {
        try {
            $couple = $this->coupleService->acceptInvite($request->user(), $code);

            return ApiResponse::success(['couple' => $couple], 'Invite accepted successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function reject(Request $request, string $code)
    {
        try {
            $this->coupleService->rejectInvite($request->user(), $code);

            return ApiResponse::success(null, 'Invite rejected successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function cancel(Request $request)
    {
        try {
            $this->coupleService->cancelInvite($request->user());

            return ApiResponse::success(null, 'Invite canceled successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function disconnect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $this->coupleService->disconnect($request->user(), $request->reason);

            return ApiResponse::success(null, 'Disconnected successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function block(Request $request)
    {
        try {
            $this->coupleService->blockPartner($request->user());

            return ApiResponse::success(null, 'Partner blocked successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function status(Request $request)
    {
        return ApiResponse::success($this->coupleService->getCoupleStatus($request->user()));
    }

    public function partner(Request $request)
    {
        $partner = $this->coupleService->getPartner($request->user());

        if (!$partner) {
            return ApiResponse::notFound('No active partner found');
        }

        return ApiResponse::success(['partner' => $partner]);
    }
}
