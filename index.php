<?php
	require 'php-sdk/facebook.php';
	$facebook = new Facebook(array(
		'appId' => '124593811064320',
		'secret' => '21edb9fabde67d1f0718e13ddcc2ac4f'
	));

	//number of users per page
	$qty = 10;
	$currentoffset = $_GET['offset'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Movie Recommender</title>
	<link rel="stylesheet" href="style.css" />
</head>
<body>
	<header>
		<p>Love watching movies, but don't know what to watch? Put your friends to work.
			This app will tell you your friends favorite movies to give you some ideas.</p>
	</header>
	<?php
		//get user fron facebook object
		$user = $facebook->getUser();

		//check for existing user ID
		if($user){

			//print logout link
			echo '<p class="notes"><a href="logout.php">logout</a></p>';

			$user_graph = $facebook ->api(array(
				'method' => 'fql.query',
				'query' => "SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me()) AND movies !=''"
			));

			$howmanyfriends = count($user_graph);
			$offsettext = ($currentoffset) ? "OFFSET $currentoffset" : '';

			$moviefriends_graph = $facebook->api(array(
				'method' => 'fql.query',
				'query' => "SELECT name, uid, movies, pic_square FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me()) AND movies !='' LIMIT $qty $offsettext"
			));

			echo '<div class="moviegroup">';
			foreach ($moviefriends_graph as $key => $value){
				echo '<div class="friend group">';
				echo '<div class="friendinfo group">';
				echo '<a href="http://facebook.com/' . $value['uid'] . '" target="_top">';
				echo '<img class="friendthump" src="https://graph.facebook.com/' . 
				$value['uid'] . '/picture" alt="' . $value['name'] . '"/>';
				echo '</a>';
				echo '<h2>' . $value['name'] . '</h2>';
				echo '<h3>Recommends</h3>';
				echo '</div>'; //closes friend info div
				echo '<ul class="movies group">';

				$moviespath = '/' . $value['uid'] . '/movies?fields=id,link,name,description,picture.type(square).height(100).width(100)';
				$movies_graph = $facebook->api($moviespath);
					//goes through movies of friends
					foreach($movies_graph['data'] as $moviekey => $movievalue){
						echo '<li>';
						echo '<a href="' . $movievalue['link'] . '" target="_top">';
						echo '<img class="moviethumb" src="' . $movievalue['picture']['data']['url'] . '" title="' . $movievalue['name'] . '" />';
						echo '</a>';
						echo '<div class="movieinfo">';
						echo '<div class="wrapper">';
						echo '<h3>' . $movievalue['name'] . '</h3>';
						echo '<p>' . $movievalue['description'] . '</p>';
						echo '</div>';
						echo '</div>';
						echo '</li>';
					}
				echo '</ul>';
				echo '</div>';
			}

			$totalpages = ceil($howmanyfriends/$qty); // total pages
			$currentpage = ($currentoffset/$qty)+1; // current page
			$nextoffset = $currentoffset + $qty; //increment offset

			if($totalpages > 1){
				echo '<div class="paging">';
				echo '<div class="pagenav">';

				//creates a previous page link
				if($currentoffset >= $qty){
					echo '<span class="previous">';
					echo '<a href="' . $_SERVER['SELF'] . '?offset=' . ($currentoffset - $qty) . '">&laquo; Previous</a>';
					echo '</span>';
				}

				for($i = 0; $i < $totalpages; $i++){
					echo '<span class="number';
					if($i === ($currentpage - 1)){
						echo ' current';
					}
					echo '">';
					echo '<a href="' . $_SERVER['SELF'] . '?offset=' . ($i * $qty) . '">' . ($i + 1) . '</a>';
					echo '</span>';
				}

				//creates a next page link
				if($nextoffset < $howmanyfriends){
					echo '<span class="next">';
					echo '<a href="' . $_SERVER['SELF'] . '?offset=' . $nextoffset . '">Next &raquo;</a>';
					echo '</span>'; 
				}

				echo '</div>';// pagenav end
				echo '<div class="info">Page ' . $currentpage . ' of ' . $totalpages . '</div>';
				echo '<p>You have ' . $howmanyfriends . ' friends.</p>';
				echo '</div>';
			}
			echo '</div>';// moviegroup end

			
		}
		//user doesn't exist
		else{
			$loginUrl = $facebook ->getLoginUrl(array(
				'display' => 'popup',
				'scope' => 'friends_likes',
				'redirect_uri' => 'http://apps.facebook.com/atetphptest'
			));
			echo '<div class="notes">';
			echo '<p>To access movie recomendations <a href="' . $loginUrl . '" target="_top">login</a></p>';
			echo '</div>';
		}
	?>
</body>
</html>