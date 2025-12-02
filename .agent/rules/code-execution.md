# GitHub Copilot Instructions for Arushi Mart Backend

## Project Overview

This is a Laravel 11 e-commerce backend API for Arushi Mart using MySQL, Sanctum authentication, and follows RESTful API conventions.

## Code Style & Conventions

### General Practices

-   Use **array syntax** for validation rules, NOT string syntax

    ```php
    // Good
    'field' => ['required', 'string', 'max:255']

    // Bad
    'field' => 'required|string|max:255'
    ```

-   Use `Resource::make()` instead of `new Resource()`

    ```php
    // Good
    return OrderResource::make($order);

    // Bad
    return new OrderResource($order);
    ```

-   Use `latest()` instead of `orderBy('created_at', 'desc')`

    ```php
    // Good
    $query->latest();

    // Bad
    $query->orderBy('created_at', 'desc');
    ```

-   Use "use" import statements instead of full path inclusion in usage

    ```php
    // Good
    use \App\Http\Controllers\Api\Admin\OrderController;
    OrderController::class

    // Bad
    \App\Http\Controllers\Api\Admin\OrderController::class
    ```

-   Use method name `delete()` instead of `destroy()` for delete operations in controllers

### Authentication & Authorization

-   Always use `$request->user()` instead of `auth()` or `auth()->user()`

    ```php
    // Good
    $request->user()->id

    // Bad
    auth()->id()
    ```

-   Use relationship methods for querying user-related data

    ```php
    // Good
    $request->user()->orders()->latest()->get();

    // Bad
    Order::where('user_id', $request->user()->id)->latest()->get();
    ```

-   Apply authorization at the **route level** using `->can()` middleware, NOT in controllers

    ```php
    // routes/api.php - Good
    Route::get('/{order}', [OrderController::class, 'show'])->can('view', 'order');

    // Controller - Bad (don't do this)
    if ($order->user_id !== $request->user()->id) abort(403);
    ```

-   Create **Policy classes** for authorization logic
-   Remove route names with `->name()` - we don't use named routes

### Model Relationships

-   Relationship method names should be:

    -   Singular for `BelongsTo` and `HasOne`: `category()`, `user()`
    -   Plural for `HasMany` and `BelongsToMany`: `items()`, `orders()`, `categories()`

-   Always use relationship methods when creating/querying related data

    ```php
    // Good
    $request->user()->orders()->create($data);

    // Bad
    Order::create(['user_id' => $request->user()->id, ...$data]);
    ```

### Resources (API Responses)

-   Follow this structure for all Resource classes:

    ```php
    return [
        'id' => $this->id,
        'attributes' => [
            // All model attributes here in camelCase
            'fieldName' => $this->field_name,
        ],
        'relationships' => [
            // Related resources here
            'items' => ItemResource::collection($this->whenLoaded('items')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
        ],
        'createdAt' => $this->created_at?->toIso8601String(),
        'updatedAt' => $this->updated_at?->toIso8601String(),
    ];
    ```

-   Return resources **directly**, not wrapped in arrays

    ```php
    // Good
    return OrderResource::make($order);

    // Bad
    return response()->json(['order' => new OrderResource($order)]);
    ```

### Enums

-   Always use **backed enums** with string values
-   Use **UPPERCASE** for case names, **lowercase** for values

    ```php
    enum OrderStatus: string
    {
        case PENDING = 'pending';
        case PROCESSING = 'processing';
        case SHIPPED = 'shipped';
    }
    ```

-   Cast enum fields in models

    ```php
    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
    ];
    ```

-   Use enum cases in code, not string values

    ```php
    // Good
    'status' => OrderStatus::PENDING,

    // Bad
    'status' => 'pending',
    ```

### Database

-   Use **UUID** for primary keys in sensitive tables (orders)
-   Use `HasUuids` trait for UUID models
-   Always add **indexes** for frequently queried columns (status, user_id, etc.)
-   Use **JSON columns** for flexible data (shipping_address, product_snapshot)
-   Cast JSON columns to `array` in model `$casts`

### Services

