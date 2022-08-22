<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOrderDetails extends Notification implements ShouldQueue
{
    use Queueable;

    public $orderId;
    public $bookName;
    public $bookAuthor;
    public $bookPrice;
    public $quantity;
    public $totalPrice;
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order, $book, $cart, $currentUser)
    {
        $this->user = $currentUser->first_name;
        $this->orderId = $order->order_id;
        $this->bookName = $book->name;
        $this->bookAuthor = $book->author;
        $this->bookPrice = $book->price;
        $this->quantity = $cart->book_quantity;
        $this->totalPrice = $order->total_price;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line($this->user . ' your order is confirmed.')
            ->line('Your Order Details : ')
            ->line('Order Id : ' . $this->orderId)
            ->line('Book Name : ' . $this->bookName)
            ->line('Book Author : ' . $this->bookAuthor)
            ->line('Book Price : ' . $this->bookPrice)
            ->line('Book Quantity : ' . $this->quantity)
            ->line('Total Payment : ' . $this->totalPrice)
            ->line('Save the OrderId For Further Communication')
            ->line('For Further Querry Contact This Email Id: ' . env('MAIL_USERNAME'))
            ->line('Thank you for using our Application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}