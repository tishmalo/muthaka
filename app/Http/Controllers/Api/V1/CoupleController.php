<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CoupleInviteRequest;
use App\Http\Requests\Api\V1\DisconnectCoupleRequest;
use App\Services\Couple\CoupleService;
use Illuminate\Http\Request;
use Throwable;

class CoupleController extends Controller
{
    public function __construct(private readonly CoupleService $coupleService)
    {
    }

    public function invite(CoupleInviteRequest $request)
    {
        try {
            return ApiResponse::success(
                $this->coupleService->createInvite($request->user(), $request->validated('phone_number')),
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

    public function disconnect(DisconnectCoupleRequest $request)
    {
        try {
            $this->coupleService->disconnect($request->user(), $request->validated('reason'));

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

