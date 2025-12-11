<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Role; 
use App\Events\NewChatMessage; 

class ChatController extends Controller
{
    private function getRoleIdByName(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();
        return $role ? $role->id : null;
    }

    public function index()
    {
        $user = Auth::user();
        
        if (! $user) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập.');
        }

        $userId = $user->id;
        $conversations = collect();
        $chatTargets = collect(); 
        $viewName = '';

        if ($user->role->name === 'admin') {
            
            $venueOwnerRoleId = $this->getRoleIdByName('venue_owner');
            $viewName = 'admin.chats.index'; 
            
            if (!is_null($venueOwnerRoleId)) {
                $chatTargets = User::where('id', '!=', $userId)
                    ->where('role_id', $venueOwnerRoleId)
                    ->get();
                
                $conversations = Conversation::where('user_one_id', $userId)
                    ->orWhere('user_two_id', $userId)
                    ->where(function ($query) use ($userId, $venueOwnerRoleId) {
                        $query->whereHas('userOne', function ($q) use ($userId, $venueOwnerRoleId) {
                            $q->where('id', '!=', $userId)->where('role_id', $venueOwnerRoleId);
                        })->orWhereHas('userTwo', function ($q) use ($userId, $venueOwnerRoleId) {
                            $q->where('id', '!=', $userId)->where('role_id', $venueOwnerRoleId);
                        });
                    })
                    ->with(['userOne', 'userTwo'])
                    ->orderBy('updated_at', 'desc')
                    ->get();
            }

        } elseif ($user->role->name === 'venue_owner') {

            $adminRoleId = $this->getRoleIdByName('admin');
            $viewName = 'venue_owner.chats.index'; 
            
            if (!is_null($adminRoleId)) {
                $chatTargets = User::where('role_id', $adminRoleId)->get(); 

                $conversations = Conversation::where('user_one_id', $userId)
                    ->orWhere('user_two_id', $userId)
                    ->where(function ($query) use ($userId, $adminRoleId) {
                        $query->whereHas('userOne', function ($q) use ($userId, $adminRoleId) {
                            $q->where('id', '!=', $userId)->where('role_id', $adminRoleId);
                        })->orWhereHas('userTwo', function ($q) use ($userId, $adminRoleId) {
                            $q->where('id', '!=', $userId)->where('role_id', $adminRoleId);
                        });
                    })
                    ->with(['userOne', 'userTwo'])
                    ->orderBy('updated_at', 'desc')
                    ->get();
            }

        } else {
            abort(403, 'Bạn không có quyền truy cập trang chat này.');
        }

        return view($viewName, [
            'conversations' => $conversations,
            'venueOwners' => ($user->role->name === 'admin') ? $chatTargets : collect(), 
            'adminUsers' => ($user->role->name === 'venue_owner') ? $chatTargets : collect(), 
        ]);
    }

    public function show(int $otherUserId)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $otherUser = User::find($otherUserId);
        
        if (!$otherUser || $userId == $otherUserId) {
            return back()->with('error', 'Người dùng đối diện không hợp lệ.');
        }

        $validOtherRole = null;
        if ($user->role->name === 'admin') {
            $validOtherRole = 'venue_owner';
        } elseif ($user->role->name === 'venue_owner') {
            $validOtherRole = 'admin';
        } else {
             return back()->with('error', 'Vai trò không được phép truy cập chat.');
        }

        if ($otherUser->role->name !== $validOtherRole) {
            return back()->with('error', 'Bạn không thể bắt đầu cuộc hội thoại với vai trò này.');
        }

        $participants = [$userId, $otherUserId];
        sort($participants); 

        $conversation = Conversation::where('user_one_id', $participants[0])
            ->where('user_two_id', $participants[1])
            ->first();
        
        $messages = collect();
        if ($conversation) {
            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();
        }

        $viewName = ($user->role->name === 'admin') ? 'admin.chats.show' : 'venue_owner.chats.show';

        return view($viewName, compact('conversation', 'messages', 'otherUser'));
    }

    public function sendOrStartChat(Request $request, int $otherUserId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $senderId = Auth::id();
        $user = Auth::user();
        $otherUser = User::find($otherUserId);

        if (!$otherUser || $senderId == $otherUserId) {
            return $request->ajax() 
                ? response()->json(['error' => 'Người dùng đối diện không hợp lệ.'], 400) 
                : back()->with('error', 'Người dùng đối diện không hợp lệ.');
        }

        $type = null;
        if (($user->role->name === 'admin' && $otherUser->role->name === 'venue_owner') || 
            ($user->role->name === 'venue_owner' && $otherUser->role->name === 'admin')) {
            $type = 'admin_to_venue_owner'; 
        } 
        else {
            return $request->ajax() 
                ? response()->json(['error' => 'Không thể bắt đầu cuộc hội thoại với vai trò này.'], 403) 
                : back()->with('error', 'Không thể bắt đầu cuộc hội thoại với vai trò này.');
        }

        $participants = [$senderId, $otherUserId];
        sort($participants); 

        $conversation = Conversation::firstOrCreate(
            [
                'user_one_id' => $participants[0],
                'user_two_id' => $participants[1],
            ],
            [
                'type' => $type,
            ]
        );

        $message = new Message([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId, 
            'message' => $request->input('message'),
        ]);
        $message->save();

        $conversation->touch();
        
        broadcast(new NewChatMessage($message));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message_id' => $message->id,
                'message' => $message->message,
                'created_at' => $message->created_at->format('H:i | d/m'),
            ], 200);
        }

        return redirect()->route(($user->role->name === 'admin' ? 'admin' : 'owner') . '.chats.show', [
            'otherUserId' => $otherUserId
        ])->with('success', 'Tin nhắn đã được gửi.');
    }
}
