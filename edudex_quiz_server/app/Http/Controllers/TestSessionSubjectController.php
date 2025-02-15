<?php

namespace App\Http\Controllers;

use App\Models\TestSession;
use App\Models\Subject;
use Illuminate\Http\Request;

class TestSessionSubjectController extends Controller
{
    public function index(TestSession $testSession)
    {
        $subjects = $testSession->subjects()
            ->withCount('testShifts')
            ->paginate(10);

        // Lấy danh sách môn học chưa được thêm vào kỳ thi
        $availableSubjects = Subject::whereDoesntHave('testSessions', function($q) use ($testSession) {
            $q->where('test_session_id', $testSession->id);
        })->get();

        return view('test_sessions.subjects.index', compact(
            'testSession', 
            'subjects',
            'availableSubjects'
        ));
    }

    public function store(Request $request, TestSession $testSession)
    {
        $validated = $request->validate([
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id'
        ]);

        $testSession->subjects()->attach($validated['subjects']);

        return redirect()
            ->route('test_sessions.subjects.index', $testSession)
            ->with('success', 'Đã thêm môn thi thành công');
    }

    public function destroy(TestSession $testSession, Subject $subject)
    {
        $testSession->subjects()->detach($subject->id);

        return redirect()
            ->route('test_sessions.subjects.index', $testSession)
            ->with('success', 'Đã xóa môn thi thành công');
    }
} 