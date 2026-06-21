<!DOCTYPE html>
<html lang="fr" class="bg-[#f6f2e8]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Techmizane</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --paper: #f6f2e8;
            --paper-strong: #fffdf8;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #e7dcc7;
            --brand: #c1750f;
            --brand-soft: #f9e9cf;
            --price: #0f766e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', sans-serif;
            margin: 0;
            overflow: hidden;
            color: var(--ink);
            background:
                radial-gradient(circle at 5% -20%, rgba(251, 191, 36, 0.25), transparent 38%),
                radial-gradient(circle at 100% 120%, rgba(15, 118, 110, 0.15), transparent 35%),
                linear-gradient(180deg, #fdfbf5 0%, var(--paper) 100%);
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 40;
            height: 98px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2.5rem;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 253, 248, 0.92);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 24px -20px rgba(0, 0, 0, 0.35);
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 0.95rem;
        }

        .logo {
            width: 52px;
            height: 52px;
            filter: drop-shadow(0 6px 14px rgba(193, 117, 15, 0.24));
        }

        .brand-title {
            margin: 0;
            line-height: 1;
            font-size: 2.2rem;
            font-weight: 800;
            color: #101827;
            letter-spacing: -0.03em;
        }

        .brand-title span {
            color: var(--brand);
        }

        .brand-sub {
            margin-top: 0.18rem;
            font-size: 0.95rem;
            color: #8b95a5;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .top-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .live-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: #fff2dc;
            border: 1px solid #f4d6a5;
            color: #9a5b0b;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .live-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: #ef4444;
            box-shadow: 0 0 0 5px rgba(239, 68, 68, 0.13);
            animation: pulse-dot 1.8s infinite;
        }

        @keyframes pulse-dot {
            0% {
                transform: scale(0.85);
                opacity: 0.9;
            }
            50% {
                transform: scale(1.12);
                opacity: 1;
            }
            100% {
                transform: scale(0.85);
                opacity: 0.9;
            }
        }

        .clock {
            font-size: 2.1rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.02em;
        }

        .ticker {
            height: 100vh;
            padding-top: 98px;
            overflow: hidden;
            position: relative;
        }

        .ticker::before,
        .ticker::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            height: 80px;
            pointer-events: none;
            z-index: 20;
        }

        .ticker::before {
            top: 98px;
            background: linear-gradient(to bottom, rgba(246, 242, 232, 0.95), rgba(246, 242, 232, 0));
        }

        .ticker::after {
            bottom: 0;
            background: linear-gradient(to top, rgba(246, 242, 232, 0.98), rgba(246, 242, 232, 0));
        }

        .ticker-track {
            --scroll-distance: -1200px;
            transform: translateY(0);
        }

        .ticker-track.is-running {
            animation: menu-scroll linear infinite;
            animation-duration: 80s;
        }

        @keyframes menu-scroll {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(var(--scroll-distance));
            }
        }

        .menu-stack {
            width: min(94vw, 1800px);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            padding: 1.2rem 0 2.8rem;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid var(--line);
            border-left: 9px solid var(--brand);
            border-radius: 24px;
            padding: 1.35rem 1.4rem 1.45rem;
            box-shadow: 0 16px 32px -28px rgba(27, 36, 56, 0.35);
        }

        .category-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .category-main {
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .category-cover {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid #ead9b8;
            background: #fff;
        }

        .category-title {
            margin: 0;
            font-size: 2.1rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            font-weight: 800;
            color: #1f2937;
        }

        .category-meta {
            margin-top: 0.2rem;
            color: var(--muted);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .category-count {
            flex-shrink: 0;
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            font-size: 0.86rem;
            font-weight: 700;
            color: #9a5b0b;
            border: 1px solid #f1d2a4;
            background: var(--brand-soft);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .item-row {
            display: grid;
            grid-template-columns: 76px 1fr auto;
            align-items: center;
            gap: 0.85rem;
            min-height: 98px;
            border-radius: 16px;
            padding: 0.7rem 0.8rem;
            border: 1px solid #ece4d2;
            background: rgba(255, 255, 255, 0.94);
        }

        .item-image {
            width: 76px;
            height: 76px;
            border-radius: 14px;
            object-fit: cover;
            border: 1px solid #e8d9bd;
            background: #fff;
        }

        .item-name {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.15;
            color: #1f2937;
        }

        .item-unit {
            margin-top: 0.2rem;
            color: #7a8596;
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .price-pill {
            border-radius: 12px;
            padding: 0.52rem 0.78rem;
            background: #e8f7f3;
            border: 1px solid #b8e2d8;
            color: var(--price);
            font-size: 1.48rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .price-pill small {
            font-size: 0.74em;
            opacity: 0.8;
            margin-left: 0.15rem;
        }

        .empty-state {
            text-align: center;
            padding: 2.4rem;
            border-radius: 18px;
            border: 1px dashed #d1c3aa;
            color: #7a8596;
            font-size: 1.15rem;
            background: rgba(255, 255, 255, 0.55);
        }

        @media (max-width: 1100px) {
            .topbar {
                height: 86px;
                padding: 0.8rem 1.1rem;
            }

            .ticker {
                padding-top: 86px;
            }

            .ticker::before {
                top: 86px;
            }

            .brand-title {
                font-size: 1.7rem;
            }

            .clock {
                font-size: 1.55rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .category-title {
                font-size: 1.55rem;
            }

            .item-name {
                font-size: 1.15rem;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand-wrap">
            <svg class="logo" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 8C6 5.79086 7.79086 4 10 4H30C32.2091 4 34 5.79086 34 8V12C34 14.2091 32.2091 16 30 16H26V32C26 34.2091 24.2091 36 22 36H18C15.7909 36 14 34.2091 14 32V16H10C7.79086 16 6 14.2091 6 12V8Z" fill="#c1750f" />
                <rect x="24" y="8" width="4" height="4" rx="1" fill="#000000" fill-opacity="0.2"/>
            </svg>
            <div>
                <h1 class="brand-title">Techmizane <span>Menu</span></h1>
                <div class="brand-sub">Affichage TV en continu</div>
            </div>
        </div>
        <div class="top-meta">
            <span class="live-chip">
                <span class="live-dot"></span>
                En direct
            </span>
            <div id="clock" class="clock">--:--</div>
        </div>
    </header>

    <main class="ticker">
        <div class="ticker-track" id="tickerTrack">
            <section class="menu-stack">
                @forelse($categories as $category)
                    @if($category->produits->count() > 0)
                        <article class="category-card">
                            <div class="category-head">
                                <div class="category-main">
                                    <img
                                        src="{{ $category->display_image_url }}"
                                        alt="{{ $category->name }}"
                                        class="category-cover"
                                        onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=180&q=80'"
                                    >
                                    <div>
                                        <h2 class="category-title">{{ $category->name }}</h2>
                                        <div class="category-meta">Sélection disponible aujourd'hui</div>
                                    </div>
                                </div>
                                <div class="category-count">{{ $category->produits->count() }} articles</div>
                            </div>

                            <div class="products-grid">
                                @foreach($category->produits as $product)
                                    <div class="item-row">
                                        <img
                                            src="{{ $product->display_image_url }}"
                                            alt="{{ $product->name }}"
                                            class="item-image"
                                            onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=180&q=80'"
                                        >
                                        <div>
                                            <p class="item-name">{{ $product->name }}</p>
                                            <div class="item-unit">{{ strtoupper($product->unit ?? 'pcs') }}</div>
                                        </div>
                                        <div class="price-pill">{{ number_format($product->price_vente, 2) }} <small>DH</small></div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endif
                @empty
                    <div class="empty-state">Aucune catégorie active à afficher pour le moment.</div>
                @endforelse
            </section>
        </div>
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
            });
        }

        function initAutoScroll() {
            const track = document.getElementById('tickerTrack');
            const source = track.dataset.sourceHtml || track.innerHTML;

            track.dataset.sourceHtml = source;
            track.classList.remove('is-running');
            track.innerHTML = `<div class="loop">${source}</div><div class="loop" aria-hidden="true">${source}</div>`;

            const firstLoop = track.querySelector('.loop');
            if (!firstLoop) {
                return;
            }

            const scrollDistance = firstLoop.offsetHeight;
            const pixelsPerSecond = 42;
            const duration = Math.max(50, Math.round(scrollDistance / pixelsPerSecond));

            track.style.setProperty('--scroll-distance', `-${scrollDistance}px`);
            track.style.animationDuration = `${duration}s`;
            track.classList.add('is-running');
        }

        updateClock();
        setInterval(updateClock, 1000);

        window.addEventListener('load', initAutoScroll);
        window.addEventListener('resize', () => {
            clearTimeout(window.__menuResizeTimer);
            window.__menuResizeTimer = setTimeout(initAutoScroll, 200);
        });
    </script>
</body>
</html>