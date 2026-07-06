<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;

class OrderNotificationService
{
    private const TYPE = 'pengiriman';

    public function orderCreated(Order $order): void
    {
        $order->loadMissing(['buyer', 'farmer']);

        $this->sendOnce(
            userId: $order->farmer_id,
            tipe: self::TYPE,
            judul: 'Pesanan baru masuk',
            pesan: "Pesanan #{$order->order_number} dari {$order->buyer?->name} berhasil dibuat dan menunggu pembayaran.",
            link: route('farmer.orders.show', $order),
        );
    }

    public function paymentPaid(Order $order): void
    {
        $order->loadMissing(['buyer', 'farmer']);

        $this->sendOnce(
            userId: $order->buyer_id,
            tipe: self::TYPE,
            judul: 'Pembayaran berhasil',
            pesan: "Pembayaran untuk pesanan #{$order->order_number} sudah diterima.",
            link: route('buyer.orders.show', $order),
        );

        $this->sendOnce(
            userId: $order->farmer_id,
            tipe: self::TYPE,
            judul: 'Pesanan sudah dibayar',
            pesan: "Pesanan #{$order->order_number} dari {$order->buyer?->name} sudah dibayar dan siap diproses.",
            link: route('farmer.orders.show', $order),
        );
    }

    public function orderConfirmed(Order $order): void
    {
        $this->sendOnce(
            userId: $order->buyer_id,
            tipe: self::TYPE,
            judul: 'Pesanan dikonfirmasi',
            pesan: "Pesanan #{$order->order_number} sudah dikonfirmasi oleh petani.",
            link: route('buyer.orders.show', $order),
        );
    }

    public function orderProcessing(Order $order): void
    {
        $this->sendOnce(
            userId: $order->buyer_id,
            tipe: self::TYPE,
            judul: 'Pesanan diproses',
            pesan: "Pesanan #{$order->order_number} sedang diproses oleh petani.",
            link: route('buyer.orders.show', $order),
        );
    }

    public function orderShipped(Order $order): void
    {
        $message = "Pesanan #{$order->order_number} sedang dikirim.";

        if ($order->tracking_number) {
            $message .= " Nomor resi: {$order->tracking_number}.";
        }

        $this->sendOnce(
            userId: $order->buyer_id,
            tipe: self::TYPE,
            judul: 'Pesanan dikirim',
            pesan: $message,
            link: route('buyer.orders.show', $order),
        );
    }

    public function orderDelivered(Order $order): void
    {
        $this->sendOnce(
            userId: $order->buyer_id,
            tipe: self::TYPE,
            judul: 'Pesanan terkirim',
            pesan: "Pesanan #{$order->order_number} sudah tercatat terkirim.",
            link: route('buyer.orders.show', $order),
        );
    }

    public function orderCompleted(Order $order): void
    {
        $this->sendOnce(
            userId: $order->farmer_id,
            tipe: self::TYPE,
            judul: 'Pesanan diselesaikan',
            pesan: "Pesanan #{$order->order_number} sudah diselesaikan pembeli.",
            link: route('farmer.orders.show', $order),
        );

        $this->sendOnce(
            userId: $order->buyer_id,
            tipe: self::TYPE,
            judul: 'Pesanan selesai',
            pesan: "Pesanan #{$order->order_number} sudah selesai. Terima kasih sudah bertransaksi di Agrilink.",
            link: route('buyer.orders.show', $order),
        );
    }

    public function orderCancelled(Order $order): void
    {
        $order->loadMissing(['buyer', 'farmer']);

        $this->sendOnce(
            userId: $order->farmer_id,
            tipe: self::TYPE,
            judul: 'Pesanan dibatalkan',
            pesan: "Pesanan #{$order->order_number} dibatalkan oleh pembeli.",
            link: route('farmer.orders.show', $order),
        );

        $this->sendOnce(
            userId: $order->buyer_id,
            tipe: self::TYPE,
            judul: 'Pesanan dibatalkan',
            pesan: "Pesanan #{$order->order_number} sudah dibatalkan.",
            link: route('buyer.orders.show', $order),
        );
    }

    private function sendOnce(int $userId, string $tipe, string $judul, string $pesan, ?string $link): void
    {
        Notification::query()->firstOrCreate(
            [
                'user_id' => $userId,
                'tipe' => $tipe,
                'judul' => $judul,
                'link' => $link,
            ],
            [
                'pesan' => $pesan,
                'dibaca' => false,
            ]
        );
    }
}
