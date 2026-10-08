# Result 1

## Prompt

create /controllers

│   RegisterController.php

│   BookInController.php

│   SearchController.php

│   LoanController.php

│   ReturnController.php

│   HistoryController.php

│

## Program

**File structure**

```
/controllers
├─ RegisterController.php
├─ BookInController.php
├─ SearchController.php
├─ LoanController.php
├─ ReturnController.php
└─ HistoryController.php
```

---

### `RegisterController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * Show registration form.
     */
    public function index()
    {
        return view('register');
    }

    /**
     * Store new user and book association.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'book_id'   => 'required|exists:books,id',
        ]);

        $user = User::create([
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'password'=> bcrypt($validated['password']),
        ]);

        // associate selected book (e.g., many‑to‑many or belongs‑to)
        $user->books()->attach($validated['book_id']);

        return redirect()->route('login')->with('success', 'Registration successful.');
    }
}
```

---

### `BookInController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;

class BookInController extends Controller
{
    /**
     * Show list of available books for check‑in.
     */
    public function index()
    {
        $books = Book::where('checked_out', false)->get();
        return view('books.index', compact('books'));
    }

    /**
     * Check a book in for a specific user.
     */
    public function store(Request $request, $bookId)
    {
        $book = Book::findOrFail($bookId);
        $user = User::findOrFail($request->input('user_id'));

        if ($book->checked_out) {
            return back()->withErrors('This book is already checked out.');
        }

        $book->update(['checked_out' => true, 'checked_out_by_user_id' => $user->id, 'checked_out_at' => now()]);

        return redirect()->route('book.in.index')->with('success', 'Book checked in successfully.');
    }
}
```

---

### `SearchController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle book search queries.
     */
    public function index(Request $request)
    {
        $query = $request->input('q');

        $books = Book::when($query, function ($q) use