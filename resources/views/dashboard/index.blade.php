<x-layout.app title="Dashboard">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Dashboard</h1>
            <p class="text-gray-400 mt-1">Vue d'ensemble de votre activité</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-ui.stat-card title="Ventes du jour" value="{{ number_format($todaySales, 2) }} DH" color="amber"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' />

            <x-ui.stat-card title="Transactions" value="{{ $todayTransactions }}" color="blue"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>' />

            <x-ui.stat-card title="Alertes stock" value="{{ $lowStockProducts }}" color="red"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>' />

            <x-ui.stat-card title="Commandes en attente" value="{{ $pendingOrders }}" color="emerald"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' />
        </div>

        {{-- Charts Row 1: Weekly Sales & Hourly Distribution --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Weekly Sales Chart --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Ventes de la semaine</h3>
                        <p class="text-sm text-gray-400">Revenus des 7 derniers jours</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 text-sm text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            {{ number_format(array_sum($weeklySales['sales']), 2) }} DH
                        </span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="weeklySalesChart"></canvas>
                </div>
            </div>

            {{-- Hourly Sales Chart --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Ventes par heure</h3>
                        <p class="text-sm text-gray-400">Distribution des ventes aujourd'hui</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-lg text-sm font-medium">
                        Aujourd'hui
                    </span>
                </div>
                <div class="h-64">
                    <canvas id="hourlySalesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Charts Row 2: Monthly Revenue & Category Distribution --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Monthly Revenue Chart --}}
            <div class="lg:col-span-2 bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Revenus mensuels</h3>
                        <p class="text-sm text-gray-400">Performance des 6 derniers mois</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-amber-400">
                            {{ number_format(array_sum($monthlyRevenue['revenue']), 2) }} DH</p>
                        <p class="text-xs text-gray-400">Total période</p>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>

            {{-- Sales by Category (Doughnut) --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white">Ventes par catégorie</h3>
                    <p class="text-sm text-gray-400">Distribution du chiffre d'affaires</p>
                </div>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Charts Row 3: Top Products & Payment Methods --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Top Products Chart --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white">Top 5 Produits</h3>
                    <p class="text-sm text-gray-400">Produits les plus vendus</p>
                </div>
                <div class="h-64">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            {{-- Payment Methods Chart --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700/50 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white">Méthodes de paiement</h3>
                    <p class="text-sm text-gray-400">Répartition des paiements</p>
                </div>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">Actions rapides</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" href="{{ route('pos.index') }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nouvelle vente
                </x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('products.create') }}">
                    Ajouter un produit
                </x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('commandes.create') }}">
                    Nouvelle commande
                </x-ui.button>
                <x-ui.button variant="info" href="{{ route('stock.index') }}">
                    Voir le stock
                </x-ui.button>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Recent Sales --}}
            <div class="lg:col-span-2">
                <x-ui.card title="Ventes récentes" subtitle="Dernières transactions">
                    <div class="space-y-4">
                        @forelse($recentSales as $vente)
                            <div class="flex items-center justify-between p-4 bg-gray-900 rounded-xl">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $vente->label ?? ('Vente #' . str_pad($vente->id, 4, '0', STR_PAD_LEFT)) }}</p>
                                        <p class="text-sm text-gray-400">{{ $vente->created_at->format('d/m/Y H:i') }} -
                                            {{ $vente->user->name ?? $vente->user_name ?? 'Client' }}</p>
                                    </div>
                                </div>
                                <span class="text-lg font-semibold text-amber-400">{{ number_format($vente->total, 2) }}
                                    DH</span>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p>Aucune vente récente</p>
                            </div>
                        @endforelse
                    </div>

                    <x-slot:footer>
                        <a href="{{ route('ventes.index') }}"
                            class="text-amber-400 hover:text-amber-300 text-sm font-medium">
                            Voir toutes les ventes →
                        </a>
                    </x-slot:footer>
                </x-ui.card>
            </div>

            {{-- Stock Alerts --}}
            <div>
                <x-ui.card title="Alertes stock" subtitle="Produits à réapprovisionner">
                    <div class="space-y-3">
                        @forelse($lowStockList as $product)
                            <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-white">{{ $product->name }}</p>
                                        <p class="text-sm text-gray-400">Stock: {{ $product->stock_quantity }} / Alerte:
                                            {{ $product->alert_stock }}</p>
                                    </div>
                                    <x-ui.badge variant="danger">Bas</x-ui.badge>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-emerald-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-emerald-400">Tous les stocks sont OK</p>
                            </div>
                        @endforelse
                    </div>

                    <x-slot:footer>
                        <a href="{{ route('stock.index') }}"
                            class="text-amber-400 hover:text-amber-300 text-sm font-medium">
                            Gérer le stock →
                        </a>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            // Chart.js Global Configuration for Dark Theme
            Chart.defaults.color = '#9CA3AF';
            Chart.defaults.borderColor = 'rgba(75, 85, 99, 0.3)';
            Chart.defaults.font.family = "'Inter', 'system-ui', 'sans-serif'";

            // Color Palette
            const colors = {
                amber: {
                    main: 'rgb(245, 158, 11)',
                    light: 'rgba(245, 158, 11, 0.2)',
                    gradient: ['rgba(245, 158, 11, 0.8)', 'rgba(245, 158, 11, 0.1)']
                },
                blue: {
                    main: 'rgb(59, 130, 246)',
                    light: 'rgba(59, 130, 246, 0.2)',
                    gradient: ['rgba(59, 130, 246, 0.8)', 'rgba(59, 130, 246, 0.1)']
                },
                emerald: {
                    main: 'rgb(16, 185, 129)',
                    light: 'rgba(16, 185, 129, 0.2)',
                    gradient: ['rgba(16, 185, 129, 0.8)', 'rgba(16, 185, 129, 0.1)']
                },
                purple: {
                    main: 'rgb(139, 92, 246)',
                    light: 'rgba(139, 92, 246, 0.2)'
                },
                rose: {
                    main: 'rgb(244, 63, 94)',
                    light: 'rgba(244, 63, 94, 0.2)'
                },
                cyan: {
                    main: 'rgb(6, 182, 212)',
                    light: 'rgba(6, 182, 212, 0.2)'
                }
            };

            const categoryColors = [
                colors.amber.main,
                colors.blue.main,
                colors.emerald.main,
                colors.purple.main,
                colors.rose.main,
                colors.cyan.main,
                'rgb(234, 179, 8)',
                'rgb(99, 102, 241)'
            ];

            // Weekly Sales Chart (Line + Bar Combined)
            const weeklySalesCtx = document.getElementById('weeklySalesChart').getContext('2d');
            const weeklySalesGradient = weeklySalesCtx.createLinearGradient(0, 0, 0, 250);
            weeklySalesGradient.addColorStop(0, colors.amber.gradient[0]);
            weeklySalesGradient.addColorStop(1, colors.amber.gradient[1]);

            new Chart(weeklySalesCtx, {
                type: 'bar',
                data: {
                    labels: @json($weeklySales['labels']),
                    datasets: [{
                        label: 'Ventes (DH)',
                        data: @json($weeklySales['sales']),
                        backgroundColor: weeklySalesGradient,
                        borderColor: colors.amber.main,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#F9FAFB',
                            bodyColor: '#D1D5DB',
                            borderColor: 'rgba(75, 85, 99, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: ctx => `${ctx.parsed.y.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} DH`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { weight: '500' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(75, 85, 99, 0.2)' },
                            ticks: {
                                callback: value => value.toLocaleString() + ' DH'
                            }
                        }
                    }
                }
            });

            // Hourly Sales Chart (Area)
            const hourlySalesCtx = document.getElementById('hourlySalesChart').getContext('2d');
            const hourlyGradient = hourlySalesCtx.createLinearGradient(0, 0, 0, 250);
            hourlyGradient.addColorStop(0, colors.blue.gradient[0]);
            hourlyGradient.addColorStop(1, colors.blue.gradient[1]);

            new Chart(hourlySalesCtx, {
                type: 'line',
                data: {
                    labels: @json($hourlySales['labels']),
                    datasets: [{
                        label: 'Ventes (DH)',
                        data: @json($hourlySales['data']),
                        fill: true,
                        backgroundColor: hourlyGradient,
                        borderColor: colors.blue.main,
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: colors.blue.main,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#F9FAFB',
                            bodyColor: '#D1D5DB',
                            borderColor: 'rgba(75, 85, 99, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: ctx => `${ctx.parsed.y.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} DH`
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 8
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(75, 85, 99, 0.2)' },
                            ticks: {
                                callback: value => value.toLocaleString() + ' DH'
                            }
                        }
                    }
                }
            });

            // Monthly Revenue Chart (Line with Area)
            const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
            const monthlyGradient = monthlyRevenueCtx.createLinearGradient(0, 0, 0, 280);
            monthlyGradient.addColorStop(0, colors.emerald.gradient[0]);
            monthlyGradient.addColorStop(1, colors.emerald.gradient[1]);

            new Chart(monthlyRevenueCtx, {
                type: 'line',
                data: {
                    labels: @json($monthlyRevenue['labels']),
                    datasets: [{
                        label: 'Revenus (DH)',
                        data: @json($monthlyRevenue['revenue']),
                        fill: true,
                        backgroundColor: monthlyGradient,
                        borderColor: colors.emerald.main,
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: colors.emerald.main,
                        pointBorderColor: '#1F2937',
                        pointBorderWidth: 3,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#F9FAFB',
                            bodyColor: '#D1D5DB',
                            borderColor: 'rgba(75, 85, 99, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: ctx => `${ctx.parsed.y.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} DH`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { weight: '500' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(75, 85, 99, 0.2)' },
                            ticks: {
                                callback: value => (value / 1000).toFixed(0) + 'K DH'
                            }
                        }
                    }
                }
            });

            // Sales by Category (Doughnut)
            new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($salesByCategory['labels']),
                    datasets: [{
                        data: @json($salesByCategory['data']),
                        backgroundColor: categoryColors.slice(0, @json(count($salesByCategory['labels']))),
                        borderColor: '#1F2937',
                        borderWidth: 3,
                        hoverBorderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 15,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#F9FAFB',
                            bodyColor: '#D1D5DB',
                            borderColor: 'rgba(75, 85, 99, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                    return `${ctx.parsed.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} DH (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Top Products Chart (Horizontal Bar)
            new Chart(document.getElementById('topProductsChart'), {
                type: 'bar',
                data: {
                    labels: @json($topProducts['labels']),
                    datasets: [{
                        label: 'Quantité vendue',
                        data: @json($topProducts['quantities']),
                        backgroundColor: [
                            colors.amber.main,
                            colors.blue.main,
                            colors.emerald.main,
                            colors.purple.main,
                            colors.rose.main
                        ],
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#F9FAFB',
                            bodyColor: '#D1D5DB',
                            borderColor: 'rgba(75, 85, 99, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                afterLabel: (ctx) => {
                                    const revenues = @json($topProducts['revenues']);
                                    return `Revenu: ${revenues[ctx.dataIndex]?.toLocaleString('fr-FR', { minimumFractionDigits: 2 }) || 0} DH`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(75, 85, 99, 0.2)' }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { weight: '500' },
                                callback: function (value) {
                                    const label = this.getLabelForValue(value);
                                    return label.length > 15 ? label.substr(0, 15) + '...' : label;
                                }
                            }
                        }
                    }
                }
            });

            // Payment Methods Chart (Polar Area)
            new Chart(document.getElementById('paymentMethodsChart'), {
                type: 'polarArea',
                data: {
                    labels: @json($paymentMethods['labels']),
                    datasets: [{
                        data: @json($paymentMethods['totals']),
                        backgroundColor: [
                            colors.emerald.light,
                            colors.blue.light,
                            colors.purple.light,
                            colors.amber.light
                        ],
                        borderColor: [
                            colors.emerald.main,
                            colors.blue.main,
                            colors.purple.main,
                            colors.amber.main
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 15,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#F9FAFB',
                            bodyColor: '#D1D5DB',
                            borderColor: 'rgba(75, 85, 99, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => `${ctx.parsed.r.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} DH`
                            }
                        }
                    },
                    scales: {
                        r: {
                            ticks: { display: false },
                            grid: { color: 'rgba(75, 85, 99, 0.2)' }
                        }
                    }
                }
            });
        });
    </script>
</x-layout.app>