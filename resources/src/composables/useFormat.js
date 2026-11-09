import { computed } from 'vue';
import dayjs from 'dayjs';
import { useAuthStore } from '../stores/auth';
import { formatPriceDisplay, resolvePriceDecimals, roundAmount } from '../lib/priceFormat';

const JALALI_MONTHS_FA = [
    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
];

function isShamsiFormat(format) {
    const f = String(format || '').trim().toUpperCase();
    return f === 'SHAMSI' ||
        f.startsWith('SHAMSI ') ||
        f.startsWith('SHAMSI/') ||
        f.includes('JALALI') ||
        f.includes('SHAMSI / JALALI') ||
        f.includes('شمسی');
}

function gregorianToJalali(value) {
    if (!value) return '';

    const raw = String(value);
    const m = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})(.*)$/);
    if (!m) return raw;

    const gy = Number(m[1]);
    const gm = Number(m[2]);
    const gd = Number(m[3]);

    const gdm = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    const leap = (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0;

    let gdn =
        365 * (gy - 1600) +
        Math.floor((gy - 1600 + 3) / 4) -
        Math.floor((gy - 1600 + 99) / 100) +
        Math.floor((gy - 1600 + 399) / 400);

    for (let i = 0; i < gm - 1; i++) gdn += gdm[i];
    if (gm > 2 && leap) gdn++;
    gdn += gd - 1;

    let jdn = gdn - 79;
    const jnp = Math.floor(jdn / 12053);
    jdn %= 12053;

    let jy = 979 + 33 * jnp + 4 * Math.floor(jdn / 1461);
    jdn %= 1461;

    if (jdn >= 366) {
        jy += Math.floor((jdn - 1) / 365);
        jdn = (jdn - 1) % 365;
    }

    let jm;
    let jd;

    if (jdn < 186) {
        jm = 1 + Math.floor(jdn / 31);
        jd = 1 + (jdn % 31);
    } else {
        jm = 7 + Math.floor((jdn - 186) / 30);
        jd = 1 + ((jdn - 186) % 30);
    }

    return {
        year: jy,
        month: jm,
        day: jd,
        monthName: JALALI_MONTHS_FA[jm - 1]
    };
}

function formatShamsi(value, withTime = false) {
    const j = gregorianToJalali(value);
    if (!j || typeof j !== 'object') return String(value || '');

    // Shamsi display: 1405/06/08
let result = `${j.year}/${String(j.month).padStart(2, '0')}/${String(j.day).padStart(2, '0')}`;

    if (withTime) {
        const timeMatch = String(value).match(/(?:T|\s)(\d{1,2}:\d{2})(?::\d{2})?/);
        if (timeMatch) result += ` ${timeMatch[1]}`;
    }

    return result;
}

export function useFormat() {
    const auth = useAuthStore();

    const currency = computed(() => auth.currency);
    const decimals = computed(() => resolvePriceDecimals(auth.user?.price_decimals));
    const dateFormat = computed(() => auth.user?.date_format || 'YYYY-MM-DD');
    const isShamsi = computed(() => isShamsiFormat(dateFormat.value));

    function number(value, precision = decimals.value) {
        return formatPriceDisplay(value, precision, auth.user?.price_format);
    }

    function money(value, precision = decimals.value) {
        return `${currency.value} ${number(value, precision)}`;
    }

    function roundMoney(value, precision = decimals.value) {
        return roundAmount(value, precision);
    }

    function date(value, format) {
        if (!value) return '';

        const selectedFormat = format || dateFormat.value;

        if (isShamsiFormat(selectedFormat)) {
            return formatShamsi(value, false);
        }

        const d = dayjs(value);
        return d.isValid() ? d.format(selectedFormat) : String(value);
    }

    function dateTime(value) {
        if (!value) return '';

        if (isShamsiFormat(dateFormat.value)) {
            return formatShamsi(value, true);
        }

        return date(value, `${dateFormat.value} HH:mm`);
    }

    return {
        money,
        number,
        roundMoney,
        currency,
        decimals,
        date,
        dateTime,
        isShamsi,
        dateFormat
    };
}
