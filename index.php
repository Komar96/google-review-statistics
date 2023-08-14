<?php
include('config.php');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin:*");

// COMPANY NAME FOR QUERIES
$company_name = "Victory Martial Arts";

try {
    // $token = $_SERVER['HTTP_AUTHORIZATION'] ?? ''; // Authorization is not in global $_SERVER
    $token = getallheaders()['Authorization'];
    $validToken = 'secret-key';
    
    if ($token !== "Bearer $validToken") {
    // if (false) { //for testing
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
    } else {
        $conn = db();

        // UNCOMMENT LINE FOR GETTING DATA FROM APIFY
        // include('apify.php'); 

        $data = get_statistics($conn, $company_name);
        $response = json_encode($data);
        echo $response;
    }
} catch (Exception $e) {
    $errorResponse = [
        "error" => "An error occurred: " . $e->getMessage()
    ];
    http_response_code(500);
    echo json_encode($errorResponse);
}


function get_statistics($conn, $company_name) {
    $maximum_values = get_maximum_values($conn, $company_name);
    $max_avg_stars = $maximum_values['max_avg_stars'] ;
    $max_total_reviews = $maximum_values['max_total_reviews'];
    $max_last_month_reviews = $maximum_values['max_last_month_reviews'];

    $reputation_score_data = reputation_score($conn, $company_name, $max_avg_stars, $max_total_reviews, $max_last_month_reviews);

    $location_performance_data = location_performance($conn, $company_name, $max_avg_stars, $max_total_reviews, $max_last_month_reviews);

    $data = array_merge($reputation_score_data, $location_performance_data);

    return $data;
}

function get_maximum_values($conn, $company_name) {
    // SELECT THE MAX AVG VALUE
    $max_avg_query = "SELECT company_address, average_stars
                        FROM (SELECT c.company_address, AVG(r.stars) AS average_stars
                            FROM clients c
                            LEFT JOIN reviews r ON c.id = r.client_id
                            WHERE c.company_name = '{$company_name}'
                            GROUP BY c.company_address
                        ) AS company_avg_stars
                        ORDER BY average_stars DESC
                        LIMIT 1";
    $max_avg = $conn->query($max_avg_query)->fetch();
    $max_avg_stars = number_format($max_avg['average_stars'], 1);
   
    // SELECT MAX REVIEWS COUNT
    $reviews_count_query = "SELECT MAX(reviews_count) as max_reviews FROM clients WHERE company_name = '{$company_name}'";
    $max_reviews_count = $conn->query($reviews_count_query)->fetch();
    $max_total_reviews = number_format($max_reviews_count['max_reviews'], 1);

    // SELECT MAX LAST 30 DATS REVIEWS
    $max_last_30_day_reviews_query = "SELECT COUNT(r.id) AS review_count
                                        FROM clients c
                                        LEFT JOIN reviews r ON c.id = r.client_id AND r.published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                        WHERE company_name = '{$company_name}'
                                        GROUP BY c.company_address
                                        ORDER BY review_count DESC
                                        LIMIT 1";
    $max_last_30_day_reviews = $conn->query($max_last_30_day_reviews_query)->fetch();
    $max_last_month_reviews = number_format($max_last_30_day_reviews['review_count'], 1);

    return [
        'max_avg_stars' => $max_avg_stars,
        'max_total_reviews' => $max_total_reviews,
        'max_last_month_reviews' => $max_last_month_reviews
    ];
}

