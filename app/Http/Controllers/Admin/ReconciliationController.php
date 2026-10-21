<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Support\BankStatementMatcher;
use App\Support\SubmissionReview;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    public function create()
    {
        return view('admin.reconciliation.index', [
            'pendingCount' => PaymentSubmission::pending()->count(),
            'result' => null,
        ]);
    }

    public function check(Request $request, BankStatementMatcher $matcher)
    {
        $request->validate([
            'statement' => ['nullable', 'required_without:file', 'string', 'max:200000'],
            'file' => ['nullable', 'required_without:statement', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'statement.required_without' => 'Tempel teks mutasi atau unggah file CSV.',
            'file.required_without' => 'Tempel teks mutasi atau unggah file CSV.',
        ]);

        $statement = $request->hasFile('file') ? (string) file_get_contents($request->file('file')->getRealPath()) : (string) $request->input('statement');

        return view('admin.reconciliation.index', [
            'pendingCount' => PaymentSubmission::pending()->count(),
            'result' => $matcher->match($statement),
        ]);
    }

    public function approve(Request $request, SubmissionReview $review)
    {
        $data = $request->validate([
            'approve' => ['required', 'array', 'min:1'],
            'approve.*' => ['integer'],
            'paid_on' => ['array'],
            'paid_on.*' => ['nullable', 'date', 'before_or_equal:today'],
        ], ['approve.required' => 'Centang minimal satu pengajuan yang cocok.']);

        $approved = [];
        $skipped = 0;
        foreach (PaymentSubmission::with('household')->whereIn('id', $data['approve'])->get() as $submission) {
            $paidOn = $data['paid_on'][$submission->id] ?? null;
            $result = $review->approve($submission, $submission->periods, $paidOn ?: now()->toDateString(), null, $request->user());
            if ($result === null) {
                $skipped++;
            } else {
                $approved[] = "#{$submission->code} rumah {$submission->household->number}";
            }
        }

        $message = $approved ? count($approved).' pengajuan dikonfirmasi lunas: '.implode(', ', $approved).'.' : 'Tidak ada pengajuan yang dikonfirmasi.';
        if ($skipped) {
            $message .= " {$skipped} sudah diproses pengurus lain.";
        }

        return redirect()->route('admin.reconciliation.create')->with($approved ? 'status' : 'warning', $message);
    }
}
