<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileChangeRequestController extends Controller
{
    /**
     * Display a listing of profile and bank change requests.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');

        $query = ProfileChangeRequest::with(['user', 'reviewer'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        $stats = [
            'pending' => ProfileChangeRequest::where('status', 'pending')->count(),
            'approved' => ProfileChangeRequest::where('status', 'approved')->count(),
            'rejected' => ProfileChangeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.profile_requests.index', compact('requests', 'stats', 'status'));
    }

    /**
     * Approve a profile and bank change request.
     */
    public function approve(Request $request, ProfileChangeRequest $profileRequest): RedirectResponse
    {
        if (! $profileRequest->isPending()) {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $user = $profileRequest->user;

        DB::transaction(function () use ($user, $profileRequest, $request) {
            // Apply requested changes to the user's active profile
            if ($profileRequest->new_name) {
                $user->name = $profileRequest->new_name;
            }
            if ($profileRequest->new_phone) {
                $user->phone = $profileRequest->new_phone;
            }
            if ($profileRequest->new_bank_name) {
                $user->bank_name = $profileRequest->new_bank_name;
            }
            if ($profileRequest->new_bank_account_number) {
                $user->bank_account_number = $profileRequest->new_bank_account_number;
            }
            if ($profileRequest->new_bank_account_name) {
                $user->bank_account_name = $profileRequest->new_bank_account_name;
            }
            if ($profileRequest->bank_book_path) {
                $user->bank_book_path = $profileRequest->bank_book_path;
            }
            $user->save();

            // Mark request as approved
            $profileRequest->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'admin_notes' => $request->input('admin_notes', 'Disetujui oleh Super Admin.'),
            ]);
        });

        return redirect()->route('admin.profile-requests.index')->with('success', "Permohonan perubahan rekening untuk worker [{$user->name}] berhasil DISETUJUI. Data rekening aktif telah diperbarui.");
    }

    /**
     * Reject a profile and bank change request.
     */
    public function reject(Request $request, ProfileChangeRequest $profileRequest): RedirectResponse
    {
        if (! $profileRequest->isPending()) {
            return redirect()->route('admin.profile-requests.index')->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $profileRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes', 'Ditolak oleh Super Admin.'),
        ]);

        return redirect()->route('admin.profile-requests.index')->with('success', "Permohonan perubahan rekening untuk worker [{$profileRequest->user->name}] berhasil DITOLAK. Data rekening lama tetap aktif.");
    }
}
