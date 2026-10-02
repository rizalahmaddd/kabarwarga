import { createWorker } from 'tesseract.js';

const INDO_MONTHS = {
    'JANUARI': '01', 'JAN': '01',
    'FEBRUARI': '02', 'PEBRUARI': '02', 'FEB': '02', 'PEB': '02',
    'MARET': '03', 'MAR': '03',
    'APRIL': '04', 'APR': '04',
    'MEI': '05', 'MAY': '05',
    'JUNI': '06', 'JUN': '06',
    'JULI': '07', 'JUL': '07',
    'AGUSTUS': '08', 'AGU': '08', 'AGS': '08',
    'SEPTEMBER': '09', 'SEP': '09', 'SEPT': '09',
    'OKTOBER': '10', 'OKT': '10', 'OCT': '10',
    'NOVEMBER': '11', 'NOPEMBER': '11', 'NOV': '11', 'NOP': '11',
    'DESEMBER': '12', 'DES': '12', 'DEC': '12',
};

const COMMON_CITIES = [
    'KOTA MALANG', 'BANDAR LAMPUNG', 'JAKARTA', 'BOGOR', 'DEPOK', 'TANGERANG', 'BEKASI', 'BANDUNG', 'SEMARANG',
    'SURABAYA', 'YOGYAKARTA', 'SOLO', 'SURAKARTA', 'MALANG', 'MEDAN', 'PADANG',
    'PALEMBANG', 'LAMPUNG', 'CIREBON', 'SUKABUMI', 'CIANJUR',
    'GARUT', 'TASIKMALAYA', 'SERANG', 'CILEGON', 'PURWAKARTA', 'KARAWANG',
    'SUBANG', 'KUNINGAN', 'MAJALENGKA', 'INDRAMAYU', 'SUMEDANG', 'CIMAHI',
    'TEGAL', 'PEKALONGAN', 'KUDUS', 'MAGELANG', 'PURWOKERTO', 'BANYUMAS',
    'CILACAP', 'BANYUWANGI', 'JEMBER', 'MADIUN', 'KEDIRI', 'PROBOLINGGO',
    'PASURUAN', 'DENPASAR', 'MATARAM', 'KUPANG', 'PONTIANAK', 'BANJARMASIN',
    'BALIKPAPAN', 'SAMARINDA', 'MAKASSAR', 'MANADO', 'PALU', 'KENDARI', 'AMBON', 'JAYAPURA'
];

/**
 * Advanced image preprocessor:
 * 1. Suppresses horizontal and vertical table gridlines using morphological run detection.
 *    Table lines are solid runs of dark pixels that touch 8px-tall text and corrupt Tesseract character segmentation.
 * 2. Upscales low-resolution images to optimal OCR dimensions (width ~2600-3000px).
 * 3. Applies local background illumination normalization and contrast S-curve binarization.
 */
