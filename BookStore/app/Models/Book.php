<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory;

    protected $table = "books";
    protected $fillable = [
        'name',
        'description',
        'author',
        'image',
        'Price',
        'quantity'
    ];

    public function adminOrUserVerification($currentUserId)
    {
        $adminId = User::select('id')->where([['role', '=', 'admin'], ['id', '=', $currentUserId]])->get();
        return $adminId;
    }

    public function findBook($bookId)
    {
        $book = Book::where('id', $bookId)->first();
        return $book;
    }

    public function getBookDetails($bookName)
    {
        return Book::select('id', 'name', 'quantity', 'author', 'Price')
            ->where('name', '=', $bookName)
            ->first();
    }
    
    /**
     * Function to get book by bookID,
     * passing the bookID as parameter
     * 
     * return array
     */
    public static function getBookById($bookId)
    {
        $book = Book::where('id', $bookId)->first();
        return $book;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
