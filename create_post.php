<?php

error_reporting(E_ALL & ~ E_NOTICE);

include 'db.php';

if (isset($_POST['submit'])) {
	extract($_POST);

	if (empty($title)) {
		$ers[] = 'Nhập tên';
	}
	if (empty($author)) {
		$ers[] = 'Nhập tác giả';
	}
	if (empty($cat_id)) {
		$ers[] = 'Nhập thể loại';
	}

	if (!isset($ers)) {

		if ($author_check = check_author_exists($author)) {
			$author_id = $author_check['id'];
		} else {
			$author_id = create_author($author);
		}

		$post_id = create_story($title, $author_id, $content);

		if (is_array($cat_id)) {
			foreach ($cat_id as $val) {
				create_stories_categories($post_id, $val);
			}
		}

	}

}

?>
<html>

<?php
if (isset($ers)) {
	foreach ($ers as $er) {
		echo "<p>$er</p>";
	}
}
?>
<form action="" method="post">
	<input type="text" name="title" placeholder="Tieu de"><br>
	<input type="text" name="author" placeholder="Tac gia"><br>
	<textarea name="content" style="width: 100%; height: 50%"></textarea>
	<?php
	foreach (get_categories() as $cat) {
		if (isset($cat_id)) {
			if (in_array($cat['id'], $cat_id)) {
				$checked = 'checked';
			} else {
				$checked = null;
			}
		}
		echo '<input type="checkbox" name="cat_id[]" value="'.$cat['id'].'" '.$checked.'> '.$cat['name'].'<br>';
	}
	?>
	<input type="submit" name="submit" value="Post">
</form>
</html>