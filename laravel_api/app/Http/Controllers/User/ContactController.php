<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string'
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $messageContent = $request->input('message');

        $text = "Bạn nhận được một tin nhắn liên hệ mới từ Website 22.Décembre:\n\n";
        $text .= "Họ tên: " . $name . "\n";
        $text .= "Email: " . $email . "\n";
        $text .= "Nội dung:\n" . $messageContent . "\n";

        try {
            Mail::raw($text, function ($message) use ($email, $name) {
                $message->to('binha10k56@gmail.com')
                        ->subject('Tin nhắn liên hệ mới từ ' . $name);
                $message->replyTo($email, $name);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Tin nhắn của bạn đã được gửi thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi gửi email. Vui lòng thử lại sau.'
            ], 500);
        }
    }
}
