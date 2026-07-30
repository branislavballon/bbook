<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The root URL is an auth gate rather than a page (ADR-0006): nothing in this
 * network is readable without an account, so a page here could only describe
 * the application rather than show it.
 *
 * A controller rather than a closure so `/` survives `route:cache`.
 */
class HomeController extends Controller
{
    /**
     * Send a guest to login and everyone else to their feed.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->route($request->user() ? 'feed' : 'login');
    }
}
