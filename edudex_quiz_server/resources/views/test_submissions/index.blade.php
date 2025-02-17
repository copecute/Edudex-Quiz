@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách bài làm</h3>
                </div>
                <div class="card-body">
                    <!-- Form tìm kiếm và lọc -->
                    <form method="GET" action="{{ route('test-submissions.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Mã sinh viên</label>
                                    <input type="text" name="student_code" class="form-control" 
                                           value="{{ request('student_code') }}" placeholder="Nhập mã SV...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tên sinh viên</label>
                                    <input type="text" name="student_name" class="form-control" 
                                           value="{{ request('student_name') }}" placeholder="Nhập tên SV...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Kỳ thi</label>
                                    <select name="test_session_id" class="form-control">
                                        <option value="">Tất cả</option>
                                        @foreach($testSessions as $session)
                                            <option value="{{ $session->id }}" 
                                                {{ request('test_session_id') == $session->id ? 'selected' : '' }}>
                                                {{ $session->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Môn thi</label>
                                    <select name="subject_id" class="form-control">
                                        <option value="">Tất cả</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}"
                                                {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Từ ngày</label>
                                    <input type="date" name="date_from" class="form-control" 
                                           value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Đến ngày</label>
                                    <input type="date" name="date_to" class="form-control" 
                                           value="{{ request('date_to') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                                <a href="{{ route('test-submissions.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i> Làm mới
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Bảng danh sách -->
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Mã SV</th>
                                <th>Tên sinh viên</th>
                                <th>Kỳ thi</th>
                                <th>Môn thi</th>
                                <th>Đề thi</th>
                                <th>Điểm</th>
                                <th>Thời gian nộp</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $submission)
                            <tr>
                                <td>{{ $submission->student->code }}</td>
                                <td>{{ $submission->student->name }}</td>
                                <td>{{ $submission->testSession->name }}</td>
                                <td>{{ $submission->subject->name }}</td>
                                <td>{{ $submission->testPaper->name }}</td>
                                <td>{{ $submission->score }}</td>
                                <td>{{ $submission->submitted_at?->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <a href="{{ route('test-submissions.show', $submission) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Phân trang -->
                    <div class="mt-3">
                        {{ $submissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 