export async function preprocessImageForOcr(imageSource) {
    let img;
    let tempUrl = null;

    if (imageSource instanceof HTMLImageElement) {
        img = imageSource;
    } else {
        img = new Image();
        tempUrl = URL.createObjectURL(imageSource);
        await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = reject;
            img.src = tempUrl;
        });
    }

    const origW = img.naturalWidth || img.width;
    const origH = img.naturalHeight || img.height;

    // Step 1: Base Canvas at original resolution to detect and suppress table gridlines
    const baseCanvas = document.createElement('canvas');
    baseCanvas.width = origW;
    baseCanvas.height = origH;
    const baseCtx = baseCanvas.getContext('2d', { willReadFrequently: true });
    baseCtx.drawImage(img, 0, 0, origW, origH);

    if (tempUrl) {
        URL.revokeObjectURL(tempUrl);
    }

    try {
        const imgData = baseCtx.getImageData(0, 0, origW, origH);
        const data = imgData.data;
        const totalPixels = origW * origH;

        // Grayscale conversion
        const gray = new Uint8Array(totalPixels);
        for (let i = 0, j = 0; i < data.length; i += 4, j++) {
            gray[j] = Math.round(0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]);
        }

        // Morphological table grid line removal:
        // Table horizontal lines are long continuous runs of dark pixels across columns
        const isLinePixel = new Uint8Array(totalPixels);
        const darkThreshold = 135;
        const minHRun = Math.max(35, Math.round(origW * 0.04));
        const minVRun = Math.max(22, Math.round(origH * 0.03));

        // Horizontal line detection
        for (let y = 0; y < origH; y++) {
            let run = 0;
            const rowOffset = y * origW;
            for (let x = 0; x < origW; x++) {
                if (gray[rowOffset + x] < darkThreshold) {
                    run++;
                } else {
                    if (run >= minHRun) {
                        for (let rx = x - run; rx < x; rx++) {
                            isLinePixel[rowOffset + rx] = 1;
                        }
                    }
                    run = 0;
                }
            }
            if (run >= minHRun) {
                for (let rx = origW - run; rx < origW; rx++) {
                    isLinePixel[rowOffset + rx] = 1;
                }
            }
        }

        // Vertical line detection
        for (let x = 0; x < origW; x++) {
            let run = 0;
            for (let y = 0; y < origH; y++) {
                const idx = y * origW + x;
                if (gray[idx] < darkThreshold) {
                    run++;
                } else {
                    if (run >= minVRun) {
                        for (let ry = y - run; ry < y; ry++) {
                            isLinePixel[ry * origW + x] = 1;
                        }
                    }
                    run = 0;
                }
            }
            if (run >= minVRun) {
                for (let ry = origH - run; ry < origH; ry++) {
                    isLinePixel[ry * origW + x] = 1;
                }
            }
        }

        // Erase detected lines by turning them pure white (255)
        for (let j = 0; j < totalPixels; j++) {
            if (isLinePixel[j]) {
                const idx = j * 4;
                data[idx] = 255;
                data[idx + 1] = 255;
                data[idx + 2] = 255;
            }
        }
        baseCtx.putImageData(imgData, 0, 0);

        // Step 2: High-Resolution Upscaling (target width ~2600-3000px)
        const scale = Math.min(3.5, Math.max(1.5, 2800 / origW));
        const upW = Math.round(origW * scale);
        const upH = Math.round(origH * scale);

        const upCanvas = document.createElement('canvas');
        upCanvas.width = upW;
        upCanvas.height = upH;
        const upCtx = upCanvas.getContext('2d', { willReadFrequently: true });
        upCtx.imageSmoothingEnabled = true;
        upCtx.imageSmoothingQuality = 'high';
        upCtx.drawImage(baseCanvas, 0, 0, upW, upH);

        // Step 3: Local illumination normalization and adaptive contrast
        const upImgData = upCtx.getImageData(0, 0, upW, upH);
        const upData = upImgData.data;
        const upTotal = upW * upH;

        const upGray = new Uint8Array(upTotal);
        for (let i = 0, j = 0; i < upData.length; i += 4, j++) {
            upGray[j] = Math.round(0.299 * upData[i] + 0.587 * upData[i + 1] + 0.114 * upData[i + 2]);
        }

        const blockSize = Math.round(40 * scale);
        const gridW = Math.ceil(upW / blockSize);
        const gridH = Math.ceil(upH / blockSize);
        const maxGrid = new Uint8Array(gridW * gridH);

        for (let gy = 0; gy < gridH; gy++) {
            const y0 = gy * blockSize;
            const y1 = Math.min(upH, y0 + blockSize);
            for (let gx = 0; gx < gridW; gx++) {
                const x0 = gx * blockSize;
                const x1 = Math.min(upW, x0 + blockSize);
                let maxLuma = 0;
                for (let y = y0; y < y1; y += 3) {
                    const rowOffset = y * upW;
                    for (let x = x0; x < x1; x += 3) {
                        const val = upGray[rowOffset + x];
                        if (val > maxLuma) maxLuma = val;
                    }
                }
                maxGrid[gy * gridW + gx] = maxLuma || 210;
            }
        }

        for (let y = 0; y < upH; y++) {
            const gy = Math.min(gridH - 1, Math.floor(y / blockSize));
            const rowOffset = y * upW;
            for (let x = 0; x < upW; x++) {
                const gx = Math.min(gridW - 1, Math.floor(x / blockSize));
                const localBg = Math.max(60, maxGrid[gy * gridW + gx]);
                const pixel = upGray[rowOffset + x];

                let normalized = (pixel / localBg) * 255;
                if (normalized > 180) {
                    normalized = 255;
                } else if (normalized < 85) {
                    normalized = 0;
                } else {
                    normalized = ((normalized - 85) / (180 - 85)) * 255;
                }

                const outVal = Math.min(255, Math.max(0, Math.round(normalized)));
                const idx = (rowOffset + x) * 4;
                upData[idx] = outVal;
                upData[idx + 1] = outVal;
                upData[idx + 2] = outVal;
            }
        }

        upCtx.putImageData(upImgData, 0, 0);
        return upCanvas;
    } catch (err) {
        console.warn('Canvas advanced filtering skipped:', err);
        return baseCanvas;
    }
}

