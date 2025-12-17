<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Xử lý Preflight request của CORS (cho phương thức POST)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cấu hình Database
$host = 'db';
$dbname = 'laravel_db';
$user = 'dunne898';
$password = 'root';

// Biến giả lập Giỏ hàng (Trong môi trường thực tế, đây là DB Session)
session_start();
$cart = &$_SESSION['cart'];
if (!isset($cart)) {
    $cart = [];
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Lấy URL path (ví dụ: /products, /add-to-cart, /cart)
    $parsedUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = trim($parsedUrl, '/'); // Lấy chuỗi như 'products', 'cart', 'add-to-cart'
    
    $method = $_SERVER['REQUEST_METHOD'];

    // --- 1. API Lấy Sản phẩm (/products) ---
    // Điều kiện này sẽ là $requestUri === 'products'
    if ($requestUri === 'products' && $method === 'GET') {
        $stmt = $pdo->query('SELECT id, name, price FROM products');
        $products = $stmt->fetchAll();
        echo json_encode(['success' => true, 'message' => 'Lấy sản phẩm thành công.', 'data' => $products]);
        exit;
    }

    // --- 2. API Xem Giỏ hàng (/cart) ---
    if ($requestUri === 'cart' && $method === 'GET') {
        // Trong môi trường thực tế: lấy chi tiết sản phẩm từ DB
        // Hiện tại: Giả lập lấy chi tiết từ DB dựa trên id trong session
        $detailedCart = [];
        if (!empty($cart)) {
             $ids = implode(',', array_keys($cart));
             $stmt = $pdo->query("SELECT id, name, price FROM products WHERE id IN ($ids)");
             $dbProducts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR | PDO::FETCH_GROUP);
             
             foreach ($cart as $id => $quantity) {
                 if (isset($dbProducts[$id])) {
                    $item = $dbProducts[$id][0];
                    $item['quantity'] = $quantity;
                    $detailedCart[] = $item;
                 }
             }
        }
        echo json_encode(['success' => true, 'message' => 'Lấy giỏ hàng thành công.', 'data' => $detailedCart]);
        exit;
    }
    
    // --- 3. API Thêm vào Giỏ hàng (/add-to-cart) ---
    if ($requestUri === 'add-to-cart' && $method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = $input['product_id'] ?? null;
        $quantity = $input['quantity'] ?? 1;

        if ($productId) {
            // Kiểm tra sản phẩm tồn tại
            $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            
            if ($stmt->fetch()) {
                // Thêm/tăng số lượng trong session giỏ hàng giả lập
                if (isset($cart[$productId])) {
                    $cart[$productId] += $quantity;
                } else {
                    $cart[$productId] = $quantity;
                }
                echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được thêm vào giỏ hàng']);
                exit;
            }
        }
        
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ hoặc Sản phẩm không tồn tại.']);
        exit;
    }


    // --- 4. API Mặc định (Lỗi 404) ---
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'API Endpoint không tồn tại.']);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống DB.', 'error' => $e->getMessage()]);
}
?>