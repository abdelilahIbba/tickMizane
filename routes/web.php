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
use App\Http\Controllers\WaiterController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\CashierPosController;
use App\Http\Controllers\Settings\ArticlesController;
use App\Http\Controllers\Settings\UserManagementController;
use App\Http\Controllers\Settings\PermissionManagementController;
use App\Http\Controllers\Settings\SystemSettingsController;
use App\Http\Controllers\Settings\DocumentationController;
use App\Http\Controllers\MenuController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login');

Route::get('/menu/tv', [MenuController::class, 'tv'])->name('menu.tv');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Password Change Routes (exempt from ForcePasswordReset middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->withoutMiddleware([\App\Http\Middleware\ForcePasswordReset::class])->group(function () {
    Route::get('/password/change', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.change.submit');
});

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
        
        // Cashier - Pending Kitchen Orders
        Route::get('/cashier/pending', [CashierPosController::class, 'index'])->name('cashier.pending');
        Route::get('/cashier/order/{commande}', [CashierPosController::class, 'showOrder'])->name('cashier.show-order');
        Route::get('/cashier/order/{commande}/payment', [CashierPosController::class, 'show'])->name('cashier.payment');
        Route::post('/cashier/order/{commande}/payment', [CashierPosController::class, 'processPayment'])->name('cashier.process-payment');
        Route::get('/cashier/history', [CashierPosController::class, 'history'])->name('cashier.history');
        Route::get('/cashier/order/{commande}/receipt', [CashierPosController::class, 'printReceipt'])->name('cashier.receipt');
        Route::get('/cashier/pending-orders', [CashierPosController::class, 'getPendingOrders'])->name('cashier.pending-orders');
        Route::get('/cashier/stats', [CashierPosController::class, 'stats'])->name('cashier.stats');
        
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
    | Tables management, Waiter Tablet Interface
    */
    Route::middleware(['role:serveur'])->group(function () {
        // Waiter Tablet Interface
        Route::get('/waiter', [WaiterController::class, 'index'])->name('waiter.index');
        Route::get('/waiter/table/{table}/order', [WaiterController::class, 'showTableOrder'])->name('waiter.table.order');
        Route::post('/waiter/table/{table}/order', [WaiterController::class, 'storeOrder'])->name('waiter.order.store');
        Route::get('/waiter/orders', [WaiterController::class, 'myOrders'])->name('waiter.orders');
        Route::get('/waiter/order/{commande}', [WaiterController::class, 'showOrder'])->name('waiter.order.show');
        
        // AJAX endpoints
        Route::get('/waiter/category/{category}/products', [WaiterController::class, 'getProductsByCategory'])->name('waiter.category.products');
        Route::get('/waiter/table/{table}/check', [WaiterController::class, 'checkTable'])->name('waiter.table.check');
        
        // Tables CRUD
        Route::resource('tables', TableController::class);
        
        // Table Actions
        Route::post('/tables/{table}/status', [TableController::class, 'updateStatus'])->name('tables.status');
        Route::post('/tables/{table}/occupy', [TableController::class, 'occupy'])->name('tables.occupy');
        Route::post('/tables/{table}/release', [TableController::class, 'release'])->name('tables.release');
        Route::post('/tables/{table}/transfer', [TableController::class, 'transfer'])->name('tables.transfer');
        Route::post('/tables/{table}/cashout', [TableController::class, 'cashout'])->name('tables.cashout');
        Route::post('/tables/{table}/assign-serveur', [TableController::class, 'assignServeur'])->name('tables.assign-serveur');
        
        // Table API (for AJAX)
        Route::get('/tables/{table}/bill', [TableController::class, 'getCurrentBill'])->name('tables.bill');
        Route::get('/tables-summary', [TableController::class, 'summary'])->name('tables.summary');
        Route::get('/tables-analytics', [TableController::class, 'analytics'])->name('tables.analytics');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Kitchen Routes (Admin + Kitchen Staff)
    |--------------------------------------------------------------------------
    | Kitchen dashboard for viewing and managing orders
    */
    Route::middleware(['role:admin'])->group(function () {
        // Kitchen Dashboard
        Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
        Route::get('/kitchen/display', [KitchenController::class, 'display'])->name('kitchen.display');
        Route::get('/kitchen/order/{commande}', [KitchenController::class, 'show'])->name('kitchen.order.show');
        Route::post('/kitchen/order/{commande}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.order.status');
        Route::post('/kitchen/order/{commande}/ready', [KitchenController::class, 'markReady'])->name('kitchen.order.ready');
        Route::post('/kitchen/order/{commande}/served', [KitchenController::class, 'markServed'])->name('kitchen.order.served');
        Route::get('/kitchen/order/{commande}/ticket', [KitchenController::class, 'printTicket'])->name('kitchen.ticket');
        
        // AJAX endpoints
        Route::get('/kitchen/orders/active', [KitchenController::class, 'getActiveOrders'])->name('kitchen.orders.active');
        Route::get('/kitchen/stats', [KitchenController::class, 'stats'])->name('kitchen.stats');

        // Settings - User Management
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
            Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
            Route::get('/users/{user}/reset-password', [UserManagementController::class, 'showResetPassword'])->name('users.reset-password');
            Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password.submit');
            Route::post('/users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
            Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');

            // Permissions Management
            Route::get('/permissions', [PermissionManagementController::class, 'index'])->name('permissions.index');
            Route::get('/permissions/{user}', [PermissionManagementController::class, 'show'])->name('permissions.show');
            Route::post('/permissions/{user}', [PermissionManagementController::class, 'update'])->name('permissions.update');
            Route::post('/permissions/{user}/grant-all', [PermissionManagementController::class, 'grantAll'])->name('permissions.grant-all');
            Route::post('/permissions/{user}/revoke-all', [PermissionManagementController::class, 'revokeAll'])->name('permissions.revoke-all');

            // System Settings
            Route::get('/system', [SystemSettingsController::class, 'index'])->name('system.index');
            Route::get('/system/{group}', [SystemSettingsController::class, 'showGroup'])->name('system.group');
            Route::post('/system/{group}', [SystemSettingsController::class, 'updateGroup'])->name('system.update');
            Route::post('/system/{group}/reset', [SystemSettingsController::class, 'resetGroup'])->name('system.reset');

            // Articles (Catalogue menu — catégories & produits)
            Route::get('/articles', [ArticlesController::class, 'index'])->name('articles.index');
            Route::post('/articles/categories', [ArticlesController::class, 'storeCategory'])->name('articles.categories.store');
            Route::put('/articles/categories/{category}', [ArticlesController::class, 'updateCategory'])->name('articles.categories.update');
            Route::delete('/articles/categories/{category}', [ArticlesController::class, 'destroyCategory'])->name('articles.categories.destroy');
            Route::post('/articles/categories/{category}/products', [ArticlesController::class, 'storeProduct'])->name('articles.products.store');
            Route::put('/articles/products/{product}', [ArticlesController::class, 'updateProduct'])->name('articles.products.update');
            Route::delete('/articles/products/{product}', [ArticlesController::class, 'destroyProduct'])->name('articles.products.destroy');

            // Documentation Visibility (Admin)
            Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index');
            Route::post('/documentation/{documentation}/visibility', [DocumentationController::class, 'updateVisibility'])->name('documentation.updateVisibility');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Documentation Viewer (All authenticated users)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])->group(function () {
        Route::get('/docs', [\App\Http\Controllers\DocumentationController::class, 'index'])->name('docs.index');
        Route::get('/docs/{slug}', [\App\Http\Controllers\DocumentationController::class, 'show'])->name('docs.show');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Common Routes (All authenticated users)
    |--------------------------------------------------------------------------
    */
    // Notifications for all users
    Route::get('/my-notifications', [NotificationController::class, 'myNotifications'])->name('notifications.mine');
    Route::post('/my-notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/cashier/order/{commandeId}/receipt/print', [CashierPosController::class, 'showPrintableReceipt'])->name('cashier.receipt.print');
    
});
