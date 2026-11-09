<style>
/* ============================================
   PER-LAYOUT THEMES — driven by body class
   Applied to all 6 landing templates.
   ============================================ */

/* ---- MODERN (teal accent) ---- */
body.layout-modern .nav-cta,
body.layout-modern .btn-primary,
body.layout-modern .cta-block .btn-primary { background: #0d9488 !important; }
body.layout-modern .nav-cta:hover,
body.layout-modern .btn-primary:hover { background: #0f766e !important; }
body.layout-modern .hero { background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%) !important; }
body.layout-modern .feature .ico { background: #ccfbf1 !important; color: #0f766e !important; }
body.layout-modern .step .num { background: #0d9488 !important; }
body.layout-modern .cta-block { background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%) !important; }
body.layout-modern .pricing-card.featured { border-color: #0d9488; }
body.layout-modern .pricing-card__features li i { color: #0d9488; }
body.layout-modern .pricing-card.featured .pricing-card__cta { background: #0d9488 !important; border-color: #0d9488 !important; }
body.layout-modern .pricing-card__amount.free { color: #0d9488; }

/* ---- STRUCTURED (indigo accent, tight) ---- */
body.layout-structured .nav-cta,
body.layout-structured .btn-primary,
body.layout-structured .cta-block .btn-primary { background: #4f46e5 !important; }
body.layout-structured .nav-cta:hover,
body.layout-structured .btn-primary:hover { background: #4338ca !important; }
body.layout-structured .hero { padding: 80px 0 60px !important; }
body.layout-structured section.block { padding: 64px 0 !important; }
body.layout-structured .feature .ico { background: #eef2ff !important; color: #4338ca !important; }
body.layout-structured .step .num { background: #4f46e5 !important; }
body.layout-structured .stats-bar { background: #312e81 !important; }
body.layout-structured .cta-block { background: linear-gradient(135deg, #312e81 0%, #4f46e5 100%) !important; }
body.layout-structured .pricing-card.featured { border-color: #4f46e5; }
body.layout-structured .pricing-card__features li i { color: #4f46e5; }
body.layout-structured .pricing-card.featured .pricing-card__cta { background: #4f46e5 !important; border-color: #4f46e5 !important; }
body.layout-structured .pricing-card__amount.free { color: #4f46e5; }

/* ---- DARK (dark everything, teal accent) ---- */
body.layout-dark { background: #0f172a !important; color: #e2e8f0 !important; }
body.layout-dark .nav { background: rgba(15,23,42,.9) !important; border-bottom-color: #1e293b !important; }
body.layout-dark .nav .brand, body.layout-dark .nav .brand-text { color: #ffffff !important; }
body.layout-dark .nav a, body.layout-dark .nav ul li, body.layout-dark .hero ul li { color: #cbd5e1 !important; }
body.layout-dark .hero { background: #0f172a !important; }
body.layout-dark .hero h1 { color: #ffffff !important; }
body.layout-dark .hero p.lead { color: #94a3b8 !important; }
body.layout-dark .hero .badge { background: rgba(20,184,166,.15) !important; color: #5eead4 !important; }
body.layout-dark .hero ul li::before { color: #14b8a6 !important; }
body.layout-dark section.block { background: #0f172a !important; }
body.layout-dark section.block.alt { background: #1e293b !important; }
body.layout-dark .section-title h2 { color: #ffffff !important; }
body.layout-dark .section-title p { color: #94a3b8 !important; }
body.layout-dark .feature, body.layout-dark .testimonial, body.layout-dark .step {
    background: #1e293b !important; border-color: #334155 !important;
}
body.layout-dark .feature h3,
body.layout-dark .step h3 { color: #ffffff !important; }
body.layout-dark .feature p, body.layout-dark .step p,
body.layout-dark .testimonial .quote { color: #cbd5e1 !important; }
body.layout-dark .testimonial .who .name { color: #ffffff !important; }
body.layout-dark .testimonial .who .role { color: #94a3b8 !important; }
body.layout-dark .nav-cta, body.layout-dark .btn-primary,
body.layout-dark .cta-block .btn-primary { background: #14b8a6 !important; color: #fff !important; }
body.layout-dark .nav-cta:hover, body.layout-dark .btn-primary:hover { background: #0f766e !important; }
body.layout-dark .btn-outline { background: transparent !important; color: #e2e8f0 !important; border-color: #334155 !important; }
body.layout-dark .btn-outline:hover { background: #1e293b !important; }
body.layout-dark .faq-item { border-bottom-color: #1e293b !important; }
body.layout-dark .faq-item summary { color: #ffffff !important; }
body.layout-dark .faq-item .answer { color: #94a3b8 !important; }
body.layout-dark .stats-bar { background: #020617 !important; }
body.layout-dark .cta-block { background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%) !important; }
body.layout-dark footer { background: #020617 !important; }

/* DARK — pricing overrides */
body.layout-dark .pricing-section { background: #0f172a !important; }
body.layout-dark .pricing-head h2 { color: #fff !important; }
body.layout-dark .pricing-head p { color: #94a3b8 !important; }
body.layout-dark .pricing-card { background: #1e293b !important; border-color: #334155 !important; }
body.layout-dark .pricing-card__name,
body.layout-dark .pricing-card__amount { color: #fff !important; }
body.layout-dark .pricing-card__features li { color: #cbd5e1 !important; }
body.layout-dark .pricing-card__cta { background: #334155 !important; border-color: #475569 !important; color: #fff !important; }
body.layout-dark .pricing-card.featured { border-color: #14b8a6 !important; }
body.layout-dark .pricing-card__features li i { color: #14b8a6 !important; }
body.layout-dark .pricing-card.featured .pricing-card__cta { background: #14b8a6 !important; border-color: #14b8a6 !important; }

/* ---- LIGHT (warm orange, bright) ---- */
body.layout-light { background: #fffbf5 !important; }
body.layout-light .nav { background: rgba(255,251,245,.9) !important; }
body.layout-light .nav-cta, body.layout-light .btn-primary,
body.layout-light .cta-block .btn-primary { background: #ea580c !important; }
body.layout-light .nav-cta:hover, body.layout-light .btn-primary:hover { background: #c2410c !important; }
body.layout-light .hero { background: linear-gradient(180deg, #fff7ed 0%, #fffbf5 100%) !important; }
body.layout-light .hero .badge { background: #ffedd5 !important; color: #c2410c !important; }
body.layout-light .hero ul li::before { color: #ea580c !important; }
body.layout-light .feature .ico { background: #ffedd5 !important; color: #c2410c !important; }
body.layout-light .step .num { background: #ea580c !important; }
body.layout-light section.block.alt { background: #fff7ed !important; }
body.layout-light .stats-bar { background: #7c2d12 !important; }
body.layout-light .cta-block { background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%) !important; }
body.layout-light .pricing-card.featured { border-color: #ea580c; }
body.layout-light .pricing-card__features li i { color: #ea580c; }
body.layout-light .pricing-card.featured .pricing-card__cta { background: #ea580c !important; border-color: #ea580c !important; }
body.layout-light .pricing-card__amount.free { color: #ea580c; }

/* ---- AGENCY (navy hero, mint buttons) ---- */
body.layout-agency .nav-cta,
body.layout-agency .btn-primary,
body.layout-agency .cta-block .btn-primary { background: #10b981 !important; }
body.layout-agency .nav-cta:hover,
body.layout-agency .btn-primary:hover { background: #059669 !important; }
body.layout-agency .hero {
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
    color: #ffffff !important;
}
body.layout-agency .hero h1 { color: #ffffff !important; }
body.layout-agency .hero p.lead { color: #cbd5e1 !important; }
body.layout-agency .hero .badge { background: rgba(16,185,129,.15) !important; color: #6ee7b7 !important; }
body.layout-agency .hero ul li { color: #cbd5e1 !important; }
body.layout-agency .hero ul li::before { color: #10b981 !important; }
body.layout-agency .stats-bar { background: #0f172a !important; }
body.layout-agency .feature .ico { background: #d1fae5 !important; color: #047857 !important; }
body.layout-agency .step .num { background: #10b981 !important; }
body.layout-agency .cta-block { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%) !important; }
body.layout-agency .pricing-card.featured { border-color: #10b981; }
body.layout-agency .pricing-card__features li i { color: #10b981; }
body.layout-agency .pricing-card.featured .pricing-card__cta { background: #10b981 !important; border-color: #10b981 !important; }
body.layout-agency .pricing-card__amount.free { color: #10b981; }

/* ============================================================
   PRICING — matching the reference design
   ============================================================ */
.pricing-section { padding: 96px 0; }
.pricing-head { text-align: center; margin-bottom: 56px; }
.pricing-head .eyebrow {
    display: inline-flex; align-items: center; gap: 10px;
    color: #0d9488; font-size: 12px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    margin-bottom: 14px;
}
.pricing-head .eyebrow::before { content: ""; width: 26px; height: 1px; background: currentColor; }
.pricing-head h2 {
    font-size: clamp(28px, 3.6vw, 44px);
    font-weight: 800; letter-spacing: -1.2px;
    color: #0f172a; line-height: 1.15; margin-bottom: 14px;
}
.pricing-head p { color: #6b7280; font-size: 16px; max-width: 620px; margin: 0 auto; }

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    align-items: start;
}
@media (max-width: 1000px) { .pricing-grid { grid-template-columns: 1fr; } }

.pricing-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 32px 28px;
    display: flex; flex-direction: column;
    transition: transform .2s, box-shadow .2s;
    position: relative;
}
.pricing-card:hover { transform: translateY(-3px); box-shadow: 0 20px 50px rgba(15,23,42,.08); }
.pricing-card.featured {
    border-color: #0d9488;
    box-shadow: 0 24px 60px rgba(13,148,136,.14);
    transform: scale(1.02);
}
@media (max-width: 1000px) { .pricing-card.featured { transform: none; } }
.pricing-card .badge {
    position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
    background: #0d9488; color: #fff;
    font-size: 10.5px; font-weight: 800; letter-spacing: 1px;
    padding: 6px 14px; border-radius: 999px;
    text-transform: uppercase;
}
.pricing-card__name {
    font-size: 22px; font-weight: 800; letter-spacing: -.5px;
    color: #0f172a; margin-bottom: 6px;
}
.pricing-card__hint { color: #6b7280; font-size: 13px; margin-bottom: 22px; }
.pricing-card__price { display: flex; align-items: flex-end; gap: 4px; margin-bottom: 6px; }
.pricing-card__amount {
    font-size: 44px; font-weight: 800; letter-spacing: -1.5px;
    color: #0f172a; line-height: 1;
}
.pricing-card__amount.free { font-size: 40px; color: #0d9488; }
.pricing-card__per { color: #6b7280; font-size: 13px; font-weight: 500; padding-bottom: 4px; }
.pricing-card__year {
    color: #6b7280; font-size: 12.5px; margin-bottom: 24px;
    display: flex; align-items: center; gap: 8px;
}
.pricing-card__save {
    background: #d1fae5; color: #047857;
    font-size: 10.5px; font-weight: 800; letter-spacing: .3px;
    padding: 3px 8px; border-radius: 4px;
}
.pricing-card__features {
    list-style: none; padding: 0; margin: 0 0 24px;
    border-top: 1px solid #f1f5f9; padding-top: 22px;
    flex: 1;
}
.pricing-card__features li {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 13px; color: #374151; margin-bottom: 11px;
}
.pricing-card__features li i {
    color: #0d9488; font-size: 15px; margin-top: 1px;
    flex-shrink: 0;
}
.pricing-card__cta {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 8px; min-height: 46px;
    padding: 0 22px; border-radius: 11px;
    background: #f9fafb; border: 1px solid #e5e7eb;
    color: #0f172a; font-size: 14px; font-weight: 700;
    transition: all .15s;
}
.pricing-card__cta:hover { background: #f3f4f6; }
.pricing-card.featured .pricing-card__cta {
    background: #0d9488; border-color: #0d9488; color: #fff;
    box-shadow: 0 8px 20px rgba(13,148,136,.28);
}
.pricing-card.featured .pricing-card__cta:hover { background: #0f766e; border-color: #0f766e; }
</style>