-   Create **Service classes** for complex business logic (OrderService)
-   Services should accept dependencies as method parameters, not use global helpers

    ```php
    // Good
    public function createOrder(User $user, array $cartItems, ...)

    // Bad
    public function createOrder(array $cartItems, ...)
    {
        $user = auth()->user();
    }
    ```

-   Use `DB::transaction()` for operations that modify multiple tables
-   Keep services focused and single-responsibility

### Validation

-   Use **FormRequest** classes for complex validation
-   Use dynamic validation rules when needed

    ```php
    public function rules(): array
    {
        $isAuthenticated = $this->user() !== null;

        return [
            'field' => [$isAuthenticated ? 'nullable' : 'required', 'string'],
        ];
    }
    ```

-   Use `required_without`, `required_with`, etc. for conditional validation
-   Validate ownership in the request when checking related resources

### Controllers

-   Keep controllers thin - delegate to Services for business logic
-   Use **route model binding** whenever possible
-   Remove docblock comments unless they add value
-   Return appropriate HTTP status codes (422 for validation, 403 for unauthorized, 404 for not found)

### Error Handling

-   Use `abort()` for errors with appropriate status codes
    ```php
    abort(404, 'Resource not found');
    abort(403, 'Unauthorized access');
    abort(422, 'Validation failed');
    ```

### Data Consistency

-   Always verify **ownership** when accessing user-related resources
-   Validate data before processing (e.g., cart validation before checkout)
-   Use explicit field mapping when storing user input to prevent data leakage

    ```php
    // Good
    'shipping_address' => [
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        // ... explicit fields only
    ],

    // Bad
    'shipping_address' => $request->shipping_address, // might include extra fields
    ```

## Business Logic Patterns

### Cart Validation

-   Always validate cart items before checkout
-   Adjust quantities based on available stock
-   Remove unavailable products/variants
-   Return 422 status with adjusted cart if any changes were made

### Checkout Flow

-   Support both guest and authenticated checkout
-   For guests: require email, create user, send OTP, require verification
-   For authenticated: create order immediately
-   Support both `shipping_address_id` (saved address) and `shipping_address` (new address)
-   Verify shipping address ownership for authenticated users
-   Guests cannot use saved shipping addresses

### Order System

-   Use UUID for order IDs
-   Generate unique order numbers (ORD-YmdHis-XXXX)
-   Store complete product snapshot in order items (for historical reference)
-   Deduct stock in database transaction when creating order
-   Initialize all status fields with PENDING on order creation

### Stock Management

-   Always use transactions when modifying stock quantities
-   Validate stock availability before creating orders
-   Store stock_quantity in product_variants table

## File Organization

-   **Models**: `app/Models/`
-   **Controllers**: `app/Http/Controllers/Api/`
-   **Resources**: `app/Http/Resources/`
-   **Requests**: `app/Http/Requests/`
-   **Services**: `app/Services/`
-   **Policies**: `app/Policies/`
-   **Enums**: `app/Enums/`

## Key Models & Relationships

-   **User** hasMany Orders, hasMany ShippingAddresses
-   **Order** belongsTo User, hasMany OrderItems (relation name: `items()`)
-   **OrderItem** belongsTo Order
-   **Product** hasMany ProductVariants, hasMany ProductImages, belongsToMany Categories, morphToMany Tags
-   **ProductVariant** belongsTo Product, belongsTo Color, belongsTo Size
-   **ShippingAddress** belongsTo User

## Available Enums

-   **OrderStatus**: PENDING, PROCESSING, SHIPPED, DELIVERED, CANCELLED
-   **PaymentStatus**: PENDING, PAID, FAILED
-   **ShippingStatus**: PENDING, PROCESSING, SHIPPED, IN_TRANSIT, OUT_FOR_DELIVERY, DELIVERED, FAILED, RETURNED
-   **ProductType**: STITCHED, UNSTITCHED

## Models

-   Do not add any "fillable" property in any models

## Testing & Quality

-   No comments in code unless absolutely necessary for complex logic
-   Follow Laravel naming conventions
-   Use type hints for all method parameters and return types
-   Keep methods focused and single-purpose
-   Favor composition over inheritance