let cachedWorker = null;

async function getWorker(onProgress = null) {
    if (!cachedWorker) {
        cachedWorker = await createWorker('ind+eng', 1, {
            logger: (m) => {
                if (onProgress && m.status === 'recognizing text') {
                    onProgress(Math.round((m.progress || 0) * 100));
                }
            },
        });
        await cachedWorker.setParameters({
            preserve_interword_spaces: '1',
            tessedit_pageseg_mode: '3', // Fully automatic page segmentation
        });
    }
    return cachedWorker;
}

export async function recognizeKkImage(imageFile, onProgress = null) {
    let inputForOcr = imageFile;
    try {
        inputForOcr = await preprocessImageForOcr(imageFile);
    } catch (e) {
        console.warn('OCR preprocessing skipped, using original file:', e);
    }

    const worker = await getWorker(onProgress);
    const ret = await worker.recognize(inputForOcr);
    return ret.data.text;
}

export function parseKartuKeluarga(rawText) {
    const rawLines = rawText.split('\n').map(l => l.trim()).filter(Boolean);
    const fullText = rawText.toUpperCase();

    // 1. Ekstraksi Nomor Kartu Keluarga (16 digit)
    let kkNumber = '';
    const kkMatch = fullText.match(/(?:NO\.?|NOMOR|KARTU\s+KELUARGA)[\s:.\-_]*([0-9IlO|SBD\s]{16,22})/i)
        || fullText.match(/\b([0-9IlO|SBD]{16})\b/);
    if (kkMatch) {
        const cleaned = cleanDigits(kkMatch[1]);
        if (cleaned.length >= 16) {
            kkNumber = cleaned.substr(0, 16);
        }
    }

    // 2. Ekstraksi Nama Kepala Keluarga dari Header Dokumen
    // Stop capturing strictly before Desa, Kelurahan, Kecamatan, RT, RW, etc.
    let headName = '';
    const headMatch = rawText.match(/(?:KEPALA\s+KELUARGA|NAMA\s+KEPALA\s+KELUARGA)[\s:.\-_]+([A-Z\s.'`,-]+?)(?=\s*(?:DESA|KELURAHAN|KECAMATAN|KABUPATEN|KOTA|PROVINSI|ALAMAT|RT|RW|KODE\s*POS|[:|\r\n]|$))/i);
    if (headMatch) {
        headName = cleanPersonName(headMatch[1]);
    }

    // 3. Ekstraksi Hubungan Keluarga & Status Perkawinan dari Tabel 2
    // Mapped by row number 1..N to prevent header/footer false positives
    const table2Map = extractTable2Map(rawLines);

    // 4. Ekstraksi Anggota Keluarga dari Tabel 1
    const members = [];
    const foundNiks = new Set();

    rawLines.forEach((line, idx) => {
        const lineUpper = line.toUpperCase();
        // Skip table headers and subheaders
        if (/NAMA\s+LENGKAP|JENIS\s+KELAMIN|TEMPAT\s+LAHIR|TANGGAL\s+LAHIR|AGAMA|PENDIDIKAN/i.test(lineUpper)) return;
        if (/STATUS\s+PERKAWINAN|STATUS\s+HUBUNGAN|DOKUMEN\s+IMIGRASI|NAMA\s+ORANG\s+TUA/i.test(lineUpper)) return;

        // Cari pola NIK (16 digit)
        const nikRegex = /(?:^|[^\d])((?:[0-9IlO|SBD][\s.-]?){15}[0-9IlO|SBD])(?=[^\d]|$)/gi;
        let m;
        while ((m = nikRegex.exec(lineUpper)) !== null) {
            const cleaned = cleanDigits(m[1]);
            if (cleaned.length === 16 && isValidIndonesianNik(cleaned)) {
                // Jangan gunakan nomor KK sebagai NIK jika nomor KK sama dan belum ada anggota
                if (cleaned === kkNumber && foundNiks.size === 0 && !lineUpper.includes('NIK')) {
                    continue;
                }

                if (!foundNiks.has(cleaned)) {
                    foundNiks.add(cleaned);
                    const memberIndex = members.length;
                    const rowNumber = memberIndex + 1;
                    const demo = inferDemographicsFromNik(cleaned);

                    // Ekstraksi nama: ambil bagian sebelum NIK
                    const escapedRaw = m[1].replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const parts = line.split(new RegExp(escapedRaw, 'i'));
                    let name = parts[0] ? cleanPersonName(parts[0]) : '';

                    // Jika nama kosong di baris ini, cek baris sebelumnya
                    if (!name || name.length < 3) {
                        const prevLine = rawLines[idx - 1] || '';
                        if (prevLine && !/NAMA\s+LENGKAP|KARTU\s+KELUARGA|REPUBLIK|INDONESIA/i.test(prevLine)) {
                            const candidate = cleanPersonName(prevLine);
                            if (candidate.length >= 3) name = candidate;
                        }
                    }

                    // Jika masih kosong / anggota pertama, gunakan nama kepala keluarga jika cocok
                    if (!name || name.length < 3) {
                        name = (memberIndex === 0 && headName) ? headName : `Anggota Keluarga ${rowNumber}`;
                    }

                    // Relasi & status perkawinan dari Tabel 2
                    const t2Data = table2Map[rowNumber] || {};
                    let relation = t2Data.relation;
                    if (!relation) {
                        relation = memberIndex === 0 ? 'Kepala Keluarga' : (memberIndex === 1 ? 'Istri' : 'Anak');
                    }
                    let marital = t2Data.marital;
                    if (!marital) {
                        marital = memberIndex <= 1 ? 'Kawin' : 'Belum Kawin';
                    }

                    // Tempat lahir
                    let birthPlace = '';
                    for (const city of COMMON_CITIES) {
                        if (new RegExp(`\\b${city}\\b`, 'i').test(lineUpper)) {
                            birthPlace = city.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
                            break;
                        }
                    }
                    if (!birthPlace && /PROBO[U|N|I|L]NGGO/i.test(lineUpper)) {
                        birthPlace = 'Probolinggo';
                    }

                    // Pekerjaan
                    const job = extractJob(lineUpper);

                    members.push({
                        nik: cleaned,
                        name: name,
                        gender: demo.gender,
                        family_relation: relation,
                        birth_place: birthPlace,
                        birth_date: demo.birthDate,
                        religion: 'Islam',
                        job: job,
                        marital_status: marital,
                        phone: '',
                    });
                }
            }
        }
    });

    // Fallback: Scan full text untuk semua NIK valid jika tidak ada baris yang terdeteksi
    if (members.length === 0) {
        const allNiks = extractAllCandidateNiks(fullText);
        allNiks.forEach((nik, idx) => {
            if (nik !== kkNumber && !foundNiks.has(nik)) {
                foundNiks.add(nik);
                const demographics = inferDemographicsFromNik(nik);
                const rowNumber = idx + 1;
                const t2Data = table2Map[rowNumber] || {};

                members.push({
                    nik: nik,
                    name: idx === 0 && headName ? headName : `Anggota Keluarga ${rowNumber}`,
                    gender: demographics.gender,
                    family_relation: t2Data.relation || (idx === 0 ? 'Kepala Keluarga' : (idx === 1 ? 'Istri' : 'Anak')),
                    birth_place: '',
                    birth_date: demographics.birthDate,
                    religion: 'Islam',
                    job: '',
                    marital_status: t2Data.marital || (idx <= 1 ? 'Kawin' : 'Belum Kawin'),
                    phone: '',
                });
            }
        });
    }

    // Fallback kedua: Jika benar-benar tidak ada NIK yang terdeteksi tapi headName ada
    if (members.length === 0 && headName) {
        members.push({
            nik: '',
            name: headName,
            gender: 'L',
            family_relation: 'Kepala Keluarga',
            birth_place: '',
            birth_date: '',
            religion: 'Islam',
            job: '',
            marital_status: 'Kawin',
            phone: '',
        });
    }

    // Pastikan kepala keluarga pada anggota 0 terpasang nama yang bersih
    if (members.length > 0 && headName) {
        if (!members[0].name || members[0].name.startsWith('Anggota') || members[0].name === 'Nama Warga') {
            members[0].name = headName;
            members[0].family_relation = 'Kepala Keluarga';
        }
    }

    return {
        kk_number: kkNumber,
        head_name: headName,
        members: members,
        raw_text: rawText,
    };
}

function cleanDigits(str) {
    return (str || '')
        .replace(/[Il|!]/g, '1')
        .replace(/[OD]/g, '0')
        .replace(/[S]/g, '5')
        .replace(/[B]/g, '8')
        .replace(/[^0-9]/g, '');
}

function cleanPersonName(name) {
    let s = (name || '')
        .replace(/^[|\s\[\]0-9.:\-_/]+/g, '')
        .replace(/\b(?:NIK|NO|KARTU|KELUARGA|LAKI|PEREMPUAN|ISLAM|KRISTEN|TGL|TEMPAT|LAHIR|AGAMA)\b/gi, '')
        .replace(/[^a-zA-Z\s.'`,-]/g, '')
        .replace(/\s{2,}/g, ' ')
        .trim();

    // Strip leading artifact numbers or table border chars (e.g. 'I9EJE' -> 'JEJE')
    s = s.replace(/^[0-9Il|]{1,2}(?=[A-Z]{3,})/i, '');
    s = s.replace(/^[JIl|]\s+(?=[A-Z]{2,})/i, '');
    // If word starts with single 'J' but isn't a common Indonesian name starting with J
    if (/^J[A-Z]{4,}/.test(s) && !/^(JOKO|JOHAN|JUAN|JONI|JULI|JAYA|JEJE|JOHN|JULIAN|JESSICA|JOHANNES|JAUHAR)/i.test(s)) {
        s = s.substring(1);
    }
    return s.trim();
}

/**
 * Validasi struktur NIK Indonesia (16 digit):
 * - Digit 1-2: Kode Provinsi (11-99)
 * - Digit 7-8: Hari lahir (01-31 untuk Pria, 41-71 untuk Wanita)
 * - Digit 9-10: Bulan lahir (01-12)
 */
function isValidIndonesianNik(nik) {
    if (!nik || nik.length !== 16) return false;
    const prov = parseInt(nik.substr(0, 2), 10);
    if (isNaN(prov) || prov < 11 || prov > 99) return false;

    const day = parseInt(nik.substr(6, 2), 10);
    if (isNaN(day)) return false;
    const isMaleDay = day >= 1 && day <= 31;
    const isFemaleDay = day >= 41 && day <= 71;
    if (!isMaleDay && !isFemaleDay) return false;

    const month = parseInt(nik.substr(8, 2), 10);
    if (isNaN(month) || month < 1 || month > 12) return false;

    return true;
}

/**
 * Ekstraksi Jenis Kelamin & Tanggal Lahir otomatis langsung dari NIK
 * Formula Dukcapil UU No. 23/2006:
 * DD > 40 => Wanita (Hari = DD - 40, Gender = P)
 * DD <= 31 => Pria (Hari = DD, Gender = L)
 */
function inferDemographicsFromNik(nik) {
    if (!isValidIndonesianNik(nik)) {
        return { gender: 'L', birthDate: '' };
    }

    const rawDay = parseInt(nik.substr(6, 2), 10);
    const month = parseInt(nik.substr(8, 2), 10);
    const rawYear = parseInt(nik.substr(10, 2), 10);

    const isFemale = rawDay > 40;
    const realDay = isFemale ? rawDay - 40 : rawDay;

    // Patokan tahun saat ini (2026): jika 2 digit <= 26 maka 2000-an, jika > 26 maka 1900-an
    const fullYear = rawYear <= 26 ? 2000 + rawYear : 1900 + rawYear;

    const dayStr = String(realDay).padStart(2, '0');
    const monthStr = String(month).padStart(2, '0');
    const dateStr = `${fullYear}-${monthStr}-${dayStr}`;

    return {
        gender: isFemale ? 'P' : 'L',
        birthDate: dateStr,
    };
}

function extractAllCandidateNiks(text) {
    const candidates = [];
    const regex = /(?:^|[^\d])((?:[0-9IlO|SBD][\s.-]?){15}[0-9IlO|SBD])(?=[^\d]|$)/gi;
    let m;
    while ((m = regex.exec(text)) !== null) {
        const cleaned = cleanDigits(m[1]);
        if (cleaned.length === 16 && isValidIndonesianNik(cleaned)) {
            if (!candidates.includes(cleaned)) {
                candidates.push(cleaned);
            }
        }
    }
    return candidates;
}

/**
 * Ekstraksi baris-baris Tabel 2 KK (Status Perkawinan & Status Hubungan Dalam Keluarga)
 * Memetakan setiap nomor baris (1, 2, 3...) ke relasi & status perkawinannya.
 */
function extractTable2Map(rawLines) {
    let inTable2 = false;
    const map = {};
    const startRegex = /(?:STATUS\s+(?:PERKAWINAN|HUBUNGAN)|DOKUMEN\s+IMIGRASI|NAMA\s+ORANG\s+TUA)/i;
    const endRegex = /(?:DIKELUARKAN\s+TANGGAL|KEPALA\s+DINAS|TANDA\s+TANGAN|BALAI\s+SERTIFIKASI|BSSN)/i;

    let currentRow = 0;
    for (const line of rawLines) {
        if (!inTable2) {
            if (startRegex.test(line)) {
                inTable2 = true;
            }
            continue;
        }

        if (endRegex.test(line)) {
            break;
        }

        // Lewati baris header penomoran (10) (11) (12)...
        if (/\([0-9]+\)/.test(line) || /STATUS\s+HUBUNGAN|PERKAWINAN\s*\|/i.test(line)) {
            continue;
        }

        const trimmed = line.trim();
        if (!trimmed || trimmed === '-' || /^[|\s_=-]+$/.test(trimmed)) continue;

        const rowMatch = trimmed.match(/^[|\[\s]*([0-9]{1,2})[.\s|\]]+(.*)/);
        let rowNum = currentRow + 1;
        let content = trimmed;
        if (rowMatch) {
            rowNum = parseInt(rowMatch[1], 10);
            content = rowMatch[2];
            currentRow = rowNum;
        } else {
            rowNum = ++currentRow;
        }

        const upper = content.toUpperCase();
        let relation = '';
        if (/\b(?:KEPALA\s+KELUARGA|KEPALA\s+FAMILI)\b/i.test(upper)) relation = 'Kepala Keluarga';
        else if (/\bSUAMI\b/i.test(upper)) relation = 'Suami';
        else if (/\b(?:ISTRI|ISTERI)\b/i.test(upper)) relation = 'Istri';
        else if (/\b(?:ANAK|ANIAX)\b/i.test(upper)) relation = 'Anak';
        else if (/\bMENANTU\b/i.test(upper)) relation = 'Menantu';
        else if (/\bCUCU\b/i.test(upper)) relation = 'Cucu';
        else if (/\b(?:ORANG\s*TUA|AYAH|IBU)\b/i.test(upper)) relation = 'Orang Tua';
        else if (/\bMERTUA\b/i.test(upper)) relation = 'Mertua';
        else if (/\bFAMILI\b/i.test(upper)) relation = 'Famili Lain';

        let marital = '';
        if (/\bBELUM\s+KAWIN\b/i.test(upper)) marital = 'Belum Kawin';
        else if (/\bCERAI\s+HIDUP\b/i.test(upper)) marital = 'Cerai Hidup';
        else if (/\bCERAI\s+MATI\b/i.test(upper)) marital = 'Cerai Mati';
        else if (/\bKAWIN\b/i.test(upper)) marital = 'Kawin';

        map[rowNum] = { relation, marital };
    }

    return map;
}

function extractJob(str) {
    const s = str.toUpperCase();
    if (/BELUM[\s/|I]*TIDAK\s*BEKERJA|TIDAK\/BELUM\s*BEKERJA|\bBELUM\s*BEKERJA\b/i.test(s)) {
        return 'Belum/Tidak Bekerja';
    }
    if (/KARYAWAN\s+SWASTA/i.test(s)) return 'Karyawan Swasta';
    if (/PEGAWAI\s+NEGERI|PNS/i.test(s)) return 'PNS';
    if (/WIRASWASTA/i.test(s)) return 'Wiraswasta';
    if (/BURUH/i.test(s)) return 'Buruh Harian Lepas';
    if (/PELAJAR|MAHASISWA/i.test(s)) return 'Pelajar/Mahasiswa';
    if (/MENGURUS\s+RUMAH/i.test(s)) return 'Mengurus Rumah Tangga';
    if (/TNI/i.test(s)) return 'TNI';
    if (/POLRI/i.test(s)) return 'Polri';
    if (/PETANI|PEKEBUN/i.test(s)) return 'Petani/Pekebun';
    if (/GURU/i.test(s)) return 'Guru';
    if (/PEDAGANG/i.test(s)) return 'Pedagang';
    if (/DOKTER/i.test(s)) return 'Dokter';
    if (/PENSIUNAN/i.test(s)) return 'Pensiunan';
    return '';
}
