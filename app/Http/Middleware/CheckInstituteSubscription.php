<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstituteSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->institute_id) {
            $institute = $user->institute;

            if (!$institute) {
                abort(403, 'Institute not found.');
            }

            if (!$institute->isActive() || $institute->isTrialExpired()) {
                return redirect()->route('subscription.expired');
            }
        }

        return $next($request);
    }
}
