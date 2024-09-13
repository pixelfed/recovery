<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Instance;
use App\Services\CaptchaService;
use App\Services\RateLimitService;

class ApiController extends Controller
{
    public function lookupToken(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        abort_unless(hash_equals(config('config.token'), $request->token), 422);

        abort_unless(RateLimitService::check($request->ip()) <= config('config.rate_limit.attempts'), 429);

        return [200];
    }

    public function lookupUsername(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|min:3|max:15',
            'token' => 'required',
            'ekey' => 'required|min:5'
        ]);

        abort_unless(hash_equals(config('config.token'), $request->token), 403);
        abort_unless(RateLimitService::check($request->ip()) <= config('config.rate_limit.attempts'), 429);
        abort_unless(CaptchaService::verifyToken($request->ekey), 409, 'Invalid captcha result');

        RateLimitService::increment($request->ip());

        $username = $request->input('username');

        if(str_starts_with($username, '@')) {
            $username = substr($username, 1);
        }

        $profiles = Profile::where('username', 'like', $username . '%')
            ->orWhere('username', 'like', '@' . $username . '%')
            ->take(100)
            ->get()
            ->pluck('username')
            ->map(function($wf) {
                if(!str_contains($wf, '@')) {
                    return [
                        'username' => $wf,
                        'domain' => 'pixelfed.social'
                    ];
                } else {
                    $parsed = substr($wf, 1);
                    $parts = explode('@', $parsed);

                    return [
                        'username' => $parts[0],
                        'domain' => $parts[1]
                    ];
                }
            })
            ->filter(function($item) {
                if(!in_array($item['domain'], ['pixelfed.social', 'pixelfed.art', 'mastodon.social'])) {
                    return Instance::whereDomain($item['domain'])->whereSoftware('pixelfed')->exists();
                }
                return true;
            })
            ->values();

        return $profiles;
    }
}
