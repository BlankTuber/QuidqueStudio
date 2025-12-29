<?php
/**
 * Admin - Media Library
 */
$pageTitle = 'Media';
$breadcrumbs = [['label' => 'Media']];
?>

<div class="page-header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Media Library</h1>
            <p class="page-subtitle"><?= count($media) ?> file<?= count($media) !== 1 ? 's' : '' ?></p>
        </div>
        <button type="button" class="btn btn-primary" id="upload-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17,8 12,3 7,8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Upload
        </button>
    </div>
</div>

<!-- Filter Pills -->
<div class="filter-pills mb-6">
    <a href="/admin/media" class="filter-pill <?= empty($currentType) ? 'active' : '' ?>">All</a>
    <a href="/admin/media?type=image" class="filter-pill <?= $currentType === 'image' ? 'active' : '' ?>">Images</a>
    <a href="/admin/media?type=video" class="filter-pill <?= $currentType === 'video' ? 'active' : '' ?>">Videos</a>
    <a href="/admin/media?type=audio" class="filter-pill <?= $currentType === 'audio' ? 'active' : '' ?>">Audio</a>
</div>

<!-- Upload Zone (hidden by default) -->
<div class="upload-zone-wrapper" id="upload-zone-wrapper" style="display: none;">
    <form method="POST" action="/admin/media" enctype="multipart/form-data" id="upload-form">
        <?= $csrf ?>
        <div class="upload-zone" id="upload-zone">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17,8 12,3 7,8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <p class="upload-zone-text">Drag & drop files here or click to browse</p>
            <p class="upload-zone-hint">Images, videos, and audio files supported</p>
            <input type="file" name="file" id="file-input" accept="image/*,video/*,audio/*" style="display: none;">
        </div>
        <div class="upload-preview" id="upload-preview" style="display: none;">
            <div class="upload-preview-image" id="preview-image"></div>
            <div class="upload-preview-info">
                <p id="preview-name"></p>
                <p id="preview-size" class="text-secondary text-small"></p>
            </div>
            <div class="form-group mt-3">
                <label for="alt_text">Alt Text (for images)</label>
                <input type="text" name="alt_text" id="alt_text" placeholder="Describe the image...">
            </div>
            <div class="flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary">Upload</button>
                <button type="button" class="btn btn-secondary" id="cancel-upload">Cancel</button>
            </div>
        </div>
    </form>
</div>

<?php if (empty($media)): ?>
    <div class="card">
        <div class="empty-state">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21,15 16,10 5,21"/>
            </svg>
            <p class="empty-state-title">No media yet</p>
            <p>Upload your first file to get started.</p>
        </div>
    </div>
<?php else: ?>
    <div class="media-grid-large" id="media-grid">
        <?php foreach ($media as $item): ?>
            <div class="media-card" data-id="<?= $item['id'] ?>">
                <div class="media-card-preview">
                    <?php if ($item['file_type'] === 'image'): ?>
                        <img src="/uploads/<?= htmlspecialchars($item['file_path']) ?>" 
                             alt="<?= htmlspecialchars($item['alt_text'] ?? '') ?>">
                    <?php elseif ($item['file_type'] === 'video'): ?>
                        <div class="media-card-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="5,3 19,12 5,21"/>
                            </svg>
                        </div>
                    <?php elseif ($item['file_type'] === 'audio'): ?>
                        <div class="media-card-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 18V5l12-2v13"/>
                                <circle cx="6" cy="18" r="3"/>
                                <circle cx="18" cy="16" r="3"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="media-card-info">
                    <p class="media-card-name" title="<?= htmlspecialchars(basename($item['file_path'])) ?>">
                        <?= htmlspecialchars(basename($item['file_path'])) ?>
                    </p>
                    <p class="media-card-meta">
                        <?= $this->formatFileSize($item['file_size']) ?>
                        · <?= date('M j, Y', strtotime($item['created_at'])) ?>
                    </p>
                </div>
                <div class="media-card-actions">
                    <button type="button" class="btn btn-ghost btn-sm edit-media-btn" 
                            data-id="<?= $item['id'] ?>"
                            data-alt="<?= htmlspecialchars($item['alt_text'] ?? '') ?>"
                            data-path="<?= htmlspecialchars($item['file_path']) ?>">
                        Edit
                    </button>
                    <form method="POST" action="/admin/media/<?= $item['id'] ?>/delete" class="inline-form">
                        <?= $csrf ?>
                        <button type="submit" class="btn btn-ghost btn-sm"
                                data-confirm="Delete this file? This cannot be undone.">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3,6 5,6 21,6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Edit Modal -->
