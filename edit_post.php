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

		update_story($title, $author_id, $content, $story_id);

		$db->query("DELETE FROM stories_categories WHERE story_id = $story_id");

		if (is_array($cat_id)) {
			foreach ($cat_id as $val) {
				create_stories_categories($story_id, $val);
			}
		}

	}

}

?>
<html>

<?php
$get_id = $_GET['id'] ?? exit('error');
$query = "SELECT s.id si, s.title st, s.content sc, a.id ai, a.name an FROM stories s JOIN authors a ON s.author_id = a.id WHERE s.id = $get_id";
$story = $db->query($query)->fetch();

if (isset($ers)) {
	foreach ($ers as $er) {
		echo "<p>$er</p>";
	}
}
?>
<form action="" method="post">
	<input type="hidden" name="story_id" value="<?php echo $story['si'] ?>">
	<input type="text" name="title" value="<?php echo $story['st'] ?? ""; ?>">
	<input type="text" name="author" value="<?php echo $story['an'] ?? ""; ?>">
	<textarea name="content" style="width: 100%; height: 60%"><?php echo $story['sc'] ?? ""; ?></textarea>
	<?php
	foreach (get_categories() as $cat) {
		
		$cat_id_check = $db->query("SELECT category_id FROM stories_categories WHERE category_id = $cat[id] AND story_id = $get_id")->fetch()['category_id'];
		if ($cat_id_check == $cat['id']) {
			$checked = 'checked';
		} else {
			$checked = null;
		}
		echo "<input type='checkbox' name='cat_id[]' value='".$cat['id']."' $checked> ".$cat['name']."<br />";
	}
	?>
	<input type="submit" name="submit" value="Edit">
</form>
</html>