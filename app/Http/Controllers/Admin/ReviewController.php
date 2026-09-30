<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\User;
use App\Services\TaskTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');
        $workerId = $request->input('worker_id');
        $search = $request->input('search');

        $query = Submission::with(['user', 'domain'])->latest();

        if ($status !== 'all') {
            $query->where('review_status', $status);
        }

        if ($workerId) {
            $query->where('user_id', $workerId);
        }

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->input('task_type'));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('target_url', 'like', "%{$search}%")
                    ->orWhere('published_url', 'like', "%{$search}%")
                    ->orWhere('client_url', 'like', "%{$search}%")
                    ->orWhere('keyword', 'like', "%{$search}%")
                    ->orWhereHas('domain', function ($sub) use ($search) {
                        $sub->where('root_domain', 'like', "%{$search}%");
                    });
            });
        }

        $submissions = $query->paginate(20)->withQueryString();
        $workers = User::whereIn('role', ['blogwalker', 'worker'])->orderBy('name')->get();
        $taskTypes = TaskTypeService::all();

        return view('admin.reviews.index', [
            'submissions' => $submissions,
            'workers' => $workers,
            'taskTypes' => $taskTypes,
            'currentTaskType' => $request->input('task_type', ''),
            'currentStatus' => $status,
            'currentWorkerId' => $workerId,
            'search' => $search,
        ]);
    }

    public function approve(Submission $submission): RedirectResponse
    {
        $submission->update([
            'review_status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        $submission->domain->recalculateCount();

        return back()->with('success', "Submission ID #{$submission->id} berhasil DISETUJUI.");
    }

    public function reject(Request $request, Submission $submission): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $submission->update([
            'review_status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Recalculate domain count so the rejected URL frees up a slot
        $submission->domain->recalculateCount();

        return back()->with('success', "Submission ID #{$submission->id} telah DITOLAK. Slot pada domain [{$submission->domain->root_domain}] telah dibebaskan kembali.");
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'submission_ids' => ['required', 'array'],
            'submission_ids.*' => ['exists:submissions,id'],
        ]);

        $ids = $request->input('submission_ids');
        Submission::whereIn('id', $ids)->update([
            'review_status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', count($ids).' submission berhasil disetujui sekaligus.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $status = $request->input('status', 'approved');
        $workerId = $request->input('worker_id');

        $query = Submission::with(['user', 'domain'])->latest();

        if ($status !== 'all') {
            $query->where('review_status', $status);
        }

        if ($workerId) {
            $query->where('user_id', $workerId);
        }

        $submissions = $query->get();
        $fileName = 'laporan-backlink-'.date('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($submissions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'ID',
                'Tanggal Submit',
                'Nama Blogwalker',
                'Root Domain',
                'Ekstensi TLD',
                'URL Target Komentar',
                'Tipe Komentar',
                'Status Review',
                'Status Bayar',
                'Tarif (Rp)',
                'URL Bukti Screenshot',
            ]);

            foreach ($submissions as $sub) {
                fputcsv($handle, array_map([$this, 'sanitizeCsv'], [
                    $sub->id,
                    $sub->created_at->format('Y-m-d H:i:s'),
                    $sub->user?->name ?? 'Unknown',
                    $sub->domain?->root_domain ?? '-',
                    $sub->domain?->tld ?? '-',
                    $sub->target_url,
                    $sub->comment_type === 'approved_live' ? 'Live Langsung' : 'Awaiting Moderation',
                    strtoupper($sub->review_status),
                    $sub->is_paid ? 'Lunas' : 'Belum Dibayar',
                    $sub->rate_amount,
                    $sub->screenshot_url,
                ]));
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Prevent CSV Formula Injection (CWE-1236).
     */
    protected function sanitizeCsv(mixed $value): string
    {
        $str = (string) $value;
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$str;
        }

        return $str;
    }
}
