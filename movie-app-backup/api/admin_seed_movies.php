<?php
/**
 * Admin Seed Script
 * - Ensures `is_admin` column exists on users table
 * - Creates `movies` table if not exists
 * - Seeds the 23 hardcoded movies into the DB
 * - Ensures a default admin user exists
 *
 * Run once: http://localhost/movie-app/api/admin_seed_movies.php
 */

header('Content-Type: application/json');
require_once 'db_connect.php';

$results = [];

try {
    // 1. Add is_admin column if missing
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
        $results[] = "Added is_admin column to users table.";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $results[] = "is_admin column already exists.";
        } else {
            throw $e;
        }
    }

    // 2. Create movies table
    $pdo->exec("CREATE TABLE IF NOT EXISTS movies (
        id VARCHAR(10) PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        language VARCHAR(50),
        genre VARCHAR(50),
        rating VARCHAR(10),
        duration VARCHAR(20),
        poster_url TEXT,
        backdrop_url TEXT,
        cast_json TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $results[] = "Movies table ready.";

    // 3. Ensure admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@cineticket.com']);
    if (!$stmt->fetch()) {
        $adminId = mt_rand(1, 99999999);
        $hashedPw = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (id, name, email, password, is_admin) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$adminId, 'Admin', 'admin@cineticket.com', $hashedPw]);
        $results[] = "Admin user created (admin@cineticket.com / admin123).";
    } else {
        $pdo->exec("UPDATE users SET is_admin = 1 WHERE email = 'admin@cineticket.com'");
        $results[] = "Admin user already exists, ensured is_admin=1.";
    }

    // 4. Seed movies (skip if already seeded)
    $count = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    if ($count > 0) {
        $results[] = "Movies table already has $count entries. Skipping seed.";
    } else {
        $movies = [
            ['m01','Kalki 2898 AD','Telugu','Sci-Fi','8.5','3h 1m','https://image.tmdb.org/t/p/w500/yXJUniVQCFnOsGbqMAk2f1MmPz6.jpg','https://image.tmdb.org/t/p/w1280/8rj1AAMEnXiqG8FjG6v95T3S2aA.jpg','[{"name":"Prabhas","img":"https://image.tmdb.org/t/p/w185/cT8htcckI7tecuvwNQezO0OBCAV.jpg"},{"name":"Amitabh Bachchan","img":"https://image.tmdb.org/t/p/w185/mB1x8KtoT2GofH7Tj1kMh04R2A4.jpg"},{"name":"Kamal Haasan","img":"https://image.tmdb.org/t/p/w185/1k60VvG4Uo12Zepq0H7nEt47tZl.jpg"}]'],
            ['m02','Dune: Part Two','English','Sci-Fi','8.8','2h 46m','https://image.tmdb.org/t/p/w500/8b8R8l88Qje9dn9OE8PY05Nez7S.jpg','https://image.tmdb.org/t/p/w1280/xOMo8BRK7PfcJv9JCnx7s5hj0PX.jpg','[{"name":"Timothée Chalamet","img":"https://image.tmdb.org/t/p/w185/sZqG7Z2C3eR0h8e8hM9IfhKk03h.jpg"},{"name":"Zendaya","img":"https://image.tmdb.org/t/p/w185/5k5r5V64Hn0Dpm2dYpQ5FmJk5J9.jpg"}]'],
            ['m03','Devara: Part 1','Telugu','Action','7.9','2h 55m','https://image.tmdb.org/t/p/w500/lQfuaXjANoTsdx5iS0gCXlK9D2L.jpg','https://image.tmdb.org/t/p/w1280/9l1eZiJHmhr5jIlthMdJN5WYoff.jpg','[{"name":"N.T. Rama Rao Jr.","img":"https://image.tmdb.org/t/p/w185/n0hK6c5tNlH5M0UqP5UvV2Jv3tM.jpg"},{"name":"Janhvi Kapoor","img":"https://image.tmdb.org/t/p/w185/pY5X6wD8p7y4xK7tq2ZqM5u0sV3.jpg"}]'],
            ['m04','Guntur Kaaram','Telugu','Action','6.5','2h 42m','https://image.tmdb.org/t/p/w500/qvBt4YLy274ZmoMAfVlwmHkjVkq.jpg','https://image.tmdb.org/t/p/w1280/tZ6h7B8H4xQ6kH9y7eT8rB5rQ3C.jpg','[{"name":"Mahesh Babu","img":"https://image.tmdb.org/t/p/w185/q4Jk6vV2W0bM8uD5X4zN3tG6jF2.jpg"},{"name":"Sreeleela","img":"https://image.tmdb.org/t/p/w185/m1nB3pX9E5C2yW4T8hG7R5qP1sH.jpg"}]'],
            ['m05','Fighter','Hindi','Action','7.2','2h 46m','https://image.tmdb.org/t/p/w500/fjTvqj3IpTrjjJTvrCKysWO6Q2K.jpg','https://image.tmdb.org/t/p/w1280/k0H5C7vH6kX7iB6vK3gP7tN3dF4.jpg','[{"name":"Hrithik Roshan","img":"https://image.tmdb.org/t/p/w185/f9B6jG8H4kM3nB5vC7xQ8zF2tP1.jpg"},{"name":"Deepika Padukone","img":"https://image.tmdb.org/t/p/w185/tvD2sR9RhnqZWeF9Nmbqg8A0c4P.jpg"}]'],
            ['m06','Manjummel Boys','Malayalam','Thriller','8.6','2h 15m','https://image.tmdb.org/t/p/w500/bswrtewwthpsh6nABiqKevU4UBI.jpg','https://image.tmdb.org/t/p/w1280/5k5r5V64Hn0Dpm2dYpQ5FmJk5J9.jpg','[{"name":"Soubin Shahir","img":"https://image.tmdb.org/t/p/w185/cT8htcckI7tecuvwNQezO0OBCAV.jpg"},{"name":"Sreenath Bhasi","img":"https://image.tmdb.org/t/p/w185/mB1x8KtoT2GofH7Tj1kMh04R2A4.jpg"}]'],
            ['m07','Leo','Tamil','Action','7.7','2h 44m','https://image.tmdb.org/t/p/w500/gSOVog7ydsaF1YpgAqBqnKYFGY.jpg','https://image.tmdb.org/t/p/w1280/k0H5C7vH6kX7iB6vK3gP7tN3dF4.jpg','[{"name":"Vijay","img":"https://image.tmdb.org/t/p/w185/kZ6h7B8H4xQ6kH9y7eT8rB5rQ3C.jpg"},{"name":"Sanjay Dutt","img":"https://image.tmdb.org/t/p/w185/jZ32iR0jN1Wn4hF4w6G7M0E5z1Z.jpg"}]'],
            ['m08','Salaar: Part 1 - Ceasefire','Telugu','Action','7.1','2h 55m','https://image.tmdb.org/t/p/w500/nlu9WbcetNFRGXXPWITr30ob7W6.jpg','https://image.tmdb.org/t/p/w1280/xOMo8BRK7PfcJv9JCnx7s5hj0PX.jpg','[{"name":"Prabhas","img":"https://image.tmdb.org/t/p/w185/cT8htcckI7tecuvwNQezO0OBCAV.jpg"},{"name":"Prithviraj","img":"https://image.tmdb.org/t/p/w185/A1pwvQ8E9vYl96P9eZ23lR5qXpD.jpg"}]'],
            ['m09','Interstellar','English','Sci-Fi','8.6','2h 49m','https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg','https://image.tmdb.org/t/p/w1280/xOMo8BRK7PfcJv9JCnx7s5hj0PX.jpg','[{"name":"Matthew McConaughey","img":"https://image.tmdb.org/t/p/w185/cT8htcckI7tecuvwNQezO0OBCAV.jpg"},{"name":"Anne Hathaway","img":"https://image.tmdb.org/t/p/w185/1XQv1D2C6Z4s7YxQXn6T7zL5H2O.jpg"}]'],
            ['m10','Aavesham','Malayalam','Comedy','8.1','2h 38m','https://image.tmdb.org/t/p/w500/dFYEowZY1aXkAmBsTfIBg5R8kj2.jpg','https://image.tmdb.org/t/p/w1280/k0H5C7vH6kX7iB6vK3gP7tN3dF4.jpg','[{"name":"Fahadh Faasil","img":"https://image.tmdb.org/t/p/w185/jZ32iR0jN1Wn4hF4w6G7M0E5z1Z.jpg"}]'],
            ['m11','Captain Miller','Tamil','Action','7.0','2h 37m','https://image.tmdb.org/t/p/w500/k0KVaOOPSHPJgJEo4nrB2K7T3xk.jpg','https://image.tmdb.org/t/p/w1280/tZ6h7B8H4xQ6kH9y7eT8rB5rQ3C.jpg','[{"name":"Dhanush","img":"https://image.tmdb.org/t/p/w185/sZqG7Z2C3eR0h8e8hM9IfhKk03h.jpg"},{"name":"Shiva Rajkumar","img":"https://image.tmdb.org/t/p/w185/mB1x8KtoT2GofH7Tj1kMh04R2A4.jpg"}]'],
            ['m12','Oppenheimer','English','Drama','8.2','3h 0m','https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg','https://image.tmdb.org/t/p/w1280/fm6KqXpk3M2HVveHwCrBRoOoA0i.jpg','[{"name":"Cillian Murphy","img":"https://image.tmdb.org/t/p/w185/y3G257h67b3q1j08462085k5p7m.jpg"},{"name":"Robert Downey Jr.","img":"https://image.tmdb.org/t/p/w185/j3G257h67b3q1j08462085k5p7m.jpg"}]'],
            ['m13','Gargi','Tamil','Drama','8.4','2h 17m','https://image.tmdb.org/t/p/w500/qYPpFf3EbKsDLWmeBXVfhVJzD4y.jpg','https://image.tmdb.org/t/p/w1280/xOMo8BRK7PfcJv9JCnx7s5hj0PX.jpg','[{"name":"Sai Pallavi","img":"https://image.tmdb.org/t/p/w185/n0hK6c5tNlH5M0UqP5UvV2Jv3tM.jpg"}]'],
            ['m14','Jodhaa Akbar','Hindi','Romance','7.6','3h 33m','https://image.tmdb.org/t/p/w500/gPjgeywFNTlh9IUuXWy85nn4h8J.jpg','https://image.tmdb.org/t/p/w1280/k0H5C7vH6kX7iB6vK3gP7tN3dF4.jpg','[{"name":"Hrithik Roshan","img":"https://image.tmdb.org/t/p/w185/f9B6jG8H4kM3nB5vC7xQ8zF2tP1.jpg"},{"name":"Aishwarya Rai","img":"https://image.tmdb.org/t/p/w185/5k5r5V64Hn0Dpm2dYpQ5FmJk5J9.jpg"}]'],
            ['m15','Premam','Malayalam','Romance','8.3','2h 36m','https://image.tmdb.org/t/p/w500/wy0AgfOd17rI0GO2eeM6OPs1xLM.jpg','https://image.tmdb.org/t/p/w1280/fm6KqXpk3M2HVveHwCrBRoOoA0i.jpg','[{"name":"Nivin Pauly","img":"https://image.tmdb.org/t/p/w185/z4T1dJ5z8i5u7471y1o7489C8f6.jpg"},{"name":"Sai Pallavi","img":"https://image.tmdb.org/t/p/w185/h4T1dJ5z8i5u7471y1o7489C8f6.jpg"}]'],
            ['m16','Pathaan','Hindi','Action','6.8','2h 26m','https://image.tmdb.org/t/p/w500/eMqj2HKJlllS5yBFrSKlO4pPHlK.jpg','https://image.tmdb.org/t/p/w1280/tZ6h7B8H4xQ6kH9y7eT8rB5rQ3C.jpg','[{"name":"Shah Rukh Khan","img":"https://image.tmdb.org/t/p/w185/q4Jk6vV2W0bM8uD5X4zN3tG6jF2.jpg"},{"name":"Deepika Padukone","img":"https://image.tmdb.org/t/p/w185/tvD2sR9RhnqZWeF9Nmbqg8A0c4P.jpg"}]'],
            ['m17','Spider-Man: Across the Spider-Verse','English','Action','8.7','2h 20m','https://image.tmdb.org/t/p/w500/8Vt6mWEReuy4Of61Lnj5Xj704m8.jpg','https://image.tmdb.org/t/p/w1280/fm6KqXpk3M2HVveHwCrBRoOoA0i.jpg','[{"name":"Shameik Moore","img":"https://image.tmdb.org/t/p/w185/v3G257h67b3q1j08462085k5p7m.jpg"}]'],
            ['m18','Sita Ramam','Telugu','Romance','8.5','2h 43m','https://image.tmdb.org/t/p/w500/t1O94ZBzsQXJihtVkrsStRLyUDR.jpg','https://image.tmdb.org/t/p/w1280/tZ6h7B8H4xQ6kH9y7eT8rB5rQ3C.jpg','[{"name":"Dulquer Salmaan","img":"https://image.tmdb.org/t/p/w185/e3G257h67b3q1j08462085k5p7m.jpg"},{"name":"Mrunal Thakur","img":"https://image.tmdb.org/t/p/w185/h3G257h67b3q1j08462085k5p7m.jpg"}]'],
            ['m19','Dangal','Hindi','Drama','8.4','2h 41m','https://image.tmdb.org/t/p/w500/z2IN8S1gP2t3iRiCVVBgRfNjMwh.jpg','https://image.tmdb.org/t/p/w1280/k0H5C7vH6kX7iB6vK3gP7tN3dF4.jpg','[{"name":"Aamir Khan","img":"https://image.tmdb.org/t/p/w185/l3G257h67b3q1j08462085k5p7m.jpg"},{"name":"Fatima Sana Shaikh","img":"https://image.tmdb.org/t/p/w185/f3G257h67b3q1j08462085k5p7m.jpg"}]'],
            ['m20','Bangalore Days','Malayalam','Comedy','8.3','2h 51m','https://image.tmdb.org/t/p/w500/iFMyZw1DTGvZ8hPa0eTseSFiRT1.jpg','https://image.tmdb.org/t/p/w1280/xOMo8BRK7PfcJv9JCnx7s5hj0PX.jpg','[{"name":"Dulquer Salmaan","img":"https://image.tmdb.org/t/p/w185/e3G257h67b3q1j08462085k5p7m.jpg"},{"name":"Nazriya Nazim","img":"https://image.tmdb.org/t/p/w185/p3G257h67b3q1j08462085k5p7m.jpg"}]'],
            ['m21','RRR','Telugu','Action','7.8','3h 7m','https://image.tmdb.org/t/p/w500/nEufeZYpKOhO3bB3s1Rvm1iVIId.jpg','https://image.tmdb.org/t/p/w1280/tZ6h7B8H4xQ6kH9y7eT8rB5rQ3C.jpg','[{"name":"NTR Jr.","img":"https://image.tmdb.org/t/p/w185/u3G257h67b3q1j08462085k5p7m.jpg"},{"name":"Ram Charan","img":"https://image.tmdb.org/t/p/w185/t3G257h67b3q1j08462085k5p7m.jpg"}]'],
            ['m22','Bramayugam','Malayalam','Horror','8.1','2h 19m','https://image.tmdb.org/t/p/w500/34P9TUqHEyeqIPgIUalFifWDPQ4.jpg','https://image.tmdb.org/t/p/w1280/k0H5C7vH6kX7iB6vK3gP7tN3dF4.jpg','[{"name":"Mammootty","img":"https://image.tmdb.org/t/p/w185/d3G257h67b3q1j08462085k5p7m.jpg"},{"name":"Arjun Ashokan","img":"https://image.tmdb.org/t/p/w185/x3G257h67b3q1j08462085k5p7m.jpg"}]'],
            ['m23','Vada Chennai','Tamil','Action','8.5','2h 44m','https://image.tmdb.org/t/p/w500/7C7IQmf17WjhfQ0e327eNtvNxQd.jpg','https://image.tmdb.org/t/p/w1280/fm6KqXpk3M2HVveHwCrBRoOoA0i.jpg','[{"name":"Dhanush","img":"https://image.tmdb.org/t/p/w185/l3m1D17j9z2D2V11u8p5R2H2k3D.jpg"},{"name":"Ameer","img":"https://image.tmdb.org/t/p/w185/q2r5n0U1Q1R24D4I1aJvE2aI2M2.jpg"}]']
        ];

        $stmt = $pdo->prepare("INSERT INTO movies (id, title, language, genre, rating, duration, poster_url, backdrop_url, cast_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($movies as $m) {
            $stmt->execute($m);
        }
        $results[] = "Seeded " . count($movies) . " movies into the database.";
    }

    echo json_encode(['success' => true, 'results' => $results]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'results' => $results]);
}
?>
