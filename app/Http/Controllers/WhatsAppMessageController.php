<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;

class WhatsAppMessageController extends Controller
{
    public function index()
    {
        $messages = WhatsAppMessage::with('appointment.customer')
            ->latest()
            ->paginate(20);

        return view('whatsapp-messages.index', compact('messages'));
    }

    public function show(WhatsAppMessage $message)
    {
        $message->load('appointment.customer');
        return view('whatsapp-messages.show', compact('message'));
    }
}
