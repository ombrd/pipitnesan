<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, $id, $hash)
    {
        $member = Member::find($id);

        if (! $member || ! hash_equals((string) $hash, sha1($member->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if ($member->hasVerifiedEmail()) {
            return view('email-verified', ['message' => 'Email already verified. You can now use the app.']);
        }

        if ($member->markEmailAsVerified()) {
            event(new Verified($member));
        }

        return view('email-verified', ['message' => 'Your email has been successfully verified! You can now use the Pipitnesan App.']);
    }
}
