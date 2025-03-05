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
                            <span class="badge bg-primary">{{ $stats['total_shifts'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Số môn thi
                            <span class="badge bg-primary">{{ $stats['total_subjects'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Số phòng thi
                            <span class="badge bg-primary">{{ $stats['total_rooms'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tổng sức chứa
                            <span class="badge bg-primary">{{ $stats['total_room_capacity'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Số CBCT
                            <span class="badge bg-primary">{{ $stats['total_proctors'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tổng số thí sinh
                            <span class="badge bg-primary">{{ $stats['total_students'] }}</span>
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
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('exam-periods.assignment.auto.store', $data['examPeriod']) }}" method="POST">
                        @csrf
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

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
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