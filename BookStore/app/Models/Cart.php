<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table="carts";
    protected $fillable = [
        'book_id',
        'book_quantity',
        'user_id'
    ];

    public function getBooks($currentUser)
    {
        $books = Cart::leftJoin('books', 'carts.book_id', '=', 'books.id')
                     ->select('books.id', 'books.name', 'books.author', 'books.description', 'books.price', 'carts.book_quantity')
                     ->where('carts.user_id', '=', $currentUser->id)->get();
        return $books;             
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCartId($cart_id){
        $result = Cart::find($cart_id);
        return $result;
    }

    public function userVerification($currentUserId)
    {
        $userId = User::select('id')->where([['role', '=', 'user'], ['id', '=', $currentUserId]])->get();
        return $userId;
    }

        /**
     * Function to get book from the cart by cartID and userID,
     * passing the required credentials as parameters
     * 
     * return array
     */
    public static function getCartByIdandUserId($cartId, $userId){
        $cart = Cart::where('id', $cartId)->where('user_id', $userId)->first();

        return $cart;
    }

}
