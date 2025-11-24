# Laravel Order System Implementation Prompt

## Context
I'm building an e-commerce ordering system in Laravel with the following existing database schema:

### Existing Tables:
- `users`: id, name, email, role, timestamps
- `orders`: id (UUID), order_number (unique), status, payment_method, payment_status, shipping_method, shipping_cost, total_amount, user_id (FK), timestamps
- `order_items`: id, order_id (FK UUID), product_id (nullable FK), product_color_id (nullable FK), product_color_variant_id (nullable FK), price, quantity, subtotal, timestamps
- `products`: id, title, slug, description, price, category_id, status, timestamps
- `product_colors`: id, product_id (FK), color_id (FK), default (bool), sort_order, timestamps
- `product_color_variants`: id, product_id (FK), product_color_id (FK), size_id (FK), stock_quantity (int), timestamps
- `product_color_images`: id, product_color_id (FK), path, timestamps
- `sizes`: id, name, sort_order, timestamps
- `colors`: id, name, hex_code, sort_order, timestamps
- `shipping_addresses`: id, first_name, last_name, address, apartment, city, postal_code, phone, user_id (FK), timestamps

## Requirements

I need you to implement the following features with Laravel best practices:

### 1. Add JSON Columns Migration
Create a migration to add these JSON columns:
- `order_items.product_snapshot` (nullable) - to store product details at time of order
- `orders.shipping_address_snapshot` (nullable) - to store shipping address snapshot at time of order

The `product_snapshot` JSON structure should be:
```json
{
  "title": "Product Title",
  "description": "Product description",
  "slug": "product-slug",
  "color": {
    "name": "Color Name",
    "hex_code": "#FFFFFF",
    "image": "/storage/path/to/image.jpg"
  },
  "size": {
    "name": "Size Name"
  },
  "category": {
    "name": "Category Name",
    "slug": "category-slug"
  }
}
```

The `shipping_address_snapshot` JSON structure should be:
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "address": "123 Main St",
  "apartment": "Apt 4B",
  "city": "Dhaka",
  "postal_code": "1200",
  "phone": "+8801712345678"
}
```

### 2. Update Models
Update the following models with proper relationships and JSON casting:

**Order Model**: Cast `shipping_address_snapshot` as array, include relationships to `orderItems` and `user`

**OrderItem Model**: Cast `product_snapshot` as array, include relationships to `order`, `product`, `productColor`, `productColorVariant`. Add helper methods: `getSnapshotTitle()`, `getSnapshotImage()`, `getSnapshotColorName()`, `getSnapshotSizeName()`

### 3. Checkout API Route & Controller
Create `POST /api/checkout` route that:
- Validates incoming request (cart_items array, shipping address fields, payment_method, shipping_method)
- Accepts cart items in this format:
```json
{
  "cart_items": [
    {
      "product_id": 1,
      "product_color_id": 2,
      "product_color_variant_id": 3,
      "quantity": 2,
      "price": 29.99
    }
  ],
  "shipping_address": {
    "first_name": "John",
    "last_name": "Doe",
    "address": "123 Main St",
    "apartment": "Apt 4B",
    "city": "Dhaka",
    "postal_code": "1200",
    "phone": "+8801712345678"
  },
  "payment_method": "cod",
  "shipping_method": "standard"
}
```
- Creates order with unique order_number (format: ORD-{TIMESTAMP}-{RANDOM})
- Creates order items with product_snapshot JSON (fetch full product data with category, color, size, image)
- Saves shipping_address_snapshot as JSON in orders table
- Deducts stock from `product_color_variants.stock_quantity`
- Uses database transaction
- Returns OrderResource with nested OrderItemResource

### 4. Shipping Addresses CRUD
Create RESTful API routes and controller methods:
- `GET /api/shipping-addresses` - List all addresses for authenticated user
- `POST /api/shipping-addresses` - Create new address
- `GET /api/shipping-addresses/{id}` - Show single address
- `PUT /api/shipping-addresses/{id}` - Update address
- `DELETE /api/shipping-addresses/{id}` - Delete address

All routes should verify the address belongs to the authenticated user. Create FormRequest for validation.

### 5. Orders API
Create these routes and controller methods:

**List Orders**: `GET /api/orders`
- Paginated (15 per page)
- Filter by status (query param: ?status=pending)
- Order by created_at DESC
- Return OrderResource collection

**Order Details**: `GET /api/orders/{order_number}`
- Fetch order with items eager loaded
- Verify order belongs to authenticated user
- Return OrderResource with nested items

### 6. API Resources
Create:
- `OrderResource`: Include all order fields, shipping_address_snapshot JSON, items (nested OrderItemResource)
- `OrderItemResource`: Include quantity, price, subtotal, product_snapshot JSON
- `ShippingAddressResource`: Include all address fields

### 7. Service Class (Optional but Recommended)
Create `OrderService` class with:
- `createOrder(array $cartItems, array $shippingData, string $paymentMethod, string $shippingMethod): Order`
- `buildProductSnapshot(ProductColorVariant $variant): array`
- `calculateShipping(string $method): float` (standard: 50 BDT, express: 100 BDT)
- `calculateTotal(array $cartItems, float $shipping): float`

### 8. Validation Rules
**CheckoutRequest**:
- cart_items: required|array|min:1
- cart_items.*.product_id: required|exists:products,id
- cart_items.*.product_color_variant_id: required|exists:product_color_variants,id
- cart_items.*.quantity: required|integer|min:1
- shipping_address.first_name: required|string|max:255
- shipping_address.phone: required|string|max:20
- payment_method: required|in:cod,online
- shipping_method: required|in:standard,express

**ShippingAddressRequest**:
- All address fields with appropriate validation rules

## Technical Requirements
- Use Laravel 11 conventions
- Use proper HTTP status codes (200, 201, 404, 422, etc.)
- Use database transactions for order creation
- Eager load relationships to prevent N+1 queries
- Add proper authorization checks (user owns the resource)
- Use FormRequest classes for validation
- Follow RESTful API design principles
- Add proper error handling
- Use UUID for order primary key (already in schema)

## File Structure Expected
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── CheckoutController.php
│   │   │   ├── OrderController.php
│   │   │   └── ShippingAddressController.php
│   ├── Requests/
│   │   ├── CheckoutRequest.php
│   │   └── ShippingAddressRequest.php
│   └── Resources/
│       ├── OrderResource.php
│       ├── OrderItemResource.php
│       └── ShippingAddressResource.php
├── Models/
│   ├── Order.php
│   └── OrderItem.php
└── Services/
    └── OrderService.php

database/migrations/
└── xxxx_xx_xx_add_json_columns_to_orders_and_order_items.php

routes/
└── api.php
```

## Notes
- Authentication is handled by Laravel Sanctum (stateful)
- User is accessed via `auth()->user()` or `auth()->id()`
- All API routes should be in `api.php` with `auth:sanctum` middleware
- Product relationships: Product → hasMany ProductColors → hasMany ProductColorVariants
- ProductColor → hasMany ProductColorImages
- ProductColorVariant → belongsTo Size

Please generate all the necessary files with complete implementation following Laravel best practices and the requirements above.