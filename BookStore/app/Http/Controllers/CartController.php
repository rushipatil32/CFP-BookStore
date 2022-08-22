<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use App\Models\Book;
use App\Models\Cart;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Notifications\sendOrderDetails;



class CartController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/addBookToCartByBookId",
     *   summary="Add Book to cart",
     *   description="User Can Add Book to cart ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"book_id"},
     *               @OA\Property(property="book_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book added to Cart Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     * */
    public function addBookToCartByBookId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $cart = new Cart();
            $book = new Book();
            $user = new User();
            $userId = $user->userVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'Your are not an User'], 404);
            }
            if ($currentUser) {
                $book_id = $request->input('book_id');
                $book_existance = $book->findBook($book_id);

                if (!$book_existance) {
                    return response()->json([
                        'status' => 404,
                        'message' => 'Book not Found'
                    ], 404);
                }
                $books = $book->findBook($book_id);
                if ($books->quantity == 0) {
                    return response()->json([
                        'status' => 404,
                        'message' => 'OUT OF STOCK'
                    ], 404);
                }
                $book_cart = $cart->bookCart($book_id, $currentUser->id);
                if ($book_cart) {
                    return response()->json([
                        'status' => 404,
                        'message' => 'Book already added in cart'
                    ], 404);
                }
                $cart->book_id = $request->get('book_id');

                if ($currentUser->carts()->save($cart)) {
                    Cache::remember('carts', 3600, function () {
                        return DB::table('carts')->get();
                    });
                    return response()->json([
                        'message' => 'Book added to Cart Sucessfully'
                    ], 201);
                }
                return response()->json(['message' => 'Book cannot be added to Cart'], 405);
            } else {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     * @OA\Post(
     *   path="/api/deleteBookByCartId",
     *   summary="Delete the book from cart",
     *   description=" Delete cart ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book deleted Sucessfully from cart"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     * */
    public function deleteBookByCartId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $user = new User();
            $userId = $user->userVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json([
                    'status' => 404,
                    'message' => 'You are not an User'
                ], 404);
            }
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
            $book = $currentUser->carts()->find($id);
            if (!$book) {
                Log::error('Book Not Found', ['id' => $request->id]);
                return response()->json(['message' => 'Book not Found in cart'], 404);
            }

            if ($book->delete()) {
                Log::info('book deleted', ['user_id' => $currentUser, 'book_id' => $request->id]);
                Cache::forget('carts');
                return response()->json(['message' => 'Book deleted Sucessfully from cart'], 201);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     * @OA\Get(
     *   path="/api/getAllBooksByUserId",
     *   summary="Get All Books Present in Cart",
     *   description=" Get All Books Present in Cart ",
     *   @OA\RequestBody(
     *
     *    ),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     * */
    public function getAllBooksByUserId()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $user = new User();
            $userId = $user->userVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'Your are not an User'], 404);
            }
            if ($currentUser) {
                $books = new Cart();
                Log::info('All book Presnet in cart are fetched');
                return response()->json([
                    'message' => 'Books Present in Cart :',
                    'Cart' => $books->getBooks($currentUser)

                ], 201);
                if ($books == '[]') {
                    Log::error('Book Not Found');
                    return response()->json(['message' => 'Books not found'], 404);
                }
            } else {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }

    /**
     * @OA\Post(
     *   path="/api/addAddress",
     *   summary="Add Address",
     *   description="User Can Add Address ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"address","city","state","landmark", "pincode", "address_type"},
     *               @OA\Property(property="address", type="string"),
     *               @OA\Property(property="city", type="string"),
     *               @OA\Property(property="state", type="string"),
     *               @OA\Property(property="landmark", type="string"),
     *               @OA\Property(property="pincode", type="integer"),
     *               @OA\Property(property="address_type", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Address Added Successfully"),
     *   @OA\Response(response=401, description="Address alredy present for the user"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     * 
     * This method will take input address,city,state,landmark,pincode and addresstype from user
     * and will store in the database for the respective user
     */
    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|between:2,600',
            'city' => 'required|string|between:2,100',
            'state' => 'required|string|between:2,100',
            'landmark' => 'required|string|between:2,100',
            'pincode' => 'required|integer',
            'address_type' => 'required|string|between:2,100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $address = new Address();
                $address->user_id = $currentUser->id;
                $address->address = $request->input('address');
                $address->city = $request->input('city');
                $address->state = $request->input('state');
                $address->landmark = $request->input('landmark');
                $address->pincode = $request->input('pincode');
                $address->address_type = $request->input('address_type');
                $address->save();
                Log::info('Address Added To Respective User', ['user_id', '=', $currentUser->id]);
                return response()->json([
                    'message' => ' Address Added Successfully'
                ], 201);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }

    /**
     * @OA\Post(
     *   path="/api/updateBookQuantityInCart",
     *   summary="Add Quantity to Existing Book in cart",
     *   description=" Add Book Quantity  in cart",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "book_quantity"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="book_quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book Quantity updated Successfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function updateBookQuantityInCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'book_quantity' => 'required|integer|min:1'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $cartObject = new Cart();
            $userId = $cartObject->userVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
            $cart_id = $request->id;
            $cart = $cartObject->getCartId($cart_id);
            if (!$cart) {
                return response()->json([
                    'message' => 'Item Not found with this id'
                ], 404);
            }
            $cart->book_quantity += $request->book_quantity;
            $cart->save();
            Log::info('Book Quantity updated Successfully to the bookstore cart');
            return response()->json([
                'message' => 'Book Quantity updated Successfully'
            ], 201);
        } catch (BookStoreException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->message()]);
        }
    }


    /**
     * @OA\Post(
     *   path="/api/placeOrder",
     *   summary="Place  Order",
     *   description=" Place a order ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"address_id","name", "quantity"},
     *               @OA\Property(property="address_id", type="integer"),
     *               @OA\Property(property="name", type="string"),
     *               @OA\Property(property="quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Order Successfully Placed..."),
     *   @OA\Response(response=401, description="We Do not have this book in the store..."),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
            'cart_id' => 'required|integer',


        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $book = new Book();
                $address = new Address();
                $cart = Cart::getCartByIdandUserId($request->cart_id, $currentUser->id);
                $book = Book::getBookById($cart->book_id);

                if ($cart == '') {
                    Log::error('Book is not available');
                    throw new BookStoreException("We Do not have this book in the store...", 401);
                }

                if ($cart['quantity'] < $request->input('quantity')) {
                    Log::error('Book stock is not available');
                    throw new BookStoreException("This much stock is unavailable for the book", 401);
                }

                //getting addressID
                $getAddress = $address->addressExist($request->input('address_id'));
                if (!$getAddress) {
                    throw new BookStoreException("This address id not available", 401);
                }

                //calculate total price
                $total_price = $book->price * $cart->book_quantity;

                $order = Order::placeOrder($request, $currentUser, $book, $cart);
                $userId = User::where('id', $currentUser->id)->first();

                $delay = now()->addSeconds(5);
                $userId->notify((new sendOrderDetails($order->order_id, $cart['name'], $cart['author'], $request->input('quantity'), $total_price))->delay($delay));

                $book->quantity  -= $cart->book_quantity;
                $book->save();
                return response()->json([
                    'message' => 'Order Successfully Placed...',
                    'OrderId' => $order->order_id,
                    'Total_Price' => $total_price,
                    'message' => 'Mail sent to the user with all details',
                ], 201);
                Cache::remember('orders', 3600, function () {
                    return DB::table('orders')->get();
                });
                // if ($order) {
                //     $book->quantity  -= $cart->book_quantity;
                //     $book->save();

                //     $delay = now()->addSeconds(600);
                //     $currentUser->notify((new SendOrderDetails($order, $book, $cart, $currentUser))->delay($delay));

                //     Log::info('Order Placed Successfully');
                //     Cache::remember('orders', 3600, function () {
                //         return DB::table('orders')->get();
                //     });

                //     return response()->json([
                //         'message' => 'Order Placed Successfully',
                //         'OrderId' => $order->order_id,
                //         'Quantity' => $cart->book_quantity,
                //         'Total_Price' => $order->total_price,
                //         'Message' => 'Mail Sent to Users Mail With Order Details',
                //     ], 201);
                // }
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }
}
