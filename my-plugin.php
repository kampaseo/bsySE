<?php
/*
Plugin Name: Google Search Plugin
Plugin URI: https://example.com/google-search-plugin
Description: Girilen ürün ismini DuckDuckGo'da arayıp, ilk 10 siteyi gösteren WordPress eklentisi.
Version: 1.0
Author: Your Name
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
        <input type="text" id="product_name" name="product_name">
        <input type="submit" value="Ara">
    </form>';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['product_name'])) {
        $product_name = sanitize_text_field($_POST['product_name']);
        gsp_search_duckduckgo($product_name);
    }
}

// DuckDuckGo araması yapma fonksiyonu
function gsp_search_duckduckgo($product_name) {
    $url = 'https://api.duckduckgo.com/?q=' . urlencode($product_name) . '&format=json';

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        echo 'Bir hata oluştu: ' . $response->get_error_message();
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['RelatedTopics'])) {
        echo '<h2>Arama Sonuçları:</h2><ul>';
        foreach ($data['RelatedTopics'] as $item) {
            if (isset($item['FirstURL']) && isset($item['Text'])) {
                echo '<li><a href="' . esc_url($item['FirstURL']) . '" target="_blank">' . esc_html($item['Text']) . '</a></li>';
            }
        }
        echo '</ul>';
    } else {
        echo 'Arama sonuçları bulunamadı.';
    }
}

// Kısa kod ekleme
add_shortcode('duckduckgo_search_plugin', 'gsp_create_form');
