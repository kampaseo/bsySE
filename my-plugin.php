<?php
/*
Plugin Name: Google Search Plugin
Plugin URI: https://example.com/google-search-plugin
Description: Girilen ürün ismini DuckDuckGo'da arayıp, ilk 10 siteyi gösteren WordPress eklentisi.
Version: 1.0
Author: SoS
Author URI: https://example.com
License: GPL2
*/

// Admin menüsüne sayfa ekleme
function gsp_add_admin_menu() {
    add_menu_page(
        'DuckDuckGo Search Plugin',    // Sayfa başlığı
        'DuckDuckGo Search',           // Menü başlığı
        'manage_options',              // Yetki
        'duckduckgo-search-plugin',    // Menü slug
        'gsp_display_admin_page',      // Gösterilecek fonksiyon
        'dashicons-search',            // Menü simgesi
        6                              // Menü pozisyonu
    );
}
add_action('admin_menu', 'gsp_add_admin_menu');

// Admin sayfasını gösterme fonksiyonu
function gsp_display_admin_page() {
    ?>
    <div class="wrap">
        <h1>DuckDuckGo Search Plugin</h1>
        <?php gsp_create_form(); ?>
    </div>
    <?php
}

// Formu oluşturma ve işleme
function gsp_create_form() {
    echo '<form method="post" action="">
        <label for="product_name">Ürün İsmi:</label>
        <input type="text" id="product_name" name="product_name" required>
        <input type="submit" value="Ara">
    </form>';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['product_name'])) {
        $product_name = sanitize_text_field($_POST['product_name']);
        echo '<p>Aranan Ürün: ' . esc_html($product_name) . '</p>';
        gsp_search_duckduckgo($product_name);
    }
}

// DuckDuckGo araması yapma fonksiyonu
function gsp_search_duckduckgo($product_name) {
    $url = "https://www.searchapi.io/api/v1/search";
    $params = array(
        "engine" => "duckduckgo",
        "q" => $product_name
    );
    $queryString = http_build_query($params);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url . '?' . $queryString,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "accept: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        echo "cURL Error #:" . $error;
    } else {
        $data = json_decode($response, true);

        if (isset($data['results']) && !empty($data['results'])) {
            echo '<h2>Arama Sonuçları:</h2><ul>';
            foreach ($data['results'] as $item) {
                if (isset($item['url']) && isset($item['title'])) {
                    echo '<li><a href="' . esc_url($item['url']) . '" target="_blank">' . esc_html($item['title']) . '</a></li>';
                }
            }
            echo '</ul>';
        } else {
            echo '<p>Arama sonuçları bulunamadı.</p>';
        }
    }
}

// Kısa kod ekleme
add_shortcode('duckduckgo_search_plugin', 'gsp_create_form');