<div id="edit-media-modal" class="modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Edit Media</h3>
            <button type="button" class="btn btn-ghost btn-icon close-modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="edit-media-form">
            <?= $csrf ?>
            <div class="modal-body">
                <div class="edit-media-preview mb-4">
                    <img src="" id="edit-preview-img" alt="">
                </div>
                <div class="form-group">
                    <label for="edit_alt_text">Alt Text</label>
                    <input type="text" name="alt_text" id="edit_alt_text" placeholder="Describe the image...">
                </div>
                <div class="form-group">
                    <label>File URL</label>
                    <input type="text" id="edit-file-url" readonly class="text-small">
                    <button type="button" class="btn btn-ghost btn-sm mt-2" id="copy-url-btn">Copy URL</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadBtn = document.getElementById('upload-btn');
    const uploadWrapper = document.getElementById('upload-zone-wrapper');
    const uploadZone = document.getElementById('upload-zone');
    const fileInput = document.getElementById('file-input');
    const uploadPreview = document.getElementById('upload-preview');
    const cancelUpload = document.getElementById('cancel-upload');
    
    // Toggle upload zone
    uploadBtn.addEventListener('click', function() {
        uploadWrapper.style.display = uploadWrapper.style.display === 'none' ? 'block' : 'none';
    });
    
    // Click to browse
    uploadZone.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Drag and drop
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });
    
    // File selected
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            showPreview(this.files[0]);
        }
    });
    
    function showPreview(file) {
        document.getElementById('preview-name').textContent = file.name;
        document.getElementById('preview-size').textContent = formatSize(file.size);
        
        const previewImage = document.getElementById('preview-image');
        previewImage.innerHTML = '';
        
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            previewImage.appendChild(img);
        } else {
            previewImage.innerHTML = '<div class="media-card-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg></div>';
        }
        
        uploadZone.style.display = 'none';
        uploadPreview.style.display = 'block';
    }
    
    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }
    
    // Cancel upload
    cancelUpload.addEventListener('click', function() {
        fileInput.value = '';
        uploadZone.style.display = 'block';
        uploadPreview.style.display = 'none';
    });
    
    // Edit modal
    const editModal = document.getElementById('edit-media-modal');
    const editForm = document.getElementById('edit-media-form');
    
    document.querySelectorAll('.edit-media-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const alt = this.dataset.alt;
            const path = this.dataset.path;
            
            editForm.action = '/admin/media/' + id;
            document.getElementById('edit_alt_text').value = alt;
            document.getElementById('edit-preview-img').src = '/uploads/' + path;
            document.getElementById('edit-file-url').value = window.location.origin + '/uploads/' + path;
            
            editModal.style.display = 'flex';
        });
    });
    
    document.querySelectorAll('.close-modal, .modal-backdrop').forEach(el => {
        el.addEventListener('click', function() {
            editModal.style.display = 'none';
        });
    });
    
    // Copy URL
    document.getElementById('copy-url-btn')?.addEventListener('click', function() {
        const urlInput = document.getElementById('edit-file-url');
        urlInput.select();
        document.execCommand('copy');
        this.textContent = 'Copied!';
        setTimeout(() => { this.textContent = 'Copy URL'; }, 2000);
    });
});
</script>