<?php
require_once('lib/htm.php');
require_once('lib/htmUsers.php');

$get_user = $dbc->prepare('SELECT * FROM users INNER JOIN profiles ON profiles.user_id = users.user_id WHERE user_name = ? LIMIT 1');
$get_user->bind_param('s', $action);
$get_user->execute();
$user_result = $get_user->get_result();

if ($user_result->num_rows == 0){
	printHeader('');
	noUser();
} else {
    $user = $user_result->fetch_assoc();
if ($user['user_level'] == -1){
    printHeader('');
	hiddenUser(); 
}
else
	if(!(isset($_GET['offset']) && is_numeric($_GET['offset']))){

		printHeader('');

		echo '<title>Uiiverse - '. $user['nickname'] .'\'s Profile</title><div id="sidebar" class="user-sidebar">';

		userContent($user, "followers");

		userSidebarSetting($user, 0);

		userInfo($user);

		echo '</div>
		<div class="main-column"><div class="post-list-outline">
		  <h2 class="label">'. $user['nickname'] .'\'s Followers</h2>';

          if ($user ['user_relationship_visibility'] == 2) {
          echo '<div class="no-content"><p>This information is private and cannot be viewed.</p></div>';
} else {
    echo '
		  <div class="list follow-list following">
		    <ul class="list-content-with-icon-and-text arrow-list" id="friend-list-content" data-next-page-url="">';

		$get_followers = $dbc->prepare('SELECT * FROM follows WHERE follow_to = ? ORDER BY follow_id DESC');
		$get_followers->bind_param('i', $user['user_id']);
		$get_followers->execute();
		$followers_result = $get_followers->get_result();

		if(!$followers_result->num_rows == 0){

			while($followers = $followers_result->fetch_array()){

				$get_follow_user = $dbc->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
				$get_follow_user->bind_param('i', $followers['follow_by']);
				$get_follow_user->execute();
				$follow_user_result = $get_follow_user->get_result();
				$follow_user = $follow_user_result->fetch_assoc();

				echo '<li class="trigger" data-href="/users/'. $follow_user['user_name'] .'/">
				  <a href="/users/'. $follow_user['user_name'] .'/" class="icon-container">
				    <img src="'. printFace($follow_user['user_face'], 0) .'" class="icon">
				  </a>

				    <div class="toggle-button">';

				$check_followed = $dbc->prepare('SELECT * FROM follows WHERE follow_by = ? AND follow_to = ? LIMIT 1');
				$check_followed->bind_param('ii', $_SESSION['user_id'], $follow_user['user_id']);
				$check_followed->execute();
				$followed_result = $check_followed->get_result();

				if (($followed_result->num_rows == 0) && ($_SESSION['user_id'] != $follow_user['user_id'])){
					echo '<button type="button" data-user-id="'. $follow_user['user_id'] .'" class="follow-button button symbol relationship-button" data-community-id="" data-url-id="" data-track-label="user" data-title-id="" data-track-action="follow" data-track-category="follow">Follow</button><button type="button" class="button follow-done-button relationship-button symbol none" disabled="">Follow</button>';
				}

				echo '</div>
				  <div class="body">
				    <p class="title">
				      <span class="nick-name">
				        <a href="/users/'. $follow_user['user_name'] .'/posts">'. $follow_user['nickname'] .'</a>
				      </span>
				      <span class="id-name">'. $follow_user['user_name'] .'</span>
				    </p>
				  </div>
				</li>';
			}
		} else {
			echo '<div id="user-page-no-content" class="no-content"><div>
			<p>No followed users.</p>
			</div></div>';
		}
	}
}
}