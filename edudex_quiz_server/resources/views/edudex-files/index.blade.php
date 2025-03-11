@extends('layouts.app')

@section('title', 'Quản lý file')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quản lý file</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Upload Section -->
                        <div class="col-md-3">
                            <form id="uploadForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Chọn file .edudex</label>
                                    <input type="file" class="form-control" id="edudexFile" accept=".edudex">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <button type="submit" class="btn btn-primary" id="readFileBtn">
                                    <i class="fas fa-file-upload me-2"></i>Đọc file
                                </button>
                            </form>
                        </div>

                        <!-- Content Display -->
                        <div class="col-md-9">
                            <div class="border rounded p-3" style="min-height: 400px; font-family: monospace;">
                                <div id="fileContent" class="text-pre-wrap"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('edudexFile');
    const readBtn = document.getElementById('readFileBtn');
    const contentDiv = document.getElementById('fileContent');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // kiểm tra file đã chọn chưa
        if (!fileInput.files.length) {
            fileInput.classList.add('is-invalid');
            fileInput.nextElementSibling.textContent = 'Vui lòng chọn file';
            return;
        }
        
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        
        try {
            readBtn.disabled = true;
            readBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đọc...';
            
            const response = await fetch('/edudex-files/read', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Lỗi HTTP! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result && result.success) {
                contentDiv.textContent = result.content || '';
                fileInput.classList.remove('is-invalid');
            } else {
                throw new Error(result?.message || 'Có lỗi xảy ra khi đọc file');
            }
        } catch (error) {
            console.error('Error:', error);
            fileInput.classList.add('is-invalid');
            fileInput.nextElementSibling.textContent = error.message || 'Có lỗi xảy ra khi đọc file';
            contentDiv.textContent = '';
        } finally {
            readBtn.disabled = false;
            readBtn.innerHTML = '<i class="fas fa-file-upload me-2"></i>Đọc file';
        }
    });
    
    // reset validation khi chọn file mới
    fileInput.addEventListener('change', function() {
        this.classList.remove('is-invalid');
        contentDiv.textContent = '';
    });
});
</script>
@endpush 