// Minimal chunk uploader
(function(window){
    async function uploadFileInChunks(file, opts) {
        const chunkSize = opts.chunkSize || 1_500_000; // ~1.5MB
        const total = Math.ceil(file.size / chunkSize);
        const fileId = Date.now().toString(36) + '_' + Math.random().toString(36).slice(2,8);
        let lastResponse = null;
        for (let idx = 0; idx < total; idx++) {
            const start = idx * chunkSize;
            const end = Math.min(file.size, start + chunkSize);
            const chunk = file.slice(start, end);
            const fd = new FormData();
            fd.append('fileId', fileId);
            fd.append('chunkIndex', idx);
            fd.append('totalChunks', total);
            fd.append('filename', file.name);
            if (opts.csrfToken) fd.append('csrf_token', opts.csrfToken);
            fd.append('chunk', chunk, file.name);

            const res = await fetch(opts.url, { method: 'POST', body: fd, credentials: 'same-origin' });
            if (!res.ok) throw new Error('Upload chunk failed: ' + res.status);
            const data = await res.json();
            lastResponse = data;
            if (!data.success) throw new Error('Server error on chunk');
            if (opts.onProgress) opts.onProgress({file, idx, total});
        }
        // lastResponse should include tmp_path
        return { tmp_path: lastResponse.tmp_path, original_name: lastResponse.original_name };
    }

    window.ChunkUploader = { uploadFileInChunks };
})(window);
