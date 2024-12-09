<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    //

    public function index(Request $request) {
        $data['title'] = "Per Campus Per Program Statistics";
        $data['stats'] = Students::whereNotNull('printed_at')
            ->select([
                'id', 'campus', 'program', DB::raw("COUNT(*) as size")
            ])
            ->groupBy(['campus', 'program'])->orderBy('program')->get();
        return view('admin.stats.index', $data);
    }
}
