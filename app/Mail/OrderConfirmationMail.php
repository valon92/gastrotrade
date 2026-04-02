<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $orderData;

    public $customerData;

    public $cartItems;

    public $totalItems;

    public $totalPrice;

    /**
     * Create a new message instance.
     */
    public function __construct($orderData, $customerData, $cartItems, $totalItems, $totalPrice)
    {
        $this->orderData = $orderData;
        $this->customerData = $customerData;
        $this->cartItems = $cartItems;
        $this->totalItems = $totalItems;
        $this->totalPrice = $totalPrice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $orderNumber = $this->orderData['order_number'] ?? 'N/A';
        $replyTo = [];
        $clientEmail = $this->customerData['email'] ?? null;
        if (is_string($clientEmail) && filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            $name = $this->customerData['name'] ?? null;
            $replyTo[] = new Address($clientEmail, is_string($name) && $name !== '' ? $name : null);
        }

        return new Envelope(
            subject: "Porosi e Re #{$orderNumber} - AronTrade",
            replyTo: $replyTo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
