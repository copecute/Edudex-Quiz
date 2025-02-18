@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết bài làm</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Thông tin sinh viên</h5>
                            <div class="d-flex mb-3">
                                @if($submission->student->avatar_url)
                                    <img src="{{ url($submission->student->avatar_url) }}" 
                                         alt="Avatar" style="width: 100px; height: 100px;">
                                @else
                                    <img src="{{ asset('upload/avatar/students/default-avatar.jpg') }}" 
                                         alt="Default Avatar" style="width: 100px; height: 100px;">
                                @endif
                                <div>
                                    <h4 class="mb-1">{{ $submission->student->name }}</h4>
                                    <p class="text-muted mb-0">Mã sinh viên: {{ $submission->student->code }}</p>
                                </div>
                            </div>
                            <table class="table">
                                <tr>
                                    <th style="width: 30%">Giới tính:</th>
                                    <td>{{ $submission->student->gender == 1 ? 'Nam' : 'Nữ' }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày sinh:</th>
                                    <td>{{ $submission->student->birthday?->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $submission->student->email }}</td>
                                </tr>
                                <tr>
                                    <th>Số điện thoại:</th>
                                    <td>{{ $submission->student->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Địa chỉ:</th>
                                    <td>{{ $submission->student->address }}</td>
                                </tr>
                                <tr>
                                    <th>Ngành học:</th>
                                    <td>
                                        @foreach($submission->student->majors as $major)
                                            <div>
                                                {{ $major->name }}
                                                @if($major->pivot->is_main)
                                                    <span class="badge badge-primary">Chính</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Thông tin bài thi</h5>
                            <table class="table">
                                <tr>
                                    <th style="width: 30%">Kỳ thi:</th>
                                    <td>{{ $submission->testSession->name }}</td>
                                </tr>
                                <tr>
                                    <th>Môn thi:</th>
                                    <td>{{ $submission->subject->name }}</td>
                                </tr>
                                <tr>
                                    <th>Đề thi:</th>
                                    <td>{{ $submission->testPaper->name }}</td>
                                </tr>
                                <tr>
                                    <th>Điểm:</th>
                                    <td>
                                        <button class="btn btn-{{ $submission->score >= 5 ? 'success' : 'danger' }} btn-sm">
                                            {{ number_format($submission->score, 2) }}
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Thời gian bắt đầu:</th>
                                    <td>{{ $submission->started_at?->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Thời gian nộp:</th>
                                    <td>{{ $submission->submitted_at?->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Thời gian làm bài:</th>
                                    <td>
                                        @if($submission->started_at && $submission->submitted_at)
                                            @php
                                                $seconds = $submission->started_at->diffInSeconds($submission->submitted_at);
                                            @endphp
                                            @if($seconds < 60)
                                                {{ $seconds }} giây
                                            @else
                                                @php
                                                    $minutes = floor($seconds / 60);
                                                    $remainingSeconds = $seconds % 60;
                                                @endphp
                                                {{ $minutes }} phút {{ $remainingSeconds }} giây
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($submission->submission_file)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>File bài làm</h5>
                            <a href="{{ asset('storage/' . $submission->submission_file) }}" 
                               class="btn btn-primary" 
                               download>
                                Tải file bài làm
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($submission->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Ghi chú</h5>
                            <div class="alert alert-info">
                                {{ $submission->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 