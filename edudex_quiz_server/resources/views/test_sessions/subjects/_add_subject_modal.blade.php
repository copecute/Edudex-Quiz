<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('test_sessions.subjects.store', $testSession) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm môn thi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($availableSubjects->isEmpty())
                        <div class="alert alert-info mb-0">
                            Tất cả môn học đã được thêm vào kỳ thi này.
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">Chọn môn thi</label>
                            <select name="subjects[]" class="form-select select2-multiple" multiple required>
                                @foreach($availableSubjects as $subject)
                                    <option value="{{ $subject->id }}">
                                        {{ $subject->name }} ({{ $subject->code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Có thể chọn nhiều môn thi cùng lúc
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    @if(!$availableSubjects->isEmpty())
                        <button type="submit" class="btn btn-primary">Thêm môn thi</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div> 