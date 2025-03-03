@extends('layouts.app')

@section('title', 'Quản lý đề thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Quản lý đề thi</li>
                </ol>
            </nav>

            @if($subjects->isEmpty())
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Bạn cần thêm ít nhất một môn học trước khi tạo đề thi. 
                    <a href="{{ route('subjects.create') }}" class="alert-link">Thêm môn học mới</a>
                </div>
            </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            Danh sách đề thi
                        </h5>
                        <div>
                            <a href="{{ route('exams.tools') }}" class="btn btn-success me-2 {{ $subjects->isEmpty() ? 'disabled' : '' }}"
                               {{ $subjects->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-file-excel me-2"></i>Nhập/Xuất
                            </a>
                            <a href="{{ route('exams.create') }}" class="btn btn-primary {{ $subjects->isEmpty() ? 'disabled' : '' }}"
                               {{ $subjects->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-plus me-2"></i>Thêm đề thi
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tìm kiếm và lọc -->
                    <form method="GET" action="{{ route('exams.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm kiếm..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="subject_code">
                                    <option value="">Tất cả môn học</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->code }}" 
                                            {{ request('subject_code') == $subject->code ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Tìm
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Danh sách -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên đề thi</th>
                                    <th>Môn học</th>
                                    <th>Thời gian</th>
                                    <th>Số câu hỏi</th>
                                    <th>Tỷ lệ độ khó</th>
                                    <th>Tags</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                <tr>
                                    <td>{{ $exam->name }}</td>
                                    <td>{{ $exam->subject->name }}</td>
                                    <td>{{ $exam->duration }} phút</td>
                                    <td>{{ $exam->total_questions }} câu</td>
                                    <td>
                                        <small>
                                            Dễ: {{ $exam->easy_rate }}% ({{ round($exam->total_questions * $exam->easy_rate / 100) }} câu)<br>
                                            TB: {{ $exam->medium_rate }}% ({{ round($exam->total_questions * $exam->medium_rate / 100) }} câu)<br>
                                            Khó: {{ $exam->hard_rate }}% ({{ round($exam->total_questions * $exam->hard_rate / 100) }} câu)
                                        </small>
                                    </td>
                                    <td>
                                        @foreach($exam->tags as $tag)
                                            <span class="badge bg-info">{{ $tag->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('exams.edit', $exam) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('exams.destroy', $exam) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $exams->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 