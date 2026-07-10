<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Widget\WidgetStateService;
use Illuminate\Http\Request;

class WidgetStateController extends Controller
{
    public function __construct(private readonly WidgetStateService $widgets)
    {
    }

    public function index(Request $request)
    {
        $state = $this->widgets->getForUser($request->user());

        if (!$state) {
            return ApiResponse::forbidden('No active couple found');
        }

        return ApiResponse::success(['widget_state' => $state]);
    }

    public function version(Request $request)
    {
        return ApiResponse::success(['version' => $this->widgets->getLatestVersion($request->user())]);
    }

    public function check(Request $request)
    {
        return response('', 204)->header('X-Widget-Version', (string) $this->widgets->getLatestVersion($request->user()));
    }
}
