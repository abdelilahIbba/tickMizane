<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes (require authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    | Dashboard, Categories, Products, Fournisseurs, Stock, Commandes (supplier)
    */
    Route::middleware(['role:admin'])->group(function () {
        // Dashboard (admin only)
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Categories management
        Route::resource('categories', CategoryController::class);
        
        // Products management
        Route::resource('products', ProductController::class);
        Route::post('/products/{product}/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
        
        // Fournisseurs (Suppliers)
        Route::resource('fournisseurs', FournisseurController::class);
        
        // Stock Movements
        Route::resource('stock', StockController::class);
        Route::get('/stock/low-stock', [StockController::class, 'lowStock'])->name('stock.low-stock');
        
        // Commandes (Supplier Orders) - using OrderController
        Route::resource('orders', OrderController::class);
        Route::post('/orders/{commande}/receive', [OrderController::class, 'receive'])->name('orders.receive');
        Route::post('/orders/{commande}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        
        // Legacy Commandes routes (backward compatibility)
        Route::resource('commandes', CommandeController::class);
        Route::post('/commandes/{commande}/receive', [CommandeController::class, 'receive'])->name('commandes.receive');
        
        // Historique (Audit Logs)
        Route::get('/historiques', [HistoriqueController::class, 'index'])->name('historiques.index');
        Route::get('/historiques/{historique}', [HistoriqueController::class, 'show'])->name('historiques.show');
        Route::get('/historiques/record/{model}/{id}', [HistoriqueController::class, 'forRecord'])->name('historiques.record');
        Route::get('/historiques/export', [HistoriqueController::class, 'export'])->name('historiques.export');
        Route::get('/historiques/timeline/{model}/{id}', [HistoriqueController::class, 'timeline'])->name('historiques.timeline');
        
        // Notifications (Stock Alerts)
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Caissier Routes (+ Admin)
    |--------------------------------------------------------------------------
    | POS, Ventes, Paiements
    */
    Route::middleware(['role:caissier'])->group(function () {
        // POS
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        
        // Ventes (Sales)
        Route::get('/ventes', [VenteController::class, 'index'])->name('ventes.index');
        Route::get('/ventes/{vente}', [VenteController::class, 'show'])->name('ventes.show');
        Route::post('/ventes/{vente}/cancel', [VenteController::class, 'cancel'])->name('ventes.cancel');
        Route::post('/ventes/{vente}/payment', [VenteController::class, 'addPayment'])->name('ventes.add-payment');
        Route::get('/ventes/report', [VenteController::class, 'report'])->name('ventes.report');
        Route::get('/ventes/{vente}/receipt', [VenteController::class, 'receipt'])->name('ventes.receipt');
        
        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/report', [PaymentController::class, 'report'])->name('payments.report');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Serveur Routes (+ Admin)
    |--------------------------------------------------------------------------
    | Tables management
    */
    Route::middleware(['role:serveur'])->group(function () {
        // Tables CRUD
        Route::resource('tables', TableController::class);
        
        // Table Actions
        Route::post('/tables/{table}/status', [TableController::class, 'updateStatus'])->name('tables.status');
        Route::post('/tables/{table}/occupy', [TableController::class, 'occupy'])->name('tables.occupy');
        Route::post('/tables/{table}/release', [TableController::class, 'release'])->name('tables.release');
        Route::post('/tables/{table}/transfer', [TableController::class, 'transfer'])->name('tables.transfer');
        Route::post('/tables/{table}/assign-serveur', [TableController::class, 'assignServeur'])->name('tables.assign-serveur');
        
        // Table API (for AJAX)
        Route::get('/tables/{table}/bill', [TableController::class, 'getCurrentBill'])->name('tables.bill');
        Route::get('/tables-summary', [TableController::class, 'summary'])->name('tables.summary');
        Route::get('/tables-analytics', [TableController::class, 'analytics'])->name('tables.analytics');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Common Routes (All authenticated users)
    |--------------------------------------------------------------------------
    */
    // Notifications for all users
    Route::get('/my-notifications', [NotificationController::class, 'myNotifications'])->name('notifications.mine');
    Route::post('/my-notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    
});
