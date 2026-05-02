<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ]);

        $alreadyPending = AccessRequest::query()
            ->where('email', strtolower(trim($data['email'])))
            ->where('status', AccessRequest::STATUS_PENDING)
            ->exists();

        if ($alreadyPending) {
            return response()->json([
                'message' => 'A request for this email is already pending admin review.',
            ], 422);
        }

        AccessRequest::create([
            'email' => strtolower(trim($data['email'])),
            'status' => AccessRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Request sent. Admin has been notified.',
        ]);
    }
}

