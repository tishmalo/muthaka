<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\CoupleServiceInterface;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CoupleInviteRequest;
use App\Http\Requests\Api\V1\DisconnectCoupleRequest;
use Illuminate\Http\Request;
use Throwable;

class CoupleController extends Controller
{
    public function __construct(private readonly CoupleServiceInterface $couples) {}

    public function invite(CoupleInviteRequest $request)
    {
        try {
            return ApiResponse::success(
                $this->couples->createInvite($request->user(), $request->validated('email')),
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
            $couple = $this->couples->acceptInvite($request->user(), $code);

            return ApiResponse::success(['couple' => $couple], 'Invite accepted successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function reject(Request $request, string $code)
    {
        try {
            $this->couples->rejectInvite($request->user(), $code);

            return ApiResponse::success(null, 'Invite rejected successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function cancel(Request $request)
    {
        try {
            $this->couples->cancelInvite($request->user());

            return ApiResponse::success(null, 'Invite canceled successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function disconnect(DisconnectCoupleRequest $request)
    {
        try {
            $this->couples->disconnect($request->user(), $request->validated('reason'));

            return ApiResponse::success(null, 'Disconnected successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function block(Request $request)
    {
        try {
            $this->couples->blockPartner($request->user());

            return ApiResponse::success(null, 'Partner blocked successfully');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function status(Request $request)
    {
        return ApiResponse::success($this->couples->getCoupleStatus($request->user()));
    }

    public function partner(Request $request)
    {
        $partner = $this->couples->getPartner($request->user());

        if (! $partner) {
            return ApiResponse::notFound('No active partner found');
        }

        return ApiResponse::success(['partner' => $partner]);
    }
}
