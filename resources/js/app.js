import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import intersect from '@alpinejs/intersect';

/* -------------------------------------------------- */
/*  طبقة التفاعل للواجهة (بديل React + framer-motion)  */
/* -------------------------------------------------- */

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function http(url, { method = 'GET', body } = {}) {
    const res = await fetch(url, {
        method,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(body ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() } : {}),
        },
        body: body ? JSON.stringify(body) : undefined,
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

const easeOut = (p, pow = 4) => 1 - Math.pow(1 - p, pow);

/** عدّاد متحرك يبدأ من الصفر — يُستدعى مرة واحدة */
function animateNumber(target, duration, onTick, pow = 4) {
    const t0 = performance.now();
    const tick = (t) => {
        const p = Math.min(1, (t - t0) / duration);
        onTick(Math.round(target * easeOut(p, pow)));
        if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}

/* ---------------- ظهور العناصر عند التمرير ---------------- */
function initReveal() {
    const els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver(
        (entries) => {
            for (const e of entries) {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    io.unobserve(e.target);
                }
            }
        },
        { rootMargin: '0px 0px -70px 0px' },
    );
    els.forEach((el) => io.observe(el));
}

/* ---------------- شريط التنقل ---------------- */
Alpine.data('navbar', () => ({
    scrolled: false,
    menu: false,
    progress: 0,
    init() {
        const onScroll = () => {
            this.scrolled = window.scrollY > 24;
            const max = document.documentElement.scrollHeight - window.innerHeight;
            this.progress = max > 0 ? window.scrollY / max : 0;
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        // اختصار فتح البحث: Ctrl/Cmd + K أو الزر /
        window.addEventListener('keydown', (e) => {
            const tag = e.target?.tagName;
            const typing = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
            if ((e.key === 'k' || e.key === 'K') && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                window.dispatchEvent(new Event('open-search'));
            } else if (e.key === '/' && !typing) {
                e.preventDefault();
                window.dispatchEvent(new Event('open-search'));
            }
        });

        this.$watch('menu', (v) => (document.body.style.overflow = v ? 'hidden' : ''));
    },
}));

/* ---------------- الواجهة الرئيسية ---------------- */
Alpine.data('heroRotator', (items) => ({
    items,
    idx: 0,
    init() {
        setInterval(() => (this.idx = (this.idx + 1) % this.items.length), 2400);
    },
}));

Alpine.data('countUp', (target) => ({
    value: 0,
    started: false,
    start() {
        if (this.started) return;
        this.started = true;
        animateNumber(target, 1900, (v) => (this.value = v));
    },
    get display() {
        return this.value.toLocaleString('en-US');
    },
}));

/* ---------------- المسارات ---------------- */
Alpine.data('tracksFilter', (degreesById) => ({
    degree: 'all',
    degreesById,
    shows(id) {
        return this.degree === 'all' || this.degreesById[id].includes(this.degree);
    },
    get count() {
        return Object.keys(this.degreesById).filter((id) => this.shows(id)).length;
    },
}));

/* ---------------- المطابقة الذكية ---------------- */
const matcherQuestions = [
    {
        q: 'ما الدرجة العلمية التي تستهدفها؟',
        options: [
            { label: 'بكالوريوس', hint: 'بعد الثانوية مباشرة', boost: { rowad: 2, emdad: 2, waed: 2, tamayoz: 1 } },
            { label: 'ماجستير', hint: 'تعميق التخصص', boost: { research: 2, tamayoz: 2, emdad: 1 } },
            { label: 'دكتوراه', hint: 'البحث العلمي الأصيل', boost: { research: 4, rowad: 1 } },
            { label: 'زمالة / دبلوم مهني', hint: 'خبرة تطبيقية مركزة', boost: { sahhi: 5, emdad: 1 } },
        ],
    },
    {
        q: 'أي مجال معرفي يشغفك أكثر؟',
        options: [
            { label: 'الطب والعلوم الصحية', boost: { sahhi: 4, research: 1 } },
            { label: 'الحاسب والذكاء الاصطناعي', boost: { rowad: 3, waed: 2 } },
            { label: 'الهندسة والطاقة', boost: { emdad: 2, waed: 3 } },
            { label: 'العلوم والبحث الأساسي', boost: { research: 4 } },
            { label: 'الإدارة والسياسات العامة', boost: { rowad: 2, emdad: 1 } },
            { label: 'الفنون والسينما والتصميم', boost: { tamayoz: 5 } },
        ],
    },
    {
        q: 'ما وجهتك الدراسية المفضلة؟',
        options: [
            { label: 'أمريكا الشمالية', boost: { rowad: 1 }, region: 'na' },
            { label: 'أوروبا', boost: { rowad: 1 }, region: 'europe' },
            { label: 'آسيا', boost: { research: 1 }, region: 'asia' },
            { label: 'أوقيانوسيا', boost: { emdad: 1 }, region: 'oceania' },
            { label: 'لا أفضّل وجهة محددة', boost: {}, region: 'all' },
        ],
    },
    {
        q: 'ما مستوى جاهزيتك للتقديم؟',
        options: [
            { label: 'جاهز للتقديم الآن', hint: 'المستندات واللغة مكتملة', boost: {}, ready: 3 },
            { label: 'أحتاج إعداد اللغة', hint: 'سنة تحضيرية مدعومة بالكامل', boost: { emdad: 1 }, ready: 2 },
            { label: 'ما زلت أستكشف', hint: 'لا بأس — سنرسم الطريق معًا', boost: {}, ready: 1 },
        ],
    },
];

Alpine.data('matcher', (tracks, universities) => ({
    questions: matcherQuestions,
    step: 0,
    answers: [],
    done: false,
    result: null,
    shownMatch: 0,
    ringCircumference: 2 * Math.PI * 68,
    get progress() {
        return this.done ? 100 : (this.step / this.questions.length) * 100;
    },
    get current() {
        return this.questions[this.step];
    },
    choose(opt) {
        this.answers = [...this.answers.slice(0, this.step), opt];
        setTimeout(() => {
            if (this.step + 1 >= this.questions.length) this.finish();
            else this.step += 1;
        }, 260);
    },
    back() {
        if (this.step > 0) this.step -= 1;
    },
    finish() {
        const scores = Object.fromEntries(tracks.map((t) => [t.id, 0]));
        let region = 'all';
        let ready = 2;
        for (const a of this.answers) {
            for (const [k, v] of Object.entries(a.boost)) scores[k] = (scores[k] ?? 0) + v;
            if (a.region) region = a.region;
            if (a.ready) ready = a.ready;
        }
        const ranked = tracks.map((t) => ({ t, s: scores[t.id] ?? 0 })).sort((a, b) => b.s - a.s);
        const top = ranked[0];
        const runnerUp = ranked[1] ?? ranked[0];
        const match = Math.min(99, Math.max(81, 80 + top.s * 3 + ready * 2));
        const unis = (region === 'all' ? universities : universities.filter((u) => u.region === region))
            .slice()
            .sort((a, b) => a.rank - b.rank)
            .slice(0, 3);
        this.result = { track: top.t, runnerUp: runnerUp.t, match, unis };
        this.done = true;
        this.shownMatch = 0;
        setTimeout(() => animateNumber(match, 1500, (v) => (this.shownMatch = v), 3), 300);
    },
    reset() {
        this.answers = [];
        this.step = 0;
        this.done = false;
        this.result = null;
    },
    get ringOffset() {
        return this.ringCircumference * (1 - this.shownMatch / 100);
    },
}));

/* ---------------- الجامعات ---------------- */
Alpine.data('universitiesFilter', (items) => ({
    region: 'all',
    query: '',
    items,
    shows(id) {
        const u = this.items[id];
        const q = this.query.trim();
        const okRegion = this.region === 'all' || u.region === this.region;
        const okQuery =
            !q ||
            u.nameAr.includes(q) ||
            u.nameEn.toLowerCase().includes(q.toLowerCase()) ||
            u.country.includes(q) ||
            u.fields.some((f) => f.includes(q));
        return okRegion && okQuery;
    },
    get count() {
        return Object.keys(this.items).filter((id) => this.shows(id)).length;
    },
}));

/* ---------------- المركز الإعلامي ---------------- */
Alpine.data('newsCenter', (items) => ({
    filter: 'all',
    items, // مرتبة مسبقًا من الخادوم: المثبّت ثم الأحدث
    selected: null,
    get visible() {
        return this.filter === 'all' ? this.items : this.items.filter((n) => n.category === this.filter);
    },
    get featuredId() {
        return this.visible[0]?.id ?? null;
    },
    inList(id) {
        return id !== this.featuredId && this.visible.some((n) => n.id === id);
    },
    open(id) {
        this.selected = this.items.find((n) => n.id === id) ?? null;
    },
    get paragraphs() {
        return this.selected ? this.selected.body.split(/\n\s*\n/) : [];
    },
    init() {
        this.$watch('selected', (v) => (document.body.style.overflow = v ? 'hidden' : ''));
    },
}));

/* ---------------- خارطة الطريق ---------------- */
Alpine.data('roadmap', (count) => ({
    active: 0,
    last: count - 1,
    prev() {
        this.active = Math.max(0, this.active - 1);
    },
    next() {
        this.active = Math.min(this.last, this.active + 1);
    },
}));

/* ---------------- المساعد الذكي ---------------- */
let msgSeq = 0;

Alpine.data('chatPanel', (askUrl, greeting, suggestions) => ({
    messages: [{ id: ++msgSeq, role: 'bot', text: greeting, suggestions }],
    input: '',
    typing: false,
    async send(raw) {
        const text = (raw ?? this.input).trim();
        if (!text || this.typing) return;
        this.input = '';
        this.messages.push({ id: ++msgSeq, role: 'user', text });
        this.typing = true;
        this.scroll();

        // زمن «كتابة» قصير يمنح إحساس المحادثة
        const minDelay = new Promise((r) => setTimeout(r, 700 + Math.random() * 500));
        try {
            const [res] = await Promise.all([http(askUrl, { method: 'POST', body: { q: text } }), minDelay]);
            this.messages.push({
                id: ++msgSeq,
                role: 'bot',
                text: res.answer ?? res.message,
                suggestions: res.suggestions ?? [],
                contact: !res.answer,
            });
        } catch {
            await minDelay;
            this.messages.push({
                id: ++msgSeq,
                role: 'bot',
                text: 'تعذّر الوصول إلى المساعد حاليًا. يمكنك التواصل مع الإدارة مباشرة عبر القنوات التالية:',
                suggestions: [],
                contact: true,
            });
        } finally {
            this.typing = false;
            this.scroll();
        }
    },
    scroll() {
        this.$nextTick(() => {
            const el = this.$refs.scroller;
            if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
        });
    },
}));

/* ---------------- البحث في المنصة ---------------- */
Alpine.data('searchOverlay', (searchUrl, quickLinks, icons) => ({
    open: false,
    query: '',
    results: [],
    active: 0,
    loading: false,
    icons,
    timer: null,
    init() {
        window.addEventListener('open-search', () => this.show());
        this.$watch('open', (v) => (document.body.style.overflow = v ? 'hidden' : ''));
        this.$watch('query', () => {
            this.active = 0;
            clearTimeout(this.timer);
            if (this.query.trim().length < 2) {
                this.results = [];
                return;
            }
            this.timer = setTimeout(() => this.fetch(), 160);
        });
    },
    show() {
        this.query = '';
        this.active = 0;
        this.open = true;
        this.$nextTick(() => this.$refs.input?.focus());
    },
    async fetch() {
        const q = this.query;
        this.loading = true;
        try {
            const res = await http(`${searchUrl}?q=${encodeURIComponent(q)}`);
            if (q === this.query) this.results = res.results;
        } catch {
            this.results = [];
        } finally {
            this.loading = false;
        }
    },
    get flat() {
        return this.query.trim().length >= 2 ? this.results : quickLinks;
    },
    get groups() {
        const map = new Map();
        this.flat.forEach((r, i) => {
            if (!map.has(r.group)) map.set(r.group, { name: r.group, groupId: r.groupId, items: [] });
            map.get(r.group).items.push({ ...r, idx: i });
        });
        return [...map.values()];
    },
    icon(groupId) {
        return this.icons[groupId] ?? this.icons.sections;
    },
    move(delta) {
        this.active = Math.max(0, Math.min(this.flat.length - 1, this.active + delta));
        this.$nextTick(() => this.$refs.list?.querySelector(`[data-idx="${this.active}"]`)?.scrollIntoView({ block: 'nearest' }));
    },
    go(r) {
        if (!r) return;
        this.open = false;
        setTimeout(() => document.querySelector(r.href)?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
    },
}));

/* ---------------- العودة للأعلى ---------------- */
Alpine.data('scrollTop', () => ({
    visible: false,
    progress: 0,
    init() {
        const onScroll = () => {
            this.visible = window.scrollY > 600;
            const max = document.documentElement.scrollHeight - window.innerHeight;
            this.progress = max > 0 ? window.scrollY / max : 0;
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    },
    get dashOffset() {
        const c = 2 * Math.PI * 21;
        return c * (1 - this.progress);
    },
}));

/* ---------------- خلفية متحركة بالتمرير (Parallax) ---------------- */
Alpine.data('parallax', () => ({
    y: 0,
    init() {
        const onScroll = () => {
            const r = this.$el.getBoundingClientRect();
            const total = window.innerHeight + r.height;
            const p = Math.min(1, Math.max(0, (window.innerHeight - r.top) / total));
            this.y = -12 + p * 24;
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    },
}));

/* ---------------- لوحة التحكم: البحث العام ---------------- */
Alpine.data('adminSearch', (url) => ({
    q: '',
    hits: [],
    timer: null,
    init() {
        this.$watch('q', () => {
            clearTimeout(this.timer);
            if (this.q.trim().length < 2) {
                this.hits = [];
                return;
            }
            this.timer = setTimeout(async () => {
                try {
                    this.hits = (await http(`${url}?q=${encodeURIComponent(this.q)}`)).hits;
                } catch {
                    this.hits = [];
                }
            }, 160);
        });
    },
    go(hit) {
        if (hit) window.location.href = hit.url;
    },
}));

/* ---------------- لوحة التحكم: رفع الملفات ---------------- */
Alpine.data('dropzone', () => ({
    over: false,
    drop(e) {
        this.over = false;
        if (!e.dataTransfer?.files?.length) return;
        this.$refs.input.files = e.dataTransfer.files;
        this.$refs.form.requestSubmit();
    },
}));

Alpine.data('copyText', (text) => ({
    copied: false,
    async copy() {
        try {
            await navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => (this.copied = false), 1800);
        } catch {
            /* الحافظة غير متاحة */
        }
    },
}));

Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(intersect);
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', initReveal);
