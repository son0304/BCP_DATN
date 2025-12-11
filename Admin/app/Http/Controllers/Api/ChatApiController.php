<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // Sử dụng Carbon cho định dạng thời gian

class ChatApiController extends Controller
{
    /**
     * Gửi tin nhắn và tìm hoặc tạo cuộc trò chuyện nếu chưa có.
     * API POST: /api/chats/send/{otherUserId}
     *
     * @param  int  $otherUserId ID của người dùng đối diện (có thể là User hoặc Venue Owner khác)
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, $otherUserId)
    {
        // Kiểm tra người dùng đối diện có tồn tại không
        $otherUser = User::find($otherUserId);
        if (!$otherUser) {
            return response()->json(['success' => false, 'message' => 'Người nhận không tồn tại.'], 404);
        }
        
        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $senderId = Auth::id();
        $receiverId = $otherUserId;

        // Đảm bảo người gửi không gửi tin nhắn cho chính mình
        if ($senderId === $receiverId) {
            return response()->json(['success' => false, 'message' => 'Không thể gửi tin nhắn cho chính mình.'], 400);
        }
        
        try {
            // 2. Tìm hoặc tạo Conversation (Đảm bảo ID nhỏ hơn đứng trước để tránh trùng lặp)
            $conversation = Conversation::firstOrCreate([
                'user1_id' => min($senderId, $receiverId),
                'user2_id' => max($senderId, $receiverId),
            ]);

            // 3. Tạo Message mới
            $message = new Message([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $request->input('message'),
            ]);
            $message->save();

            // 4. Cập nhật last_message_id trong Conversation
            // Dùng update thay vì save để tránh lỗi Race condition tiềm năng nếu có nhiều request cùng lúc
            $conversation->update(['last_message_id' => $message->id]);

            // Định dạng dữ liệu tin nhắn trả về cho frontend
            $responseData = [
                'id' => $message->id,
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'conversation_id' => $conversation->id,
                'created_at' => $message->created_at->toDateTimeString(),
                'formatted_time' => $message->created_at->format('H:i | d/m'),
            ];

            // 5. Trả về phản hồi JSON thành công (Mã 201 Created)
            return response()->json([
                'success' => true,
                'message' => 'Tin nhắn đã được gửi thành công.',
                'data' => $responseData,
            ], 201);
            
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có vấn đề về DB
            return response()->json(['success' => false, 'message' => 'Lỗi server khi gửi tin nhắn.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy lịch sử tin nhắn của một cuộc trò chuyện.
     * API GET: /api/chats/{otherUserId}/messages
     *
     * @param  int  $otherUserId ID của người dùng đối diện
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages($otherUserId)
    {
        $userId = Auth::id();

        // 1. Tìm cuộc trò chuyện
        $conversation = Conversation::where(function ($query) use ($userId, $otherUserId) {
            $query->where('user1_id', $userId)->where('user2_id', $otherUserId);
        })->orWhere(function ($query) use ($userId, $otherUserId) {
            $query->where('user1_id', $otherUserId)->where('user2_id', $userId);
        })->first();

        if (!$conversation) {
            // Nếu chưa có cuộc trò chuyện nào, trả về mảng rỗng
            return response()->json(['success' => true, 'data' => [], 'conversation_id' => null]);
        }
        
        // 2. Lấy tất cả tin nhắn
        $messages = Message::where('conversation_id', $conversation->id)
                            ->orderBy('created_at', 'asc')
                            ->get();

        // 3. Định dạng dữ liệu trả về
        $formattedMessages = $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'conversation_id' => $message->conversation_id,
                'created_at' => $message->created_at->toDateTimeString(),
                'formatted_time' => $message->created_at->format('H:i | d/m'),
            ];
        });

        return response()->json([
            'success' => true, 
            'data' => $formattedMessages, 
            'conversation_id' => $conversation->id
        ]);
    }
}