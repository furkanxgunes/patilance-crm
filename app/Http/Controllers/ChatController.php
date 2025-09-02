<?php

namespace App\Http\Controllers;

use App\Models\WaMessageLog;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $threads = WaMessageLog::query()
            ->whereNotNull('wa_id')
            ->select('wa_id')
            ->selectRaw('MAX(created_at) AS last_at')
            ->selectRaw('SUM(CASE WHEN direction="inbound" THEN 1 ELSE 0 END) AS inbound_count')
            ->selectRaw('SUM(CASE WHEN direction="outbound" THEN 1 ELSE 0 END) AS outbound_count')
            ->groupBy('wa_id')
            ->orderByDesc('last_at')
            ->paginate(20);

        return view('chat.index', compact('threads'));
    }

    public function show($wa_id)
    {
        $messages = WaMessageLog::where('wa_id', $wa_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.show', compact('messages', 'wa_id'));
    }
}
