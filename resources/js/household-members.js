import { recognizeKkImage, parseKartuKeluarga } from './kk-ocr';
import { showConfirm, showAlert } from './dialog';

export function setupHouseholdMembers() {
    const ocrContainer = document.getElementById('ocr-review-container');
    if (!ocrContainer) return;

    const btnOpenOcr = document.getElementById('btn-open-ocr');
    const btnCloseOcr = document.getElementById('btn-close-ocr');
    const fileInput = document.getElementById('kk-file-input');
    const hiddenFileInput = document.getElementById('hidden-kk-file');
    const progressContainer = document.getElementById('ocr-progress-container');
    const statusText = document.getElementById('ocr-status-text');
    const percentageText = document.getElementById('ocr-percentage');
    const progressBar = document.getElementById('ocr-progress-bar');
    const formSync = document.getElementById('form-sync-members');
    const reviewTableBody = document.getElementById('review-table-body');
    const btnAddReviewRow = document.getElementById('btn-add-review-row');
    const btnCancelReview = document.getElementById('btn-cancel-review');
    const reviewKkNumber = document.getElementById('review_kk_number');
    const reviewHeadName = document.getElementById('review_head_name');
    const reviewImgPreview = document.getElementById('review-image-preview');
    const reviewImgPlaceholder = document.getElementById('review-image-placeholder');

    // Zoom & Rotate controls in side preview
    const btnZoomIn = document.getElementById('btn-zoom-in');
    const btnZoomOut = document.getElementById('btn-zoom-out');
    const btnZoomReset = document.getElementById('btn-zoom-reset');
    const btnRotateLeft = document.getElementById('btn-rotate-left');
    const btnRotateRight = document.getElementById('btn-rotate-right');
    const btnRescanRotated = document.getElementById('btn-rescan-rotated');
    let currentZoom = 1;
    let currentFile = null;
    let isRotating = false;

    // Pick Confirmation Modal Elements
    const modalConfirmPick = document.getElementById('modal-confirm-pick');
    const pickModalPreviewImg = document.getElementById('pick-modal-preview-img');
    const pickRotationBadge = document.getElementById('pick-rotation-badge');
    const btnModalRotateLeft = document.getElementById('btn-modal-rotate-left');
    const btnModalRotateRight = document.getElementById('btn-modal-rotate-right');
    const btnCancelPickModal = document.getElementById('btn-cancel-pick-modal');
    const btnCancelPickModalX = document.getElementById('btn-cancel-pick-modal-x');
    const btnConfirmStartOcr = document.getElementById('btn-confirm-start-ocr');

    let pendingFile = null;
    let pendingRotation = 0;

    function applyZoom(zoom) {
        currentZoom = Math.max(0.5, Math.min(3, zoom));
        if (reviewImgPreview) {
            reviewImgPreview.style.transform = `scale(${currentZoom})`;
            if (btnZoomReset) btnZoomReset.textContent = `${Math.round(currentZoom * 100)}%`;
        }
    }

    if (btnZoomIn) btnZoomIn.addEventListener('click', () => applyZoom(currentZoom + 0.25));
    if (btnZoomOut) btnZoomOut.addEventListener('click', () => applyZoom(currentZoom - 0.25));
    if (btnZoomReset) btnZoomReset.addEventListener('click', () => applyZoom(1));

    // Helper: Canvas rotate image file
    async function rotateFileByDegrees(file, deg) {
        const normalizedDeg = ((deg % 360) + 360) % 360;
        if (normalizedDeg === 0) return file;

        const img = new Image();
        const objectUrl = URL.createObjectURL(file);
        await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = reject;
            img.src = objectUrl;
        });

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        const isQuarterTurn = normalizedDeg === 90 || normalizedDeg === 270;
        canvas.width = isQuarterTurn ? img.height : img.width;
        canvas.height = isQuarterTurn ? img.width : img.height;

        ctx.translate(canvas.width / 2, canvas.height / 2);
        ctx.rotate((normalizedDeg * Math.PI) / 180);
        ctx.drawImage(img, -img.width / 2, -img.height / 2);

        URL.revokeObjectURL(objectUrl);

        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.95));
        return new File([blob], file.name || 'kartu-keluarga.jpg', { type: 'image/jpeg' });
    }

    async function rotateCurrentFile(deg) {
        if (!currentFile || isRotating) return;
        isRotating = true;

        try {
            const rotatedFile = await rotateFileByDegrees(currentFile, deg);
            currentFile = rotatedFile;

            if (reviewImgPreview) {
                reviewImgPreview.src = URL.createObjectURL(rotatedFile);
                reviewImgPreview.classList.remove('hidden');
            }
            if (hiddenFileInput) {
                const dt = new DataTransfer();
                dt.items.add(rotatedFile);
                hiddenFileInput.files = dt.files;
            }

            if (btnRescanRotated) {
                btnRescanRotated.classList.remove('hidden');
            }
        } catch (err) {
            console.error('Gagal merotasi gambar:', err);
        } finally {
            isRotating = false;
        }
    }

    if (btnRotateLeft) btnRotateLeft.addEventListener('click', () => rotateCurrentFile(-90));
    if (btnRotateRight) btnRotateRight.addEventListener('click', () => rotateCurrentFile(90));
    if (btnRescanRotated) {
        btnRescanRotated.addEventListener('click', () => {
            if (currentFile) {
                handleFile(currentFile);
            }
        });
    }

    // Modal Pick Confirmation Workflow
    function openPickConfirmationModal(file) {
        pendingFile = file;
        pendingRotation = 0;
        updatePickModalDisplay();
        if (modalConfirmPick) {
            modalConfirmPick.classList.remove('hidden');
        }
    }

    function closePickConfirmationModal() {
        if (modalConfirmPick) modalConfirmPick.classList.add('hidden');
        pendingFile = null;
        pendingRotation = 0;
        if (fileInput) fileInput.value = '';
    }

    function updatePickModalDisplay() {
        if (!pendingFile || !pickModalPreviewImg) return;
        pickModalPreviewImg.src = URL.createObjectURL(pendingFile);
        pickModalPreviewImg.style.transform = `rotate(${pendingRotation}deg)`;

        if (pickRotationBadge) {
            const labels = {
                0: '0° (Tegak Normal)',
                90: '90° (Miring Kanan)',
                180: '180° (Terbalik)',
                270: '270° (Miring Kiri)',
            };
            pickRotationBadge.textContent = labels[pendingRotation] || `${pendingRotation}°`;
        }
    }

    if (btnModalRotateLeft) {
        btnModalRotateLeft.addEventListener('click', () => {
            pendingRotation = (pendingRotation - 90 + 360) % 360;
            updatePickModalDisplay();
        });
    }

    if (btnModalRotateRight) {
        btnModalRotateRight.addEventListener('click', () => {
            pendingRotation = (pendingRotation + 90) % 360;
            updatePickModalDisplay();
        });
    }

    if (btnCancelPickModal) btnCancelPickModal.addEventListener('click', closePickConfirmationModal);
    if (btnCancelPickModalX) btnCancelPickModalX.addEventListener('click', closePickConfirmationModal);

    if (btnConfirmStartOcr) {
        btnConfirmStartOcr.addEventListener('click', async () => {
            if (!pendingFile) return;

            let fileToScan = pendingFile;
            if (pendingRotation !== 0) {
                btnConfirmStartOcr.disabled = true;
                btnConfirmStartOcr.innerHTML = `
                    <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Menyesuaikan Orientasi Foto...</span>
                `;
                try {
                    fileToScan = await rotateFileByDegrees(pendingFile, pendingRotation);
                } catch (e) {
                    console.error('Gagal rotate file:', e);
                }
                btnConfirmStartOcr.disabled = false;
                btnConfirmStartOcr.innerHTML = `
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h3"/><path d="M20 7V4h-3"/><path d="M4 17v3h3"/><path d="M20 17v3h-3"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                    <span>Mulai Scan Dokumen Ini (Teks Sudah Tegak)</span>
                `;
            }

            if (modalConfirmPick) modalConfirmPick.classList.add('hidden');
            handleFile(fileToScan);
        });
    }

    // Open & Close Scan Section
    if (btnOpenOcr) {
        btnOpenOcr.addEventListener('click', () => {
            ocrContainer.classList.remove('hidden');
            ocrContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    if (btnCloseOcr) {
        btnCloseOcr.addEventListener('click', () => {
            ocrContainer.classList.add('hidden');
        });
    }

    if (btnCancelReview) {
        btnCancelReview.addEventListener('click', () => {
            formSync.classList.add('hidden');
            progressContainer.classList.add('hidden');
            if (fileInput) fileInput.value = '';
        });
    }

    // Paste Raw Text Mode
    const btnPasteRaw = document.getElementById('btn-paste-raw');
    const pasteContainer = document.getElementById('paste-text-container');
    const btnCancelPaste = document.getElementById('btn-cancel-paste');
    const btnProcessRaw = document.getElementById('btn-process-raw');
    const rawOcrText = document.getElementById('raw-ocr-text');

    // Review Raw Text Panel Elements
    const reviewRawTextPanel = document.getElementById('review-raw-text-panel');
    const btnToggleRawText = document.getElementById('btn-toggle-raw-text');
    const reviewRawOcrTextarea = document.getElementById('review-raw-ocr-textarea');
    const btnCloseRawPanel = document.getElementById('btn-close-raw-panel');
    const btnReextractRaw = document.getElementById('btn-reextract-raw');

    if (btnToggleRawText && reviewRawTextPanel) {
        btnToggleRawText.addEventListener('click', () => {
            reviewRawTextPanel.classList.toggle('hidden');
            if (!reviewRawTextPanel.classList.contains('hidden')) {
                reviewRawOcrTextarea?.focus();
            }
        });
    }

    if (btnCloseRawPanel && reviewRawTextPanel) {
        btnCloseRawPanel.addEventListener('click', () => {
            reviewRawTextPanel.classList.add('hidden');
        });
    }

    if (btnReextractRaw) {
        btnReextractRaw.addEventListener('click', () => {
            const text = reviewRawOcrTextarea?.value.trim() || '';
            if (!text) return;
            const parsed = parseKartuKeluarga(text);
            populateReviewForm(parsed);
            reviewRawTextPanel?.classList.add('hidden');
        });
    }

    if (btnPasteRaw) {
        btnPasteRaw.addEventListener('click', (e) => {
            e.stopPropagation();
            pasteContainer.classList.toggle('hidden');
            if (!pasteContainer.classList.contains('hidden')) {
                rawOcrText?.focus();
            }
        });
    }

    if (btnCancelPaste) {
        btnCancelPaste.addEventListener('click', () => {
            pasteContainer.classList.add('hidden');
        });
    }

    if (btnProcessRaw) {
        btnProcessRaw.addEventListener('click', () => {
            const text = rawOcrText.value.trim();
            if (!text) return;
            const parsed = parseKartuKeluarga(text);
            populateReviewForm(parsed);
            pasteContainer.classList.add('hidden');
        });
    }

    // Handle File Drop / Selection & OCR
    async function handleFile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        currentFile = file;
        if (btnRescanRotated) btnRescanRotated.classList.add('hidden');

        // Preview Image in side panel
        const objectUrl = URL.createObjectURL(file);
        if (reviewImgPreview) {
            reviewImgPreview.src = objectUrl;
            reviewImgPreview.classList.remove('hidden');
        }
        if (reviewImgPlaceholder) reviewImgPlaceholder.classList.add('hidden');
        applyZoom(1);

        // Sync to hidden input so it gets submitted on form save
        if (hiddenFileInput) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            hiddenFileInput.files = dataTransfer.files;
        }

        // Show progress bar
        progressContainer.classList.remove('hidden');
        progressBar.style.width = '10%';
        percentageText.textContent = '10%';
        statusText.innerHTML = `
            <svg class="size-4 animate-spin text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyiapkan engine OCR lokal di browser & pra-pemrosesan gambar...
        `;

        try {
            const rawText = await recognizeKkImage(file, (progress) => {
                const pct = Math.max(10, Math.min(95, progress));
                progressBar.style.width = `${pct}%`;
                percentageText.textContent = `${pct}%`;
                statusText.innerHTML = `
                    <svg class="size-4 animate-spin text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Membaca tulisan dokumen KK (${progress}%)...
                `;
            });

            progressBar.style.width = '100%';
            percentageText.textContent = '100%';
            statusText.textContent = 'Selesai membaca! Menyusun data anggota keluarga...';

            if (rawOcrText) rawOcrText.value = rawText;
            if (reviewRawOcrTextarea) reviewRawOcrTextarea.value = rawText;

            const parsed = parseKartuKeluarga(rawText);
            populateReviewForm(parsed);
        } catch (err) {
            console.error('OCR Error:', err);
            statusText.textContent = 'Gagal memproses gambar otomatis. Silakan isi form review manual.';
            // Still populate with blank row so admin can type
            populateReviewForm({
                kk_number: '',
                head_name: '',
                members: [{ name: '', family_relation: 'Kepala Keluarga' }],
            });
        }
    }

    if (fileInput) {
        fileInput.addEventListener('change', () => {
            if (fileInput.files?.[0]) {
                openPickConfirmationModal(fileInput.files[0]);
            }
        });
    }

    // Populate Review Table
    function populateReviewForm(data) {
        if (reviewKkNumber) reviewKkNumber.value = data.kk_number || window.EXISTING_KK_NUMBER || '';
        if (reviewHeadName) reviewHeadName.value = data.head_name || window.EXISTING_HEAD_NAME || '';

        const reviewOccupancyStatus = document.getElementById('review_occupancy_status');
        if (reviewOccupancyStatus) {
            reviewOccupancyStatus.value = data.occupancy_status || window.EXISTING_OCCUPANCY_STATUS || 'pemilik';
        }

        reviewTableBody.innerHTML = '';
        const members = data.members && data.members.length ? data.members : [{ name: '', family_relation: 'Kepala Keluarga' }];

        members.forEach((m, idx) => {
            appendReviewRow(m, idx);
        });

        formSync.classList.remove('hidden');
        progressContainer.classList.add('hidden');
        formSync.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function appendReviewRow(member = {}, index = null) {
        const idx = index !== null ? index : reviewTableBody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 transition-colors';

        const relations = [
            'Kepala Keluarga', 'Suami', 'Istri', 'Anak', 'Orang Tua',
            'Mertua', 'Menantu', 'Cucu', 'Famili Lain', 'Lainnya'
        ];
        const religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'];
        const maritalStatuses = ['Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati'];

        const defaultOcc = document.getElementById('review_occupancy_status')?.value || window.EXISTING_OCCUPANCY_STATUS || 'pemilik';
        const occStatus = member.occupancy_status || defaultOcc;

        const relationOpts = relations.map(r => `<option value="${r}" ${r === (member.family_relation || 'Anggota') ? 'selected' : ''}>${r}</option>`).join('');
        const religionOpts = religions.map(r => `<option value="${r}" ${r === (member.religion || 'Islam') ? 'selected' : ''}>${r}</option>`).join('');
        const maritalOpts = maritalStatuses.map(s => `<option value="${s}" ${s === (member.marital_status || 'Kawin') ? 'selected' : ''}>${s}</option>`).join('');

        tr.innerHTML = `
            <td class="p-2 text-center font-bold text-slate-700 row-number">${idx + 1}</td>
            <td class="p-2">
                <input type="text" name="members[${idx}][nik]" value="${member.nik || ''}" maxlength="20" class="input font-mono text-xs py-1 px-2 text-slate-900 font-semibold" placeholder="16 digit">
            </td>
            <td class="p-2">
                <input type="text" name="members[${idx}][name]" value="${member.name || ''}" required maxlength="100" class="input font-bold text-xs py-1 px-2 text-slate-900" placeholder="Nama Lengkap">
            </td>
            <td class="p-2">
                <select name="members[${idx}][family_relation]" class="input text-xs py-1 px-2 font-semibold text-slate-900">${relationOpts}</select>
            </td>
            <td class="p-2">
                <select name="members[${idx}][occupancy_status]" class="input text-xs py-1 px-1.5 font-bold text-slate-900">
                    <option value="pemilik" ${occStatus === 'pemilik' ? 'selected' : ''}>Pemilik</option>
                    <option value="kontrak" ${occStatus === 'kontrak' ? 'selected' : ''}>Kontrak</option>
                </select>
            </td>
            <td class="p-2">
                <select name="members[${idx}][gender]" class="input text-xs py-1 px-2 font-semibold text-slate-900">
                    <option value="L" ${member.gender === 'L' ? 'selected' : ''}>L</option>
                    <option value="P" ${member.gender === 'P' ? 'selected' : ''}>P</option>
                </select>
            </td>
            <td class="p-2">
                <input type="text" name="members[${idx}][birth_place]" value="${member.birth_place || ''}" class="input text-xs py-1 px-2 font-medium text-slate-900" placeholder="Kota Lahir">
            </td>
            <td class="p-2">
                <input type="date" name="members[${idx}][birth_date]" value="${member.birth_date || ''}" class="input text-xs py-1 px-1.5 font-semibold text-slate-900">
            </td>
            <td class="p-2">
                <select name="members[${idx}][religion]" class="input text-xs py-1 px-2 font-semibold text-slate-900">${religionOpts}</select>
            </td>
            <td class="p-2">
                <input type="text" name="members[${idx}][job]" value="${member.job || ''}" class="input text-xs py-1 px-2 font-medium text-slate-900" placeholder="Pekerjaan">
            </td>
            <td class="p-2">
                <select name="members[${idx}][marital_status]" class="input text-xs py-1 px-2 font-semibold text-slate-900">${maritalOpts}</select>
            </td>
            <td class="p-2">
                <input type="tel" name="members[${idx}][phone]" value="${member.phone || ''}" class="input text-xs py-1 px-2 font-medium text-slate-900" placeholder="08...">
            </td>
            <td class="p-2 text-center">
                <button type="button" class="text-rose-600 hover:text-rose-800 p-1 rounded hover:bg-rose-50 btn-remove-row" title="Hapus Baris">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                </button>
            </td>
        `;

        tr.querySelector('.btn-remove-row')?.addEventListener('click', () => {
            tr.remove();
            reindexReviewRows();
        });

        reviewTableBody.appendChild(tr);
    }

    function reindexReviewRows() {
        reviewTableBody.querySelectorAll('tr').forEach((row, idx) => {
            row.querySelector('.row-number').textContent = idx + 1;
            row.querySelectorAll('input, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/members\[\d+\]/, `members[${idx}]`));
                }
            });
        });
    }

    if (btnAddReviewRow) {
        btnAddReviewRow.addEventListener('click', () => {
            appendReviewRow();
        });
    }

    // Form Sync Submit Confirmation (Prevent double submit)
    if (formSync) {
        formSync.addEventListener('submit', async (e) => {
            if (formSync.dataset.confirmed === 'true') {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const submitBtn = formSync.querySelector('button[type="submit"]') || document.getElementById('btn-submit-sync');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

            const rowCount = reviewTableBody.querySelectorAll('tr').length;
            if (rowCount === 0) {
                await showAlert({
                    title: 'Tabel Anggota Masih Kosong',
                    message: 'Tabel anggota keluarga masih kosong. Silakan masukkan minimal 1 data anggota keluarga sebelum menyimpan.',
                    type: 'warning',
                });
                return;
            }

            const householdNo = window.HOUSEHOLD_NUMBER || '';
            const confirmMsg = `Apakah Anda yakin seluruh data (${rowCount} anggota keluarga) sudah benar dan ingin disimpan ke database untuk Rumah ${householdNo}?`;

            const confirmed = await showConfirm({
                title: 'Simpan Data Penghuni',
                message: confirmMsg,
                confirmText: 'Ya, Simpan ke Database',
                cancelText: 'Periksa Lagi',
                type: 'primary',
            });

            if (confirmed) {
                formSync.dataset.confirmed = 'true';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Menyimpan ke Database...</span>
                    `;
                }
                formSync.submit();
            } else {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (originalBtnHtml) {
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                }
            }
        });
    }

    // Edit Mode Tabel Lengkap (load existing members into review table)
    const btnEditAllTable = document.getElementById('btn-edit-all-table');
    if (btnEditAllTable) {
        btnEditAllTable.addEventListener('click', () => {
            ocrContainer.classList.remove('hidden');
            populateReviewForm({
                kk_number: window.EXISTING_KK_NUMBER,
                head_name: window.EXISTING_HEAD_NAME,
                members: window.EXISTING_MEMBERS,
            });
            ocrContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    // Modal Single Member (Manual Add / Edit)
    const singleModal = document.getElementById('single-member-modal');
    const singleForm = document.getElementById('single-member-form');
    const modalTitle = document.getElementById('modal-title');
    const formMethodField = document.getElementById('form-method-field');
    const btnOpenManual = document.getElementById('btn-open-manual');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const btnCancelModal = document.getElementById('btn-cancel-modal');

    function openSingleModal(title, actionUrl, isEdit = false, memberData = null) {
        if (!singleModal || !singleForm) return;
        modalTitle.textContent = title;
        singleForm.action = actionUrl;

        if (isEdit) {
            formMethodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        } else {
            formMethodField.innerHTML = '';
            singleForm.reset();
        }

        if (memberData) {
            document.getElementById('m_nik').value = memberData.nik || '';
            document.getElementById('m_name').value = memberData.name || '';
            document.getElementById('m_relation').value = memberData.family_relation || 'Kepala Keluarga';
            document.getElementById('m_occupancy_status').value = memberData.occupancy_status || window.EXISTING_OCCUPANCY_STATUS || 'pemilik';
            document.getElementById('m_gender').value = memberData.gender || 'L';
            document.getElementById('m_birth_place').value = memberData.birth_place || '';
            document.getElementById('m_birth_date').value = memberData.birth_date ? memberData.birth_date.split('T')[0] : '';
            document.getElementById('m_religion').value = memberData.religion || 'Islam';
            document.getElementById('m_marital_status').value = memberData.marital_status || 'Kawin';
            document.getElementById('m_job').value = memberData.job || '';
            document.getElementById('m_phone').value = memberData.phone || '';
        } else {
            const mOcc = document.getElementById('m_occupancy_status');
            if (mOcc) mOcc.value = window.EXISTING_OCCUPANCY_STATUS || 'pemilik';
        }

        singleModal.classList.remove('hidden');
    }

    function closeSingleModal() {
        if (singleModal) singleModal.classList.add('hidden');
    }

    if (btnOpenManual) {
        btnOpenManual.addEventListener('click', () => {
            openSingleModal('Tambah Anggota Keluarga', singleForm.dataset.storeUrl || singleForm.action, false);
        });
    }

    if (btnCloseModal) btnCloseModal.addEventListener('click', closeSingleModal);
    if (btnCancelModal) btnCancelModal.addEventListener('click', closeSingleModal);

    // Edit Single Buttons
    document.querySelectorAll('.btn-edit-single').forEach(btn => {
        btn.addEventListener('click', () => {
            const member = JSON.parse(btn.dataset.member);
            const updateUrl = btn.dataset.updateUrl;
            openSingleModal('Ubah Data Anggota Keluarga', updateUrl, true, member);
        });
    });
}
