<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriodProctor;
use App\Models\ExamPeriod;
use App\Models\Account;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProctorsExport;
use App\Imports\ProctorsImport;
use App\Exports\ProctorsTemplateExport;
use Illuminate\Support\Facades\DB;

class ExamPeriodProctorController extends Controller
{
    public function index(ExamPeriod $examPeriod)
    {
        $proctors = $examPeriod->proctors()
            ->join('accounts', 'exam_period_proctors.account_id', '=', 'accounts.id')
            ->join('account_infos', 'accounts.id', '=', 'account_infos.account_id')
            ->select(
                'exam_period_proctors.*',
                'accounts.username',
                'accounts.email',
                'accounts.role',
                'account_infos.fullName',
                'account_infos.phoneNumber'
            )
            ->when(request('search'), function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('accounts.username', 'like', "%{$search}%")
                        ->orWhere('account_infos.fullName', 'like', "%{$search}%")
                        ->orWhere('account_infos.phoneNumber', 'like', "%{$search}%")
                        ->orWhere('accounts.email', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        return view('exam_period_proctors.index', compact('examPeriod', 'proctors'));
    }

    public function assign(ExamPeriod $examPeriod)
    {
        $accounts = Account::join('account_infos', 'accounts.id', '=', 'account_infos.account_id')
            ->select(
                'accounts.*',
                'account_infos.fullName',
                'account_infos.phoneNumber'
            )
            ->with(['examPeriodProctors' => function($q) use ($examPeriod) {
                $q->where('exam_period_id', $examPeriod->id);
            }])
            ->get();

        return view('exam_period_proctors.assign', compact('examPeriod', 'accounts'));
    }

    public function store(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'exists:accounts,id'
        ], [
            'account_ids.*.exists' => 'Một số tài khoản không tồn tại'
        ]);

        try {
            DB::transaction(function() use ($examPeriod, $request) {
                // Xóa tất cả phân công cũ
                $examPeriod->proctors()->delete();
                
                // Thêm phân công mới
                if ($request->has('account_ids')) {
                    foreach ($request->account_ids as $accountId) {
                        $examPeriod->proctors()->create([
                            'account_id' => $accountId
                        ]);
                    }
                }
            });

            return redirect()
                ->route('exam-period-proctors.index', $examPeriod)
                ->with('success', 'Phân công coi thi thành công!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi phân công coi thi!');
        }
    }

    public function destroy(ExamPeriod $examPeriod, ExamPeriodProctor $proctor)
    {
        try {
            $proctor->delete();
            return redirect()
                ->route('exam-period-proctors.index', $examPeriod)
                ->with('success', 'Xóa cán bộ coi thi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa cán bộ coi thi!');
        }
    }

    public function importExportTools(ExamPeriod $examPeriod)
    {
        return view('exam_period_proctors.tools', compact('examPeriod'));
    }

    public function import(Request $request, ExamPeriod $examPeriod)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new ProctorsImport($examPeriod->id), $request->file('file'));
            return back()->with('success', 'Nhập dữ liệu thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function export(ExamPeriod $examPeriod)
    {
        return Excel::download(
            new ProctorsExport($examPeriod->id),
            'danh-sach-can-bo-coi-thi-' . $examPeriod->name . '.xlsx'
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProctorsTemplateExport, 'mau-nhap-can-bo-coi-thi.xlsx');
    }
} 