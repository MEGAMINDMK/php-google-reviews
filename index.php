<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
<?php

/*

💬 Get Google-Reviews with PHP cURL & without API Key
=====================================================

**This is a dirty but usefull way to grab the first 8 most relevant reviews from Google with cURL and without the use of an API Key**

How to find the needed CID No:
  - use: [https://pleper.com/index.php?do=tools&sdo=cid_converter]
  - and do a search for your business name

> HINT: Use .quote in you CSS to style the output

###### Copyright 2019-2020 Igor Gaffling

*/

$options = array(
  'google_maps_review_cid' => '12312321543565667345', // Customer Identification (CID)
  'show_only_if_with_text' => false, // true = show only reviews that have text
  'show_only_if_greater_x' => 0,     // (0-4) only show reviews with more than x stars
  'show_rule_after_review' => true,  // false = don't show <hr> Tag after each review (and before first)
  'show_blank_star_till_5' => true,  // false = don't show always 5 stars e.g. ⭐⭐⭐☆☆
  'your_language_for_tran' => 'en',  // give you language for auto translate reviews  
  'sort_by_reating_best_1' => true,  // true = sort by rating (best first)
  'show_cname_as_headline' => true,  // true = show customer name as headline
  'show_age_of_the_review' => true,  // true = show the age of each review
  'show_txt_of_the_review' => true,  // true = show the text of each review
  'show_author_of_reviews' => true,  // true = show the author of each review
);

/* -------------------- */
echo getReviews($options);
/* -------------------- */

function getReviews($option) {
  $ch = curl_init('https://www.google.com/maps?cid='.$option['google_maps_review_cid']);                                                               /* GOOGLE REVIEWS BY cURL */
  if ( isset($option['your_language_for_tran']) and !empty($option['your_language_for_tran']) ) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: '.$option['your_language_for_tran']));
  }
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0');
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  curl_close($ch);                                                                                                                                     /* </cURL END> */
  $pattern = '/window\.APP_INITIALIZATION_STATE(.*);window\.APP_FLAGS=/ms';                                                                            /* REVIEW REGEX PATTERN */
  if ( preg_match($pattern, $result, $match) ) {                                                                                                       /* CHECK IF REVIEWS FOUND */
    $match[1] = trim($match[1], ' =;');                                                                                                                /* DIRTY JSON FIX */
    $reviews  = json_decode($match[1]);                                                                                                                /* 2. JSON DECODE */
    $reviews  = ltrim($reviews[3][6], ")]}'");                                                                                                         /* DIRTY JSON FIX */
    $reviews  = json_decode($reviews);                                                                                                                 /* 2. JSON DECODE */
    $customer = $reviews[6][11];                                                                                                                       /* POSITION OF REVIEWS */
    $reviews  = $reviews[6][52][0];                                                                                                                    /* POSITION OF REVIEWS */
  }                                                                                                                                                    /* END CHECK */
  $return = '<table>';                                                                                                                                        /* INI VAR */
  if (isset($reviews)) {                                                                                                                               /* CHECK REVIEWS */
  //  if ( isset($option['sort_by_reating_best_1']) and $option['your_language_for_tran'] == true )                                /* CHECK SORT */
   //   array_multisort(array_map(function($element) { return $element[4]; }, $reviews), SORT_DESC, $reviews);                                           /* SORT */
  //  $return .= '';	/* OPEN DIV */
  //  if (isset($option['show_cname_as_headline']) and $option['show_cname_as_headline'] == true) $return .= '<tr><td><strong>'.$customer.'</strong></td>';       /* CUSTOMER */
   foreach ($reviews as $review) {  
      if (isset($option['show_age_of_the_review']) and $option['show_age_of_the_review'] == true) $return .= '<tr><th><small>'.$review[0][1].' &mdash; '.$review[1].' </small>';      /* AUTHOR */
      if (isset($option['show_only_if_greater_x']) and $review[4] <= $option['show_only_if_greater_x']) continue; for ($i=1; $i <= $review[4]; ++$i) $return .= '⭐';  /* CHECK RATING */                                                                                              /* RATING */
      if (isset($option['show_blank_star_till_5']) and $option['show_blank_star_till_5'] == true) for ($i=1; $i <= 5-$review[4]; ++$i) $return .= '☆'; /* RATING */
      if (isset($option['show_rule_after_review']) and $option['show_rule_after_review'] == true) $return .= '</th></tr>'; 	/* START LOOP */
      if (isset($option['show_only_if_with_text']) and $option['show_only_if_with_text'] == true and empty($review[3])) continue; $return .= '<tr><td>';   /* CHECK TEXT */                                                                                                                               /* NEWLINE */
      if (isset($option['show_txt_of_the_review']) and $option['show_txt_of_the_review'] == true) $return .= $review[3].'</td></tr>';                        /* TEXT *//* RULER */
    }                                                                                                                                                  /* END LOOP */
    $return .= '</table>';                                                                                                                               /* CLOSE DIV */
  }                                                                                                                                                    /* CHECK REVIEWS */
  return $return;                                                                                                                                      /* RETURN DATA */
}                                                                                                                                                      /* END OF FUNCTION */
