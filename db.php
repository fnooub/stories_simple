<?php

$db = new PDO("mysql:host=localhost;dbname=stories_simple;charset=utf8", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

function create_story($title, $author_id, $content)
{
	global $db;
	$query = "INSERT INTO stories (title, author_id, content) VALUES (?,?,?)";
	$stmt = $db->prepare($query);
	$stmt->execute(array($title, $author_id, $content));
	return $db->lastInsertId();
}

function create_category($name)
{
	global $db;
	$query = "INSERT INTO categories (name) VALUES (?)";
	$stmt = $db->prepare($query);
	$stmt->execute(array($name));
	return $db->lastInsertId();
}

function create_stories_categories($story_id, $category_id)
{
	global $db;
	$query = "INSERT INTO stories_categories (story_id, category_id) VALUES (?,?)";
	$stmt = $db->prepare($query);
	$stmt->execute(array($story_id, $category_id));
}

function create_author($name)
{
	global $db;
	$query = "INSERT INTO authors (name) VALUES (?)";
	$stmt = $db->prepare($query);
	$stmt->execute(array($name));
	return $db->lastInsertId();
}

function update_story($title, $author_id, $content, $id)
{
	global $db;
	$sql = "UPDATE stories SET title=?, author_id=?, content=? WHERE id=?";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($title, $author_id, $content, $id));
}

function check_author_exists($name)
{
	global $db;
	//$query = "SELECT id FROM authors WHERE name = ?";
	$query = "SELECT id FROM authors WHERE name REGEXP BINARY ?";
	$stmt = $db->prepare($query);
	$stmt->execute(array("^$name$"));
	if ($stmt->rowCount() > 0) {
		return $stmt->fetch();
	}
	return false;
}

function get_categories()
{
	global $db;
	$query = "SELECT * FROM categories ORDER BY name ASC";
	return $db->query($query)->fetchAll();
}

function get_posts()
{
	global $db;
	$query = "SELECT * FROM stories ORDER BY id DESC";
	return $db->query($query)->fetchAll();
}

function get_categories_by_post_id($id)
{
	global $db;
	$query = "SELECT * FROM categories c JOIN stories_categories sc ON sc.category_id = c.id WHERE sc.story_id = ?";
	$stmt = $db->prepare($query);
	$stmt->execute(array($id));
	if ($stmt->rowCount() > 0) {
		return $stmt->fetchAll();
	}
	return false;
}

function get_post($id)
{
	global $db;
	$query = "SELECT s.title st, s.content sc, a.name FROM stories s JOIN authors a ON s.author_id = a.id WHERE s.id = ?";
	$stmt = $db->prepare($query);
	$stmt->execute(array($id));
	if ($stmt->rowCount() > 0) {
		$data['post'] = $stmt->fetch();
		$data['categories'] = get_categories_by_post_id($id);
		$data['chapters'] = get_chapters($id);
		return $data;
	}
	return false;
}

function get_chapters($id)
{
	global $db;
	return $db->query("SELECT * FROM chapters WHERE story_id = $id")->fetchAll();
}

function get_chapter($id)
{
	global $db;
	$query = "SELECT s.title st, a.name an, c.title ct, c.content cc, c.story_id ci FROM chapters c JOIN stories s ON c.story_id = s.id JOIN authors a ON s.author_id = a.id WHERE c.id = ?";
	$stmt = $db->prepare($query);
	$stmt->execute(array($id));
	if ($stmt->rowCount() > 0) {
		$data['post'] = $stmt->fetch();
		$data['nextpre'] = array(
			'pre' => get_nextpre_chapter($id, $data['post']['ci'], 'pre'),
			'next' => get_nextpre_chapter($id, $data['post']['ci'], 'next')
		);
		return $data;
	}
	return false;
}

function get_nextpre_chapter($chapter_id, $story_id, $nextpre = '')
{
	global $db;
	if ($nextpre == 'next') {
		return $db->query("SELECT * FROM chapters WHERE story_id = $story_id AND id > $chapter_id ORDER BY id LIMIT 1")->fetch();
	} elseif ($nextpre == 'pre') {
		return $db->query("SELECT * FROM chapters WHERE story_id = $story_id AND id < $chapter_id ORDER BY id DESC LIMIT 1")->fetch();
	}
	return false;
}