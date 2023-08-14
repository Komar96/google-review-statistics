
const apiUrl = 'index.php';
const authToken = 'secret-key';
let json_data;

const loading_element = document.getElementById('loading');
loading_element.style.display = 'block';

fetch(apiUrl, { headers : {
      'Authorization': `Bearer ${authToken}`
    }
  })
  .then(response => {

      loading_element.style.display = 'none';
      if (!response.ok) {
          throw new Error(`Network response was not ok: ${response.status}`);
      }
      return response.json();
  })
  .then(data => {
      console.log('Response:', data);
      json_data = data;
      update_reputation_score_data(json_data);
      update_location_performance_data(json_data);
  })
  .catch(error => {
      console.error('Error:', error);
      loading_element.style.display = 'none';
  });


function update_reputation_score_data(json_data) {
    // Populate the reviews rating data
    populate_and_style_progress_bar(
      json_data.reviews_rating,
      "reviews-rating-label",
      "reviews-rating",
      "reviews-rating-bar",
      "reviews-rating-percentage"
    );

    // Populate the review number data
    populate_and_style_progress_bar(
      json_data.reviews_number,
      "reviews-number-label",
      "reviews-number",
      "reviews-number-bar",
      "reviews-number-percentage"
    );

    // Populate the review last month data
    populate_and_style_progress_bar(
      json_data.reviews_last_month,
      "reviews-last-month-label",
      "reviews-last-month",
      "reviews-last-month-bar",
      "reviews-last-month-percentage"
    );
}

function populate_and_style_progress_bar(data, label_element_id, value_element_id, bar_element_id, bar_percentage_id) {
  const label_element = document.getElementById(label_element_id);
  label_element.textContent = data.label;

  const value_element = document.getElementById(value_element_id);
  value_element.textContent = data.best_value;

  const percentage_element = document.getElementById(bar_percentage_id);
  percentage_element.textContent = data.overall_percentage;

  const bar_element = document.getElementById(bar_element_id);
  bar_element.style.height = data.overall_percentage;

 

  if (data.label === "Worst") {
    bar_element.style.backgroundColor = "red";
    label_element.style.backgroundColor = "red";
  } else if (data.label === "Average") {
    bar_element.style.backgroundColor = "yellow";
    label_element.style.backgroundColor = "yellow";
  } else {
    bar_element.style.backgroundColor = "green";
    label_element.style.backgroundColor = "green";
  }
}


function update_location_performance_data(json_data) {
  const highest_scores = json_data.highest_reputation_score;
  const lowest_scores = json_data.lowest_reputation_score;

  const html_template = `
        <div class="row custom-border">
            <div class="col-md-6">
                <div id="company" style="color:#1976D2;font-size: 13px;white-space:nowrap;">
                    {location}
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-end">
                <div class="progress progress-bar-horizontal">
                    <div id="horizontal-progress-bar" class="progress-bar" role="progressbar"
                        aria-valuenow="{total_score}" aria-valuemin="0" aria-valuemax="1000" style="width: {total_percentage}%; background-color: {progress_color};">
                    </div>
                </div>
                <div style="margin-left: 10px;" id="score-value">{total_score}</div>
            </div>
        </div>
  `;

    let highest_resulting_html = '';
    let lowest_resulting_html = '';

    function generateHtml(location, total_score, total_percentage, progress_color) {
        return html_template
            .replace(/{location}/g, location)
            .replace(/{total_score}/g, total_score)
            .replace(/{total_percentage}/g, total_percentage)
            .replace(/{progress_color}/g, progress_color)
    }

    for (const location in highest_scores) {
      const data = highest_scores[location];
      const total_score = data["score"];
      const total_percentage = data["percentage"];
      const label = data["label"];

      let progress_color;
      progress_color = "white";
      if (label === "Worst") {
          progress_color = "red";
      } else if (label === "Average") {
          progress_color = "yellow";
      } else {
          progress_color = "green";
      }
      highest_resulting_html += generateHtml(location, total_score, total_percentage, progress_color);
    }
    document.getElementById('highest-location-performance').innerHTML = highest_resulting_html;

    for (const location in lowest_scores) {
      const data = lowest_scores[location];
      const total_score = data["score"];
      const total_percentage = data["percentage"];
      const label = data["label"];

      let progress_color;
      progress_color = "white";
      if (label === "Worst") {
          progress_color = "red";
      } else if (label === "Average") {
          progress_color = "yellow";
      } else {
          progress_color = "green";
      }
      lowest_resulting_html += generateHtml(location, total_score, total_percentage, progress_color);
    }
    document.getElementById('lowest-location-performance').innerHTML = lowest_resulting_html;
}