<?php
$API_TOKEN = 'apify_api_i4P3aqisZAWKhUDOPgCsVAhmWYD91E2Cb649';

$input = [
    "startUrls" => [
        [
            "url" => "https://www.google.com/maps/place/Victory+Martial+Arts/@36.2328513,-115.3519414,12.23z/data=!4m10!1m2!2m1!1svictory+martial+arts!3m6!1s0x80c8c0252bd4450f:0xe957fb448f4b9a66!8m2!3d36.197452!4d-115.2509593!15sChR2aWN0b3J5IG1hcnRpYWwgYXJ0cyIDiAEBkgETbWFydGlhbF9hcnRzX3NjaG9vbOABAA!16s%2Fg%2F11b6hsmbyn?entry=ttu"
        ],
        [
            "url" => "https://www.google.com/maps/place/Victory+Martial+Arts/@33.3764052,-113.9253953,8.3z/data=!4m10!1m2!2m1!1svictory+martial+arts!3m6!1s0x872ba8e8b5bc8b95:0x48ac3a8f34e6db70!8m2!3d33.379926!4d-111.7893192!15sChR2aWN0b3J5IG1hcnRpYWwgYXJ0cyIDiAEBkgETbWFydGlhbF9hcnRzX3NjaG9vbOABAA!16s%2Fg%2F1hc7jz_76?entry=ttu"
        ],
        [
            "url" => "https://www.google.com/maps/place/Victory+Martial+Arts/@28.7381653,-82.1343125,8.67z/data=!4m10!1m2!2m1!1svictory+martial+arts!3m6!1s0x88e772b9969a1cb1:0xcfcd0b2db6bd80b5!8m2!3d28.7548094!4d-81.3573015!15sChR2aWN0b3J5IG1hcnRpYWwgYXJ0cyIDiAEBkgETbWFydGlhbF9hcnRzX3NjaG9vbOABAA!16s%2Fg%2F1tf6692h?entry=ttu"
        ]
    ],
    "maxReviews" => 3,
    "reviewsSort" => "newest",
    "language" => "en",
    "personalData" => true
];

$input_json = json_encode($input);
$api_url = "https://api.apify.com/v2/actor-tasks/komar~scraper/run-sync-get-dataset-items?token=$API_TOKEN";

$ch = curl_init($api_url);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input_json);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if ($response === false) {
    echo 'cURL Error: ' . curl_error($ch);
} 

curl_close($ch);

$decoded_content = json_decode($response, true);

foreach ($decoded_content as $data) {
    if (!isset($location[$data['address']])) {
        $location[$data['address']] = array(
            "title" => $data['title'],
            "city" => $data['city'],
            "reviews_count" => $data['reviewsCount'],
            "category_name" => $data['categoryName'],
            "website" => $data['website'],
            "url" => $data['url'],
            "reviews" => [] 
        );
    }

    $location[$data['address']]['reviews'][] = [
        'stars' => $data['stars'],
        'published_at' => $data['publishedAtDate']
    ];
}

foreach ($location as $address => $entry) {
    $query = $conn->prepare("INSERT INTO clients (company_name, city, reviews_count, company_address, category, website, google_maps_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $query->execute([$entry['title'], $entry['city'], $entry['reviews_count'], $address, $entry['category_name'], $entry['website'], $entry['url']]);
    $client_id = $conn->lastInsertId();

    foreach ($entry['reviews'] as $review) {
        $query = $conn->prepare("INSERT INTO reviews (client_id, stars, published_at) VALUES (?, ?, ?)");

        $date_time = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $review['published_at']);
        $formatted_date_time = $date_time->format('Y-m-d H:i:s');
        
        $query->execute([$client_id, $review['stars'], $formatted_date_time]);
    }
}

?>
