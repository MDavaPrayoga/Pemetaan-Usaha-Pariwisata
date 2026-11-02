<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsahaPariwisata;
use App\Models\Kecamatan;
use App\Models\Kbli;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Halaman 4: Dashboard Eksekutif & Statistik Mutu Data.
     * Dapat diakses oleh Admin (Operator Dispar) dan Pimpinan (Eksekutif Dispar).
     */
    public function index(): View
    {
        $totalSemua = UsahaPariwisata::count();
        $totalVerified = UsahaPariwisata::verified()->count();
        $totalUnverified = UsahaPariwisata::unverified()->count();

        $totalBuka = UsahaPariwisata::verified()->buka()->count();
        $totalTutupPermanen = UsahaPariwisata::verified()->tutupPermanen()->count();
        $totalTutupSementara = UsahaPariwisata::verified()->tutupSementara()->count();

        return view('admin.dashboard', compact(
            'totalSemua',
            'totalVerified',
            'totalUnverified',
            'totalBuka',
            'totalTutupPermanen',
            'totalTutupSementara'
        ));
    }
}
