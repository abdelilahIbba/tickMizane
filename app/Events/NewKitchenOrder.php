<?php

namespace App\Events;

use App\Models\Commande;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * NewKitchenOrder - حدث طلبية مطبخ جديدة
 *
 * يُطلق هذا الحدث عند إنشاء طلبية جديدة تحتاج تحضيراً في المطبخ.
 * يُبث عبر قناة 'kitchen' باستخدام Laravel Echo
 * ليستقبله شاشة المطبخ فوراً.
 *
 * البيانات المبثوثة:
 * - معرف الطلبية ورقم الطاولة واسم النادل
 * - عدد العناصر ووقت الإنشاء
 *
 * اسم الحدث المبث: 'new-order'
 */
class NewKitchenOrder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Commande $commande;

    /**
     * Create a new event instance.
     */
    public function __construct(Commande $commande)
    {
        $this->commande = $commande->load(['details.produit', 'table', 'user']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('kitchen');
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->commande->id,
            'table_number' => $this->commande->table?->numero,
            'table_name' => $this->commande->table?->name,
            'waiter_name' => $this->commande->user?->name,
            'items_count' => $this->commande->details->count(),
            'created_at' => $this->commande->created_at->format('H:i'),
            'message' => "Nouvelle commande pour la table {$this->commande->table?->numero}",
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'new-order';
    }
}
