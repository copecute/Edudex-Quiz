@extends('layouts.app')

@section('title', 'Tỷ lệ hoàn thành')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-reports.index', $examPeriod) }}">Báo cáo kết quả thi</a></li>
                    <li class="breadcrumb-item active">Tỷ lệ hoàn thành</li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Tỷ lệ hoàn thành tổng quan</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="completionChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Phân phối điểm</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="scoreDistributionChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tỷ lệ hoàn thành theo môn thi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Môn thi</th>
                                    <th>Mã môn</th>
                                    <th>Tổng thí sinh</th>
                                    <th>Đã hoàn thành</th>
                                    <th>Tỷ lệ hoàn thành</th>
                                    <th>Điểm trung bình</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjectCompletionRates as $index => $subject)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $subject->subject_name }}</td>
                                    <td>{{ $subject->subject_code }}</td>
                                    <td>{{ $subject->total_students }}</td>
                                    <td>{{ $subject->completed_students }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $subject->completion_rate < 70 ? 'bg-warning' : 'bg-success' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $subject->completion_rate }}%" 
                                                 aria-valuenow="{{ $subject->completion_rate }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ number_format($subject->completion_rate, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($subject->avg_score, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Biểu đồ tỷ lệ hoàn thành tổng quan
    const completionCtx = document.getElementById('completionChart').getContext('2d');
    const completionChart = new Chart(completionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Đã hoàn thành', 'Chưa hoàn thành'],
            datasets: [{
                data: [{{ $overallStats->completed_students }}, {{ $overallStats->total_students - $overallStats->completed_students }}],
                backgroundColor: ['#28a745', '#ffc107'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Biểu đồ phân phối điểm
    const distributionCtx = document.getElementById('scoreDistributionChart').getContext('2d');
    const distributionChart = new Chart(distributionCtx, {
        type: 'bar',
        data: {
            labels: ['0-1', '1-2', '2-3', '3-4', '4-5', '5-6', '6-7', '7-8', '8-9', '9-10'],
            datasets: [{
                label: 'Số lượng thí sinh',
                data: {{ json_encode($scoreDistribution) }},
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Số lượng thí sinh'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Khoảng điểm'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItem) {
                            return `Điểm: ${tooltipItem[0].label}`;
                        },
                        label: function(context) {
                            return `Số lượng: ${context.raw} thí sinh`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush 