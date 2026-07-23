<?php
require_once "c:/xampp/htdocs/fruvive/userFunc.php";
$object = new data();

try {
    // 1. Create wishlist table
    $sqlWish = "CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userid INT NOT NULL,
        productid INT NOT NULL,
        client_id INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (productid) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $object->conn->exec($sqlWish);
    echo "1. Wishlist table created successfully.\n";

    // 2. Create reviews table
    $sqlRev = "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userid INT NOT NULL,
        productid INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT DEFAULT NULL,
        client_id INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userid) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (productid) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $object->conn->exec($sqlRev);
    echo "2. Reviews table created successfully.\n";

    // 3. Seed dummy reviews if empty
    $check = $object->conn->prepare("SELECT COUNT(*) FROM reviews");
    $check->execute();
    if ($check->fetchColumn() == 0) {
        $dummyReviews = [
            // Banana(Martaman) - pid 2
            ['userid' => 1, 'productid' => 2, 'rating' => 5, 'comment' => 'Super sweet and fresh. Absolutely loved it!', 'client_id' => 1],
            ['userid' => 3, 'productid' => 2, 'rating' => 4, 'comment' => 'Great value for money, perfect size.', 'client_id' => 1],
            
            // Apple(Red Delicious) - pid 3
            ['userid' => 1, 'productid' => 3, 'rating' => 5, 'comment' => 'Very crispy apples, highly recommended!', 'client_id' => 1],
            
            // Almonds - pid 30
            ['userid' => 3, 'productid' => 30, 'rating' => 5, 'comment' => 'Premium packaging and quality.', 'client_id' => 1],
            ['userid' => 1, 'productid' => 30, 'rating' => 4, 'comment' => 'Fresh harvest almonds, tastes great.', 'client_id' => 1],
        ];

        $stmt = $object->conn->prepare("INSERT INTO reviews(userid, productid, rating, comment, client_id) VALUES(:userid, :productid, :rating, :comment, :client_id)");
        foreach ($dummyReviews as $rev) {
            $stmt->execute($rev);
        }
        echo "3. Seeded dummy reviews successfully.\n";
    } else {
        echo "3. Reviews already exist. Skipping seed.\n";
    }

} catch (PDOException $e) {
    echo "Error during migrations: " . $e->getMessage() . "\n";
}
?>