function reputation_score($conn, $company_name, $max_avg_stars, $max_total_reviews, $max_last_month_reviews) {
    // AVG REVIEW AMONG ALL LOCATIONS
    $avg_review_among_all_locations_query = "SELECT AVG(average_stars) AS average_review_value_among_all_locations 
                                            FROM (SELECT company_address, average_stars
                                                                FROM (SELECT c.company_address, AVG(r.stars) AS average_stars
                                                                    FROM clients c
                                                                    LEFT JOIN reviews r ON c.id = r.client_id
                                                                    WHERE c.company_name = '{$company_name}'
                                                                    GROUP BY c.company_address
                                                                ) AS company_avg_stars
                                                    ) as subquery";
    $avg_review_among_all_locations = $conn->query($avg_review_among_all_locations_query)->fetch();
    $avg_review = number_format($avg_review_among_all_locations['average_review_value_among_all_locations'], 1);

    // AVG REVIEW COUNT AMONG ALL LOCATIONS
    $avg_review_count_among_all_locations_query  = "SELECT AVG(reviews_count) as average_review_count FROM clients WHERE company_name = '{$company_name}'";
    $avg_review_count_among_all_locations = $conn->query($avg_review_count_among_all_locations_query)->fetch();
    $avg_review_count = number_format($avg_review_count_among_all_locations['average_review_count'], 1);

    // GET AVERAGE REVIEWS PAST 30 DAYS AMONG ALL LOCATIONS
    $avg_review_count_past_30_days_among_all_locations_query = "SELECT AVG(review_count) AS average_review_count_last_month
                                                                    FROM (
                                                                        SELECT c.company_address, COUNT(r.id) AS review_count
                                                                        FROM clients c
                                                                        LEFT JOIN reviews r ON c.id = r.client_id AND r.published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                                        WHERE c.company_name = '{$company_name}'
                                                                        GROUP BY c.company_address
                                                                    ) as subquery";
    $avg_review_count_past_30_days_among_all_locations = $conn->query($avg_review_count_past_30_days_among_all_locations_query)->fetch();
    $avg_review_count_last_month = number_format($avg_review_count_past_30_days_among_all_locations['average_review_count_last_month'], 1);

    // overall review rating
    $overall_reviews_rating_percentage = number_format(($avg_review / $max_avg_stars) * 100, 1);
    $overall_reviews_rating_score = str_replace('.', '', $overall_reviews_rating_percentage);
 
    // overall review number
    $overall_reviews_number_percentage = number_format(($avg_review_count / $max_total_reviews) * 100, 1);
    $overall_reviews_number_score = str_replace('.', '', $overall_reviews_number_percentage);

    // overall review number
    $overall_reviews_last_month_percentage = number_format(($avg_review_count_last_month / $max_last_month_reviews) * 100, 1);
    $overall_reviews_last_month_score = str_replace('.', '', $overall_reviews_last_month_percentage);

    $reviews_rating = [
        "best_value" => $max_avg_stars,
        "label" => get_label($overall_reviews_rating_score),
        "overall_percentage" => $overall_reviews_rating_percentage.'%'
    ];
    $reviews_number = [
        "best_value" => $max_total_reviews,
        "label" => get_label($overall_reviews_number_score),
        "overall_percentage" => $overall_reviews_number_percentage.'%'
    ];
    $reviews_last_month = [
        "best_value" => $max_last_month_reviews,
        "label" => get_label($overall_reviews_last_month_score),
        "overall_percentage" => $overall_reviews_last_month_percentage.'%'
    ];

    return [
        "reviews_rating" => $reviews_rating,
        "reviews_number" => $reviews_number,
        "reviews_last_month" => $reviews_last_month
    ];
}

function location_performance($conn, $company_name, $max_avg_stars, $max_total_reviews, $max_last_month_reviews) {
    $location_performance_query = "SELECT c.id AS location_id, c.company_name AS location_name, c.city,
                        AVG(r.stars) AS average_stars,
                        COUNT(r.id) AS total_reviews_count,
                        COUNT(CASE WHEN r.published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE NULL END) AS last_month_review_count
                    FROM clients c
                    LEFT JOIN reviews r ON c.id = r.client_id
                    WHERE c.company_name = '{$company_name}'
                    GROUP BY c.id, c.company_name, c.city";
    $location_performance_result = $conn->query($location_performance_query)->fetchAll();

    $location_performance = [];
    foreach ($location_performance_result as $company_location) {
        // limit to one decimal
        $location_average_stars = number_format((float) $company_location['average_stars'], 1);
        $location_review_count = number_format((float) $company_location['total_reviews_count'], 1);
        $location_last_month_reviews = number_format((float) $company_location['last_month_review_count'],1);

        // percentage
        $reviews_rating_percentage = number_format(($location_average_stars / (float) $max_avg_stars) * 100, 1);
        $reviews_number_percentage = number_format(($location_review_count / (float) $max_total_reviews) * 100, 1);
        $last_month_reviews_percentage = number_format(($location_last_month_reviews / (float) $max_last_month_reviews) * 100, 1);

        // parameters score
        $reviews_rating_score = str_replace('.', '', $reviews_rating_percentage);
        $reviews_number_score = str_replace('.', '', $reviews_number_percentage);
        $last_month_reviews_score = str_replace('.', '', $last_month_reviews_percentage);

        // total sore
        $location_total_score = round(($reviews_rating_score + $reviews_number_score + $last_month_reviews_score) / 3);

        // total percentage
        $total_percentage = number_format(($location_total_score / 1000) * 100, 1);

        $location = $company_location['location_name'].' - '.$company_location['city'];
        $location_performance[$location] = [
            'score' => $location_total_score,
            'percentage' => $total_percentage,
            'label' => get_label($location_total_score),
        ];
    }

    // best 5 locations
    $scores = array_column($location_performance, 'score');
    array_multisort($scores, SORT_DESC, $location_performance);
    $desc_limited_data = array_slice($location_performance, 0, 5, true); 

    // worst 5 locations
    $scores = array_column($location_performance, 'score');
    array_multisort($scores, SORT_ASC, $location_performance);
    $asc_limited_data = array_slice($location_performance, 0, 5, true); 

    return [
        'highest_reputation_score' => $desc_limited_data,
        'lowest_reputation_score' => $asc_limited_data
    ];
}

// Function to get label based on score
function get_label($score) {
    $label_ranges = [
        ["label" => "Worst", "range_start" => 0, "range_end" => 333],
        ["label" => "Average", "range_start" => 334, "range_end" => 666],
        ["label" => "Best", "range_start" => 667, "range_end" => 1000]
    ];

    foreach ($label_ranges as $range) {
        if ($score >= $range["range_start"] && $score <= $range["range_end"]) {
            return $range["label"];
        }
    }
    return "unknown";
}

?>
