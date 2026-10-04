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

/* ---------------- محرّك التوجيه الاسترشادي ---------------- */
/*  يطابق (الدرجة، المجال) مع روابط: مسار ← درجة ← تخصص ← جامعة.
    واعد مستبعد مسبقًا لأنه يعمل بنظام البرامج. */
Alpine.data('matcher', (data) => ({
    data,
    step: 0,
    degree: '',
    field: '',
    region: 'all',
    done: false,
    get fields() {
        return [{ id: 'all', name: 'جميع المجالات' }, ...this.data.fields];
    },
    get canNext() {
        return (this.step === 0 && this.degree) || (this.step === 1 && this.field) || this.step === 2;
    },
    get progress() {
        return this.done ? 100 : (this.step / 3) * 100;
    },
    next() {
        if (!this.canNext) return;
        if (this.step < 2) this.step += 1;
        else this.done = true;
    },
    reset() {
        Object.assign(this, { step: 0, degree: '', field: '', region: 'all', done: false });
    },
    get results() {
        if (!this.done) return [];
        return this.data.tracks
            .map((track) => {
                let score = 0;
                if (track.degrees.includes(this.degree)) score += 40;

                const majors = track.majors.filter(
                    (m) => m.degree === this.degree && (this.field === 'all' || m.field === this.field),
                );
                if (majors.length) score += 30 + Math.min(majors.length * 3, 20);

                const links = track.links.filter((l) => l.degree === this.degree);
                const totalUnis = new Set(links.map((l) => l.uni)).size;
                if (totalUnis) score += Math.min(totalUnis, 10);

                const withUnis = majors
                    .map((m) => ({ name: m.name, unis: links.filter((l) => l.major === m.id).length }))
                    .filter((m) => m.unis > 0);

                return { track, score: Math.min(score, 100), majors: withUnis, totalUnis };
            })
            .filter((r) => r.score > 0)
            .sort((a, b) => b.score - a.score);
    },
}));

/* ---------------- مستكشف المسار: درجة ← تخصص ← جامعات ---------------- */
Alpine.data('trackExplorer', (data) => ({
    data,
    degree: '',
    major: '',
    search: '',
    init() {
        this.$watch('degree', () => {
            this.major = '';
            this.search = '';
        });
        this.$watch('major', () => (this.search = ''));
    },
    get degreeObj() {
        return this.data.degrees.find((d) => d.id === this.degree) ?? null;
    },
    get majorObj() {
        return this.degreeObj?.majors.find((m) => m.id === this.major) ?? null;
    },
    /** التخصصات مجمّعة بالمجال المعرفي لقائمة الاختيار */
    get fieldGroups() {
        const groups = {};
        for (const m of this.degreeObj?.majors ?? []) (groups[m.field] ??= []).push(m);
        return Object.entries(groups).map(([name, majors]) => ({ name, majors }));
    },
    get constraints() {
        return this.major ? (this.data.constraints[this.major] ?? []) : [];
    },
    /** قيد degree_restriction: الدرجات المسموحة لهذا التخصص فقط */
    get allowedDegrees() {
        const c = this.constraints.find((x) => x.allowedIds.length);
        return c ? c.allowedIds : null;
    },
    get universities() {
        const q = this.search.trim();
        return (this.majorObj?.universities ?? []).filter(
            (u) => !q || u.nameAr.includes(q) || u.nameEn.toLowerCase().includes(q.toLowerCase()) || u.country.includes(q),
        );
    },
}));

/* ---------------- صفحة واعد: تصفية البرامج ---------------- */
Alpine.data('waedPrograms', (programs) => ({
    programs, // id => { sector, text }
    sector: 'الكل',
    search: '',
    showPast: true,
    shows(id) {
        const p = this.programs[id];
        const q = this.search.trim();
        return (this.sector === 'الكل' || p.sector === this.sector) && (!q || p.text.includes(q));
    },
    count(ids) {
        return ids.filter((id) => this.shows(id)).length;
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
        if (!r.href.startsWith('#')) {
            window.location.href = r.href;
            return;
        }
        const target = document.querySelector(r.href);
        if (target) setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
        else window.location.href = `/${r.href}`;
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

/** معاينة الصفحات القانونية: نفس قواعد LegalPage::renderedContent، والنص مُهرَّب أولًا */
const escapeHtml = (t) => t.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
window.renderLegal = (content) =>
    content
        .trim()
        .split(/\n\s*\n/)
        .filter(Boolean)
        .map((p) => {
            const html = escapeHtml(p.trim())
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.+?)\*\*/g, '<strong class="text-ink">$1</strong>')
                .replace(/\*(.+?)\*/g, '<em class="text-slate-500">$1</em>');
            return `<p>${html}</p>`;
        })
        .join('');

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
