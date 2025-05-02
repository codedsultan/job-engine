<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SyncCrmUsersJob;

class SyncCrmJobController extends Controller
{
    /**
     * Trigger CRM user sync.
     */
    public function syncCrmUsers(Request $request)
    {
        $adminId = $request->user()?->id ?? 1;

        SyncCrmUsersJob::dispatch($adminId);

        return response()->json([
            'message' => 'CRM user sync has been queued.',
        ]);
    }
}
