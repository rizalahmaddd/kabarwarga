import jsQR from 'jsqr';
import QRCode from 'qrcode';

function parse(payload) {
    const tags = [];
    let offset = 0;

    while (offset < payload.length) {
        const id = payload.slice(offset, offset + 2);
        const size = payload.slice(offset + 2, offset + 4);
        if (!/^\d{2}$/.test(id) || !/^\d{2}$/.test(size) || offset + 4 + Number(size) > payload.length) {
            return null;
        }
        tags.push([id, payload.slice(offset + 4, offset + 4 + Number(size))]);
        offset += 4 + Number(size);
    }

    return tags;
}

function crc(data) {
    let value = 0xffff;
    for (const byte of new TextEncoder().encode(data)) {
        value ^= byte << 8;
        for (let i = 0; i < 8; i++) {
            value = value & 0x8000 ? (value << 1) ^ 0x1021 : value << 1;
            value &= 0xffff;
        }
    }

    return value.toString(16).toUpperCase().padStart(4, '0');
}

function tag(id, value) {
    return id + String(value.length).padStart(2, '0') + value;
}

export function isStaticQris(payload) {
    const tags = parse(payload || '');
    if (!tags || tags.length < 2 || tags[0][0] !== '00' || tags.at(-1)[0] !== '63') return false;
    if (tags.at(-1)[1].toUpperCase() !== crc(payload.slice(0, -4))) return false;

    return tags.find(([id]) => id === '01')?.[1] !== '12';
}

// Keep in sync with App\Support\Qris::withAmount().
export function qrisWithAmount(payload, amount) {
    const tags = (parse(payload) || []).filter(([id]) => !['54', '55', '56', '57', '63'].includes(id));
    let result = '';
    let amountAdded = false;

    for (const [id, value] of tags) {
        if (!amountAdded && Number(id) > 54) {
            result += tag('54', String(amount));
            amountAdded = true;
        }
        result += tag(id, id === '01' ? '12' : value);
    }
    if (!amountAdded) result += tag('54', String(amount));
    result += '6304';

    return result + crc(result);
}

export async function decodeQrisImage(source) {
    const image = new Image();
    image.src = typeof source === 'string' ? source : URL.createObjectURL(source);
    await image.decode();

    // Large phone screenshots decode faster and just as reliably once scaled down.
    const scale = Math.min(1, 1200 / Math.max(image.naturalWidth, image.naturalHeight));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(image.naturalWidth * scale);
    canvas.height = Math.round(image.naturalHeight * scale);
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

    const result = jsQR(ctx.getImageData(0, 0, canvas.width, canvas.height).data, canvas.width, canvas.height);

    return result ? result.data.trim() : null;
}

export async function renderQr(canvas, payload) {
    await QRCode.toCanvas(canvas, payload, { width: 560, margin: 2, errorCorrectionLevel: 'M' });
    // The library pins an inline pixel width; let CSS size it to the container instead.
    canvas.style.removeProperty('width');
    canvas.style.removeProperty('height');
}
