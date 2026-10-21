<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DuesType;
use App\Support\Arrears;
use Illuminate\Http\Request;

class ArrearsController extends Controller
{
    public function index(Request $request, Arrears $arrears)
    {
        $typeId = $request->integer('jenis');
        $rows = $arrears->all();

        if ($typeId) {
            $rows = $rows
                ->map(function (array $row) use ($typeId) {
                    $row['items'] = $row['items']->where('type.id', $typeId)->values();
                    $row['months'] = $row['items']->sum(fn (array $item) => count($item['periods']));
                    $row['total'] = $row['items']->sum('amount');

                    return $row;
                })
                ->filter(fn (array $row) => $row['items']->isNotEmpty())
                ->sortByDesc('total')
                ->values();
        }

        return view('admin.arrears.index', [
            'rows' => $rows,
            'types' => DuesType::active()->orderBy('name')->get(),
            'typeId' => $typeId,
        ]);
    }
}
