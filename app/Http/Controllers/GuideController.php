<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class GuideController extends Controller
{
    /**
     * Display the comprehensive user guide for Super Admin and Blogwalker.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user && $user->isAdmin();

        // Default tab based on role or query parameter
        $defaultTab = $request->query('tab', $isAdmin ? 'admin' : 'worker');
        if (! in_array($defaultTab, ['worker', 'admin', 'faq'], true)) {
            $defaultTab = $isAdmin ? 'admin' : 'worker';
        }

        return view('guide.index', [
            'isAdmin' => $isAdmin,
            'activeTab' => $defaultTab,
        ]);
    }
}
