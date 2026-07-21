<?php

namespace App\Notifications;

use App\Models\Produit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * LowStockNotification - إشعار انخفاض المخزون
 *
 * يُرسل هذا الإشعار تلقائياً عندما يصل
 * مخزون منتج إلى حد التنبيه أو دونه.
 *
 * قنوات التسليم:
 * - database : يحفظ في جدول notifications لعرضه في الواجهة
 * - mail     : يرسل بريداً إلكترونياً للمدير
 *
 * يحتوي على اسم المنتج والكمية الحالية وحد التنبيه
 */
class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Produit $produit;

    /**
     * Create a new notification instance.
     */
    public function __construct(Produit $produit)
    {
        $this->produit = $produit;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Alerte Stock Bas - ' . $this->produit->name)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Le produit **' . $this->produit->name . '** a atteint un niveau de stock bas.')
            ->line('**Stock actuel:** ' . $this->produit->stock_quantity . ' ' . $this->produit->unit)
            ->line('**Seuil d\'alerte:** ' . $this->produit->alert_stock . ' ' . $this->produit->unit)
            ->line('**Catégorie:** ' . ($this->produit->category?->name ?? 'N/A'))
            ->action('Voir le produit', url('/products/' . $this->produit->id))
            ->line('Pensez à passer une commande auprès de votre fournisseur.')
            ->salutation('TechMizane Cash - Système de gestion');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'produit_id' => $this->produit->id,
            'produit_name' => $this->produit->name,
            'current_stock' => $this->produit->stock_quantity,
            'alert_threshold' => $this->produit->alert_stock,
            'unit' => $this->produit->unit,
            'category' => $this->produit->category?->name,
            'message' => "Stock bas pour {$this->produit->name}: {$this->produit->stock_quantity} {$this->produit->unit} (seuil: {$this->produit->alert_stock})",
        ];
    }
}
