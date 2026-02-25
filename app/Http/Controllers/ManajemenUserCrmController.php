<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class ManajemenUserCrmController extends Controller
{
    // ========================================================================
    // PAGE VIEW
    // ========================================================================

    public function index()
    {
        return view('manajemen-user.index');
    }

    // ========================================================================
    // API ENDPOINTS
    // ========================================================================

    /**
     * Paginated list with search & sort
     */
    public function apiList(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search  = (string) ($request->get('search') ?? '');
        $sort    = (string) ($request->get('sort') ?? 'created_at');
        $order   = (string) ($request->get('order') ?? 'desc');

        $allowed = ['name', 'email', 'created_at'];
        if (!in_array($sort, $allowed)) $sort = 'created_at';
        if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy($sort, $order)->paginate($perPage);

        $data->getCollection()->transform(function ($item) {
            $item->created_fmt = $item->created_at
                ? Carbon::parse($item->created_at)->format('d M Y H:i')
                : '-';
            $item->photo_url = $item->profile_photo
                ? '/storage/' . $item->profile_photo
                : null;
            return $item;
        });

        return response()->json($data);
    }

    /**
     * Stats for dashboard cards
     */
    public function apiStats()
    {
        $total = User::count();
        $verified = User::whereNotNull('email_verified_at')->count();
        $unverified = $total - $verified;
        $recentMonth = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        return response()->json([
            'total'        => $total,
            'verified'     => $verified,
            'unverified'   => $unverified,
            'recent_month' => $recentMonth,
        ]);
    }

    /**
     * Get single record
     */
    public function apiShow(int $id)
    {
        $item = User::find($id);

        if (!$item) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $item->created_fmt = $item->created_at
            ? Carbon::parse($item->created_at)->format('d M Y H:i')
            : '-';
        $item->photo_url = $item->profile_photo
            ? '/storage/' . $item->profile_photo
            : null;

        return response()->json($item);
    }

    /**
     * Store new user
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
        ], [
            'name.required'     => 'Nama wajib diisi',
            'email.required'    => 'Email wajib diisi',
            'email.email'       => 'Format email tidak valid',
            'email.unique'      => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min'      => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);

        $item = User::create($data);

        return response()->json([
            'message' => 'User berhasil ditambahkan',
            'data'    => $item,
        ], 201);
    }

    /**
     * Update user
     */
    public function apiUpdate(Request $request, int $id)
    {
        $item = User::find($id);

        if (!$item) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ], [
            'name.required'  => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email'    => 'Format email tidak valid',
            'email.unique'   => 'Email sudah terdaftar',
            'password.min'   => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $updateData = $validator->validated();

        // Only update password if provided
        if (!empty($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        } else {
            unset($updateData['password']);
        }

        $item->update($updateData);

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'data'    => $item->fresh(),
        ]);
    }

    /**
     * Delete user
     */
    public function apiDelete(int $id)
    {
        $item = User::find($id);

        if (!$item) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Prevent self-deletion
        if (auth()->check() && auth()->id() === $item->id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}
