<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function index(): View
    {
        // Unpaid summaries per blogwalker
        $unpaidSummaries = User::whereIn('role', ['blogwalker', 'worker'])
            ->whereHas('submissions', function ($q) {
                $q->where('review_status', 'approved')->where('is_paid', false);
            })
            ->withCount(['submissions as unpaid_count' => function ($q) {
                $q->where('review_status', 'approved')->where('is_paid', false);
            }])
            ->withSum(['submissions as unpaid_total' => function ($q) {
                $q->where('review_status', 'approved')->where('is_paid', false);
            }], 'rate_amount')
            ->get();

        // Recent completed payouts
        $payoutHistory = Payout::with(['user', 'processor'])
            ->latest('paid_at')
            ->paginate(15);

        return view('admin.payouts.index', [
            'unpaidSummaries' => $unpaidSummaries,
            'payoutHistory' => $payoutHistory,
        ]);
    }

    public function process(Request $request, User $worker): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $unpaidSubmissions = Submission::where('user_id', $worker->id)
            ->where('review_status', 'approved')
            ->where('is_paid', false)
            ->get();

        if ($unpaidSubmissions->isEmpty()) {
            return back()->with('error', "Worker [{$worker->name}] tidak memiliki komentar yang belum dibayar.");
        }

        DB::transaction(function () use ($worker, $unpaidSubmissions, $validated) {
            $totalAmount = $unpaidSubmissions->sum('rate_amount');
            $count = $unpaidSubmissions->count();

            $payout = Payout::create([
                'user_id' => $worker->id,
                'total_submissions' => $count,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'] ?? 'Transfer Bank',
                'notes' => $validated['notes'],
                'processed_by' => Auth::id(),
                'paid_at' => now(),
            ]);

            Submission::whereIn('id', $unpaidSubmissions->pluck('id'))->update([
                'is_paid' => true,
                'payout_id' => $payout->id,
                'paid_at' => now(),
            ]);
        });

        return back()->with('success', "Pembayaran untuk worker [{$worker->name}] berhasil diproses dan dicatat!");
    }
}
