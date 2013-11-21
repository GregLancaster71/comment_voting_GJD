<?php
function voteButtonSelected()
{
    global $wpdb, $current_user;
    get_currentuserinfo();
    $voter_id           = $current_user->ID;
    $comment_id         = get_comment_ID();
    $comment            = get_comment($comment_ID);
    $nonce              = wp_create_nonce("commentsvote_nonce_" . $comment_id);
    $vote_up            = '<button id="upvote' . $comment_id . '" class="upvote" type="button" data-nonce="' . $nonce . '"><span class="glyphicon glyphicon-chevron-up"></span></button>';
    $votedUp            = '<button id="upvote' . $comment_id . '" class="upvote voted" type="button" data-nonce="' . $nonce . '"><span class="glyphicon glyphicon-chevron-up"></span></button>';
    $vote_down          = '<button id="downvote' . $comment_id . '" class="downvote" type="button" data-nonce="' . $nonce . '"><span class="glyphicon glyphicon-chevron-down"></span></button>';
    $votedDown          = '<button id="downvote' . $comment_id . '" class="downvote voted" type="button" data-nonce="' . $nonce . '"><span class="glyphicon glyphicon-chevron-down"></span></button>';
    $checkExistingVotes = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ams_comment_voting WHERE voter_id = %d AND comment_id = %d", $voter_id, $comment_id));
    foreach ($checkExistingVotes as $existingVotes) {
        $storedCommentID = $existingVotes->comment_id;
        $storedVoteValue = $existingVotes->vote_value;
        $storedVoterID   = $existingVotes->voter_id;
        if ($voter_id == $storedVoterID) {
            $storedVoteValueArray = array(
                '-1' => $vote_up . $votedDown,
                '0' => $vote_up . $vote_down,
                '1' => $votedUp . $vote_down
            );
            if (array_key_exists($storedVoteValue, $storedVoteValueArray) && $storedCommentID == $comment_id) {
                return $storedVoteValueArray[$storedVoteValue];
            }
        }
    }
    return $vote_up . $vote_down;
}
function checkUserVotes($content)
{
    global $wpdb;
    $comment_id  = get_comment_ID();
    $count_votes = $wpdb->get_var($wpdb->prepare("SELECT SUM(vote_value) FROM " . $wpdb->prefix . "ams_comment_voting WHERE comment_id = %d", $comment_id));
	if (!isset($count_votes)) {	$count_votes = 0; }
    $output      = "<div id='vote_buttons'><span class='getvotes'>" . $count_votes . "</span>" . voteButtonSelected() . "<br></div><br>" . $content;
    return $output;
}
add_action('comment_text', 'checkUserVotes');
add_action('wp_enqueue_scripts', 'amc_comment_vote_ajax');
function amc_comment_vote_ajax()
{
    wp_register_script("amc_comment_vote", WP_PLUGIN_URL . '/gogonow/js/amc_comment_vote.js', array(
        'jquery'
    ));
    wp_localize_script('amc_comment_vote', 'anotherAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
    wp_enqueue_script('jquery');
    wp_enqueue_script('amc_comment_vote');
}
add_action('wp_ajax_amc_comment_vote', 'amc_comment_vote');
add_action('wp_ajax_nopriv_amc_comment_vote', 'amc_comment_vote');
function amc_comment_vote()
{
    global $wpdb, $comment, $post, $current_user;
    get_currentuserinfo();
    if (!is_user_logged_in()) {
        $response = array(
            'loggedIn' => false,
            'alertMsg' => 'not logged in :,-('
        );
        die();
    } else {
        $cID      = $_POST['commentid'];
        $voter_id = $current_user->ID;
        if (!wp_verify_nonce($_POST['nonce'], "commentsvote_nonce_" . $cID)) {
            exit("Something Wrong");
        }
        $grabFromCID     = get_comment($cID);
        $author_id       = $grabFromCID->user_id;
        $comment_post_id = $grabFromCID->comment_post_ID;
        $vote_replace    = '';
        $vote_direction  = $_POST['direction'];
        $voteValueArray  = array(
            'upvote' => 1,
            'downvote' => -1
        );
        if (array_key_exists($vote_direction, $voteValueArray)) {
            $vote_value = $voteValueArray[$vote_direction];
        }
        $replaceVoteValue = '';
        $alreadyVotedUp   = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "ams_comment_voting WHERE comment_id = %d AND voter_id = %d", $cID, $voter_id));
        if (isset($alreadyVotedUp)) {
            $current_value = $alreadyVotedUp->vote_value;
            if ($current_value == $vote_value) {
                $replaceVoteValue = 0;
            } else
                $replaceVoteValue = $vote_value;
        }
        $tableName = $wpdb->prefix . 'ams_comment_voting';
        $sql       = $wpdb->prepare("INSERT INTO $tableName (`voter_id`, `comment_id`, `comment_pid`, `author_id`, `vote_value`) VALUES (%d, %d, %d, %d, %d) ON DUPLICATE KEY UPDATE vote_value = %d", $voter_id, $cID, $comment_post_id, $author_id, $vote_value, $replaceVoteValue);
        $wpdb->query($sql);
        $response = array(
            'loggedIn' => true,
            'success' => true,
            'alertMsg' => 'testMSG'
        );
    }
    wp_send_json_success($response);
    die();
}
