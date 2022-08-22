<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    protected $fillable = [
        'user_id',
        'cart_id',
        'address_id',
        'book_name',
        'book_author',
        'book_price',
        'book_quantity',
        'total_price',
        'order_id'
    ];

    /**
     * Function to place new order with the credentials provided,
     * taking user request, currentUser, book and cart as credentials
     * 
     * return array
     */
    public static function placeOrder($request, $currentUser, $book, $cart)
    {
        $total_price = $book->price * $cart->book_quantity;
        $order = new Order();
        $order->user_id = $currentUser->id;
        $order->cart_id = $request->cart_id;
        $order->address_id = $request->address_id;
        $order->book_name = $book->name;
        $order->book_author = $book->author;
        $order->book_price = $book->price;
        $order->book_quantity = $cart->book_quantity;
        $order->total_price = $total_price;
        $order->order_id = $order->unique_code(9);
        $order->save();

        return $order;        
    }

    /**
     * Function to get order by cartID
     * passing cartID as parameter
     * 
     * return array
     */
    public static function getOrderByCartId($cartId)
    {
        $order = Order::where('cart_id', $cartId)->first();

        return $order;
    }

    public static function getOrderByUserId($userId)
    {
        $order = Order::where('user_id', $userId)->get();

        return $order;
    }

    public static function getOrderByIDandUserID($ordersId, $userID)
    {
        $order = Order::where('id', $ordersId)->where('user_id', $userID)->first();

        return $order;
    }

    /**
     * Function to get order by orderID and userID
     * passing orderID and userID as parameters
     * 
     * return array
     */
    public static function getOrderByOrderID($orderID, $userID)
    {
        $order = Order::where('order_id', $orderID)->where('user_id', $userID)->first();

        return $order;
    }

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function book()
    {
        return $this->belongsTo(Book::class);
    }


    /**
     * base_convert – Convert a number between arbitrary bases
     * sha1 – Calculate the sha1 hash of a string.
     * uniqid – Generate a unique ID.
     * mt_rand – Generate a random value via the Mersenne Twister Random Number Generator.
     */
    public function unique_code($limit)
    {
        return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
    }
    
}