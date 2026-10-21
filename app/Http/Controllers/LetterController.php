<?php

namespace App\Http\Controllers;

use App\Models\Letter;

class LetterController extends Controller
{
    public function verify(string $code)
    {
        $letter = Letter::with('household')->where('code', strtoupper($code))->firstOrFail();
        abort_unless($letter->isApproved(), 404);

        return view('public.letters.verify', compact('letter'));
    }
}
