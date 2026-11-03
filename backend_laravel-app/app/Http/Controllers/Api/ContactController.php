<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use App\Models\Message;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'website' => ['nullable', 'max:0'],
        ]);

        unset($data['website']);
        $data['subject'] ??= '';

        $message = Message::create($data);

        $to = Profile::first()?->email ?? config('mail.from.address');
        Mail::to($to)->queue(new ContactMessageMail($message));

        return response()->json(['message' => 'Message sent successfully!'], 201);
    }
}
