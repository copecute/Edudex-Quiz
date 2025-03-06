@extends('layouts.app')

@section('title', 'Tự động phân công')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $data['examPeriod']) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Tự động phân công</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Số ca thi
                            <span class="badge {{ $stats['total_shifts'] == 0 ? 'bg-danger' : 'bg-primary' }}">
                                {{ $stats['total_shifts'] }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Số môn thi
                            <span class="badge {{ $stats['total_subjects'] == 0 ? 'bg-danger' : 'bg-primary' }}">
                                {{ $stats['total_subjects'] }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Số phòng thi
                            <span class="badge {{ $stats['total_rooms'] == 0 ? 'bg-danger' : 'bg-primary' }}">
                                {{ $stats['total_rooms'] }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tổng sức chứa
                            <span class="badge {{ $stats['total_room_capacity'] < $stats['total_students'] ? 'bg-danger' : 'bg-primary' }}">
                                {{ $stats['total_room_capacity'] }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Số CBCT
                            <span class="badge {{ ($stats['total_proctors'] == 0 || $stats['total_proctors'] < $stats['total_rooms']) ? 'bg-danger' : 'bg-primary' }}">
                                {{ $stats['total_proctors'] }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tổng số thí sinh
                            <span class="badge {{ $stats['total_students'] == 0 ? 'bg-danger' : 'bg-primary' }}">
                                {{ $stats['total_students'] }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tự động phân công</h5>
                </div>
                <div class="card-body">
                    @php
                        $canAutoAssign = true;
                        $errors = [];
                        
                        if ($stats['total_shifts'] == 0) {
                            $canAutoAssign = false;
                            $errors[] = 'Chưa có ca thi nào được tạo';
                        }
                        
                        if ($stats['total_subjects'] == 0) {
                            $canAutoAssign = false;
                            $errors[] = 'Chưa có môn thi nào được thêm vào';
                        }
                        
                        if ($stats['total_rooms'] == 0) {
                            $canAutoAssign = false;
                            $errors[] = 'Chưa có phòng thi nào được thêm vào';
                        }
                        
                        if ($stats['total_proctors'] == 0) {
                            $canAutoAssign = false;
                            $errors[] = 'Chưa có cán bộ coi thi nào được phân công';
                        }
                        
                        if ($stats['total_proctors'] < $stats['total_rooms']) {
                            $canAutoAssign = false;
                            $errors[] = sprintf(
                                'Số cán bộ coi thi (%d) không đủ cho số phòng thi (%d) (cần ít nhất 1 CBCT/phòng)', 
                                $stats['total_proctors'], 
                                $stats['total_rooms']
                            );
                        }
                        
                        if ($stats['total_students'] == 0) {
                            $canAutoAssign = false;
                            $errors[] = 'Chưa có thí sinh nào được thêm vào';
                        }
                        
                        if ($stats['total_room_capacity'] < $stats['total_students']) {
                            $canAutoAssign = false;
                            $errors[] = 'Tổng sức chứa phòng thi (' . $stats['total_room_capacity'] . ') không đủ cho số thí sinh (' . $stats['total_students'] . ')';
                        }
                    @endphp

                    @if(!$canAutoAssign)
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Không thể thực hiện phân công tự động</h6>
                            <ul class="mb-0">
                                @foreach($errors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Hệ thống sẽ tự động phân công:
                        <ul class="mb-0">
                            <li>Phân bổ môn thi vào các ca thi</li>
                            <li>Phân bổ phòng thi cho từng môn</li>
                            <li>Phân công CBCT cho các phòng</li>
                            <li>Sắp xếp thí sinh vào phòng thi</li>
                        </ul>
                    </div>

                    <form action="{{ route('exam-periods.assignment.auto.store', $data['examPeriod']) }}" method="POST">
                        @csrf
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" {{ !$canAutoAssign ? 'disabled' : '' }}>
                                <i class="fas fa-magic me-1"></i>
                                Bắt đầu tự động phân công
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 