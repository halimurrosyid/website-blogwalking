<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Period;
use App\Models\Submission;
use App\Models\TargetUrl;
use App\Services\DomainService;
use App\Services\ImageUploadService;
use App\Services\PeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $periods = Period::orderByDesc('starts_at')->get();

        $selectedPeriodId = $request->input('period_id', '');

        $query = Submission::with(['domain', 'period'])->where('user_id', $user->id);

        if ($selectedPeriodId !== '' && $selectedPeriodId !== 'all') {
            $query->where('period_id', $selectedPeriodId);
        }

        if ($request->filled('status')) {
            $query->where('review_status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('target_url', 'like', "%{$search}%")
                    ->orWhereHas('domain', function ($sub) use ($search) {
                        $sub->where('root_domain', 'like', "%{$search}%");
                    });
            });
        }

        // Calculate summary stats for the selected period
        $statsQuery = Submission::where('user_id', $user->id);
        if ($selectedPeriodId !== '' && $selectedPeriodId !== 'all') {
            $statsQuery->where('period_id', $selectedPeriodId);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'approved' => (clone $statsQuery)->where('review_status', 'approved')->count(),
            'pending' => (clone $statsQuery)->where('review_status', 'pending')->count(),
            'rejected' => (clone $statsQuery)->where('review_status', 'rejected')->count(),
            'earned' => (clone $statsQuery)->where('review_status', 'approved')->sum('rate_amount'),
            'paid' => (clone $statsQuery)->where('review_status', 'approved')->where('is_paid', true)->sum('rate_amount'),
        ];

        $submissions = $query->latest()->paginate(15)->withQueryString();

        return view('worker.submissions.index', [
            'submissions' => $submissions,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
            'currentStatus' => $request->input('status', ''),
            'search' => $request->input('search', ''),
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $user = Auth::user();

        $periodService = app(PeriodService::class);
        $activePeriod = $periodService->getActivePeriod();
        $assignment = Assignment::where('user_id', $user->id)
            ->where(function ($q) use ($activePeriod) {
                $q->where('period_id', $activePeriod->id)
                    ->orWhereNull('period_id');
            })
            ->latest()
            ->first();

        $isDisqualified = $assignment ? $assignment->isDisqualified() : false;

        $target = null;
        if ($request->filled('target_id')) {
            $target = TargetUrl::with('domain')->find($request->input('target_id'));
        }

        return view('worker.submissions.create', [
            'user' => $user,
            'assignment' => $assignment,
            'activePeriod' => $activePeriod,
            'isDisqualified' => $isDisqualified,
            'target' => $target,
        ]);
    }

    public function store(
        Request $request,
        DomainService $domainService,
        ImageUploadService $imageUploadService
    ): RedirectResponse {
        $user = Auth::user();

        $request->validate([
            'target_url' => ['required', 'string', 'url'],
            'comment_type' => ['required', 'in:approved_live,pending_moderation'],
            'screenshot_file' => ['nullable', 'image', 'max:5120'], // Max 5MB file upload
            'screenshot_base64' => ['nullable', 'string'], // From Ctrl+V paste
            'target_id' => ['nullable', 'integer', 'exists:target_urls,id'],
        ]);

        // Require at least one form of screenshot
        if (! $request->hasFile('screenshot_file') && empty($request->input('screenshot_base64'))) {
            throw ValidationException::withMessages([
                'screenshot' => 'Mohon sertakan bukti screenshot (bisa paste Ctrl+V atau upload file).',
            ]);
        }

        try {
            // Store screenshot (WebP converted)
            $imageInput = $request->hasFile('screenshot_file')
                ? $request->file('screenshot_file')
                : $request->input('screenshot_base64');

            $screenshotPath = $imageUploadService->storeScreenshot($imageInput);

            // Record submission with domain lock protection
            $submission = $domainService->recordSubmission(
                $user,
                $request->input('target_url'),
                $screenshotPath,
                $request->input('comment_type')
            );

            // If this came from a target task, link and complete it
            if ($request->filled('target_id')) {
                $target = TargetUrl::find($request->input('target_id'));
                if ($target) {
                    $target->markCompleted($submission);
                    $submission->update(['target_url_id' => $target->id]);
                }
            }

            return redirect()
                ->route('blogwalker.submissions.index')
                ->with('success', "Bukti komentar di domain [{$submission->domain->root_domain}] berhasil dikirim! Menunggu verifikasi admin.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat memproses pengiriman: '.$e->getMessage()]);
        }
    }
}
