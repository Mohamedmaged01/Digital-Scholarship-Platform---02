import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import intersect from '@alpinejs/intersect';

/* -------------------------------------------------- */
/*  طبقة التفاعل للواجهة (بديل React + framer-motion)  */
/* -------------------------------------------------- */

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/** ترجمة نصوص الواجهة المولّدة في JS (القاموس يُحقن من القالب حسب اللغة) */
const t = (key, vars = {}) =>
    Object.entries(vars).reduce((s, [k, v]) => s.replaceAll(`:${k}`, v), window.KASP_I18N?.[key] ?? key);

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

/* معرض صور الواجهة: يتقدّم كل 5 ثوانٍ، ويتوقف عند المرور أو التركيز، ولا يتحرك تلقائيًا مع تقليل الحركة */
Alpine.data('heroGallery', (count) => ({
    idx: 0,
    paused: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    hover: false,
    last: Date.now(),
    init() {
        this.timer = setInterval(() => {
            if (this.paused || this.hover || document.hidden) return (this.last = Date.now());
            if (Date.now() - this.last >= 5000) this.go((this.idx + 1) % count);
        }, 500);
    },
    go(i) {
        this.idx = i;
        this.last = Date.now();
    },
    destroy() {
        clearInterval(this.timer);
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

/* ---------------- محرّك التوجيه ---------------- */
/*  مطابقة مبنية على ضوابط كل مسار: يجب أن يشمل المسار الدرجة المختارة، ثم يُطابق
    المجال مع نطاق تخصصاته — جميع التخصصات (الرواد)، أو التخصصات المربوطة فعليًا
    (التميز)، أو مجالات محددة (الصحي)، أو تخصصات محددة وفق الدليل لم تُنشر قائمتها. */
Alpine.data('matcher', (data) => ({
    data,
    step: 0,
    degree: '',
    field: '',
    done: false,
    get fields() {
        return [{ id: 'all', name: t('لم أحدد بعد — اعرض كل المسارات') }, ...this.data.fields];
    },
    get degreeName() {
        return this.data.degrees.find((d) => d.id === this.degree)?.name ?? '';
    },
    get fieldName() {
        return this.fields.find((f) => f.id === this.field)?.name ?? '';
    },
    get canNext() {
        return (this.step === 0 && this.degree) || (this.step === 1 && this.field);
    },
    get progress() {
        return this.done ? 100 : (this.step / 2) * 100;
    },
    next() {
        if (!this.canNext) return;
        if (this.step < 1) this.step += 1;
        else this.done = true;
    },
    reset() {
        Object.assign(this, { step: 0, degree: '', field: '', done: false });
    },
    get results() {
        if (!this.done) return [];
        const anyField = this.field === 'all';
        // ترتيب العرض: تطابق صريح للتخصص/المجال، ثم «جميع التخصصات»، ثم «تخصصات محددة وفق الدليل»
        const rank = { listed: 0, field: 0, all: 1, specified: 2 };

        return this.data.tracks
            .filter((track) => track.degrees.includes(this.degree))
            .map((track) => {
                const listed = track.majors.filter((m) => m.degree === this.degree);
                if (listed.length) {
                    const majors = listed.filter((m) => anyField || m.field === this.field);
                    if (!majors.length) return null;
                    return { track, kind: 'listed', majors, majorsNote: t(':count تخصص محدد في هذا المجال', { count: majors.length }) };
                }
                if (track.scope === 'all') {
                    return { track, kind: 'all', majors: [], majorsNote: track.scopeLabel };
                }
                if (track.matchFields.length) {
                    if (!anyField && !track.matchFields.includes(this.field)) return null;
                    return { track, kind: 'field', majors: [], majorsNote: t('تخصصات محددة في هذا المجال — راجع الشروط الخاصة') };
                }
                return { track, kind: 'specified', majors: [], majorsNote: t(':scope — تحقق من الدليل الاسترشادي', { scope: track.scopeLabel }) };
            })
            .filter(Boolean)
            .sort((a, b) => rank[a.kind] - rank[b.kind]);
    },
}));

/* ---------------- قوائم الدليل الاسترشادي: مجال ← تخصصات + مؤسسات بترتيب الدليل ---------------- */
Alpine.data('guidelineLists', (lists) => ({
    lists,
    active: lists[0]?.key ?? null,
    search: '',
    get current() {
        return this.lists.find((l) => l.key === this.active) ?? null;
    },
    get filtered() {
        const q = this.search.trim().toLowerCase();
        if (!this.current) return [];
        return q ? this.current.institutions.filter((u) => `${u.name} ${u.country}`.toLowerCase().includes(q)) : this.current.institutions;
    },
    select(key) {
        this.active = key;
        this.search = '';
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
        window.dispatchEvent(new Event('assistant-asked'));
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
                text: t('تعذّر الوصول إلى المساعد حاليًا. يمكنك التواصل مع الإدارة مباشرة عبر القنوات التالية:'),
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

/* ---------------- تقييم أداء الخدمة ---------------- */
// يُطلب التقييم تلقائيًا مرة واحدة في الجلسة بعد استخدام المساعد أو البحث، وزر التقييم متاح دائمًا
const sessionFlag = (key, value) => {
    try {
        return value === undefined ? sessionStorage.getItem(key) : sessionStorage.setItem(key, value);
    } catch {
        return null;
    }
};
const requestRating = (source) => setTimeout(() => window.dispatchEvent(new CustomEvent('rate-service', { detail: { source, auto: true } })), 450);

Alpine.data('serviceRating', (url) => ({
    open: false,
    source: 'widget',
    rating: '',
    notes: '',
    sending: false,
    done: false,
    error: '',
    get notesRequired() {
        return this.rating === 'needs_improvement';
    },
    show({ source = 'widget', auto = false } = {}) {
        if (auto && (sessionFlag('kasp-rated') || sessionFlag('kasp-rating-asked'))) return;
        if (auto) sessionFlag('kasp-rating-asked', '1');
        Object.assign(this, { source, open: true, done: false, error: '' });
    },
    async submit() {
        if (!this.rating) return (this.error = t('اختر التقييم المناسب أولًا'));
        if (this.notesRequired && !this.notes.trim()) return (this.error = t('يرجى كتابة الملاحظات لمساعدتنا على معرفة مكان الخلل'));
        this.sending = true;
        this.error = '';
        try {
            await http(url, { method: 'POST', body: { rating: this.rating, notes: this.notes, source: this.source } });
            sessionFlag('kasp-rated', '1');
            Object.assign(this, { done: true, rating: '', notes: '' });
        } catch {
            this.error = t('تعذّر إرسال التقييم، حاول مرة أخرى.');
        } finally {
            this.sending = false;
        }
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
    searched: false,
    init() {
        window.addEventListener('open-search', () => this.show());
        this.$watch('open', (v) => {
            document.body.style.overflow = v ? 'hidden' : '';
            if (!v && this.searched) {
                this.searched = false;
                requestRating('search');
            }
        });
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
            this.searched = true;
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
