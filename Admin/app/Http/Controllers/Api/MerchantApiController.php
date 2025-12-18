<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerchantProfile;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MerchantApiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Lấy Merchant Profile (Kèm ảnh)
        $merchant = MerchantProfile::with('images')
            ->where('user_id', $user->id)
            ->first();


        $venue = Venue::with(['images', 'courts'])
            ->where('owner_id', $user->id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Lấy dữ liệu thành công',
            'data' => [
                'merchant' => $merchant,
                'venue' => $venue,
            ],
        ]);
    }

    public function updateMerchant(Request $request, $id)
    {
        // 0️⃣ Authorization (Ví dụ)
        // if (auth()->user()->cant('update', MerchantProfile::find($id))) { abort(403); }
        Log::info('Update Merchant Request: ', $request->all());
        // 1️⃣ Validate
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:30',
            'bank_account_name' => 'required|string|max:255',

            // Logic mới: Cho phép giữ lại file cũ hoặc thêm mới
            'user_profiles' => 'nullable|array',
            'user_profiles.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Mảng chứa ID của các ảnh CŨ muốn GIỮ LẠI (nếu frontend hỗ trợ)
            'keep_file_ids' => 'nullable|array',
            'keep_file_ids.*' => 'integer|exists:merchant_user_profiles,id',
        ]);

        $merchant = MerchantProfile::findOrFail($id);

        DB::beginTransaction();

        try {
            // 2️⃣ Update thông tin text
            $merchant->update([
                'business_name' => $validated['business_name'],
                'business_address' => $validated['business_address'],
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_account_name' => $validated['bank_account_name'],
                'status' => 'resubmitted',
            ]);



            $keepIds = $request->input('keep_file_ids', []); // Mảng ID cần giữ

            // Lấy các profile cần xóa
            $filesToDelete = $merchant->images()->whereNotIn('id', $keepIds)->get();

            foreach ($filesToDelete as $profile) {

                $path = str_replace(storage_path('app/public/'), '', public_path($profile->url));
                // Hoặc cách đơn giản dựa trên logic upload cũ:
                $relativePath = str_replace('/storage/', '', parse_url($profile->url, PHP_URL_PATH));

                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
                $profile->delete();
            }

            // B. Upload file mới (Append)
            if ($request->hasFile('user_profiles')) {
                foreach ($request->file('user_profiles') as $file) {
                    $path = $file->store('uploads/merchant_profiles', 'public');

                    $merchant->images()->create([
                        'url' => Storage::url($path),
                    ]);
                }
            }

            DB::commit(); // Lưu tất cả nếu không có lỗi

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thành công',
                'data' => $merchant->load('images'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác nếu có lỗi
            Log::error('Update Merchant Failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.',
                'error' => $e->getMessage() // Chỉ hiện khi debug
            ], 500);
        }
    }
}