<?php

require 'core.php';


///////////////
## Delete
//////////////

if (isset($get['delete'])) {
	$id = (int)$get['delete'];
	
	// first of all get all related persons and delete them
	mq("DELETE FROM members where relatedMember = $id");
	mq("DELETE FROM members where id = $id");
	
	header('Location: /');
	exit;
}


///////////////
## Insert/Update
//////////////
if (isset($post['action'])) {
	
	$errors = [];
	
	if (strlen($post['firstName']) == 0 || !ctype_alnum($post['firstName'])) {
		$errors[] = 'firstName invalid';
	}
	if (strlen($post['middleName']) == 0 || !ctype_alnum($post['middleName'])) {
		$errors[] = 'middleName invalid';
	}
	if (strlen($post['lastName']) == 0 || !ctype_alnum($post['lastName'])) {
		$errors[] = 'lastName invalid';
	}
	if (!in_array($post['gender'], ['m', 'f'])) {
		$errors[] = 'gender invalid';
	}
	if (!in_array($post['relationship'], $relationships)) {
		$errors[] = 'relationship invalid';
	}
	if ($post['relationship'] != 'none' && empty(mScalar("SELECT 1 FROM members WHERE id = ".$post['relatedMember']))) {
		$errors[] = 'relatedMember invalid';
	}
	
	if (empty($errors)) {
		
			
		if ($post['action'] == 'add') {
			
			mq("INSERT into members (`firstName`, `middleName`, `lastName`, `gender`, `relationship`, `relatedMember`) VALUES
								('".$post['firstName']."', '".$post['middleName']."', '".$post['lastName']."', '".$post['gender']."', '".$post['relationship']."', '".$post['relatedMember']."')");
								
			$_SESSION['msg'] = "Data inserted";

		}else{
		
			$id = (int)$post['editId'];
		
			mq("UPDATE `members` SET `firstName`='".$post['firstName']."',`middleName`='".$post['middleName']."',`lastName`='".$post['lastName']."',`gender`='".$post['gender']."',`relationship`='".$post['relationship']."',`relatedMember`='".$post['relatedMember']."' WHERE id = $id");
			
			$_SESSION['msg'] = "Data updated";
		
		}
		
	}else{
		$_SESSION['msg'] = "Fix following errors: ".implode('; ', $errors);
	}
	
	
	header('Location: /');
	exit;
}

if (isset($_SESSION['msg'])) {
	echo "<font color=blue>". $_SESSION['msg'] ."</font> <hr>";
	unset($_SESSION['msg']);
}

///////////////
## Display data
//////////////

?>

<h2>Members tree</h2>
<?php

$members = mGetAll(mq("SELECT * FROM members"));

$rankedList = [];

foreach ($members as $member) {
	$rank = $relationships[$member['relationship']];
	$rankedList[$rank][] = $member;
	
}

if (count($rankedList) == 3) {
	echo "<ul>";
	foreach ($rankedList[2] as $grandparent) {

		// grandparent
		echo "<li>".printName($grandparent, true)."</li>";
		
		$currentChildren = [];
		$currentParents = [];

		foreach ($rankedList[0] as $child) {
			if ($grandparent['relatedMember'] == $child['id']) {
				$currentChildren[] = $child;
							
				foreach ($rankedList[1] as $parent) {
					if ($parent['relatedMember'] == $child['id']) {
						$currentParents[] = $parent;
					}
				}
				
			}
		}
			// print parents
			echo "<ul>";
			
			foreach ($currentParents as $parent) {
				echo "<li>".printName($parent, true)."</li>";
			}
			
				// print childrens
				echo "<ul>";
				foreach ($currentChildren as $child) {
					echo "<li>".printName($child, true)."</li>";
				}
				
				echo "</ul>";
			
			
			echo "</ul>";
	}
	echo "</ul>";
}else{
	echo "The tree structure is not perfect and cannot be displayed.";
}

$editFirstName = '';
$editMiddleName = '';
$editLastName = '';
$editGender = '';
$editRelationship = '';
$editRelatedMember = '';


if (isset($get['edit'])) {
	$id = $get['edit'];
	$editedMember = mRow("SELECT * FROM members WHERE id = $id");
	
	$editFirstName = $editedMember['firstName'];
	$editMiddleName = $editedMember['middleName'];
	$editLastName = $editedMember['lastName'];
	$editGender = $editedMember['gender'];
	$editRelationship = $editedMember['relationship'];
	$editRelatedMember = $editedMember['relatedMember'];
}

?>


<form method="POST" action="">

	<h2>Add member</h2>
	<b>Fist Name*</b>
	<br>
	<input name="firstName" type="text" size="20" value="<?=$editFirstName?>">
	
	<br><br>

	<b>Middle Name*</b>
	<br>
	<input name="middleName" type="text" size="20" value="<?=$editMiddleName?>">
	
	<br><br>

	<b>Last name*</b>
	<br>
	<input name="lastName" type="text" size="20" value="<?=$editLastName?>">

	<br><br>
	
	<b>Gender*</b>
	<br>
	<label><input type="radio"  name="gender" value='m' <?=$editGender=='m'?'checked':''?>> Male</label>
	<label><input type="radio"  name="gender" value='f'  <?=$editGender=='f'?'checked':''?>> Female</label>
	
	<br><br>
	
	<b>Relationship</b>
	<select name='relationship'>
		<option value='none'>None</option>
		<?php
		foreach ($relationships as $relationship => $rank) {
		?>
			<option value='<?=$relationship?>' <?=$editRelationship == $relationship ? 'selected' : ''?>><?=$relationship?></option>
		<?php
		}
		?>
	</select>
	
	<br><br>
	
	<b>Select related family member</b>
	<select name='relatedMember'>
		<option value='0'></option>
		<?php
		$q = mq('SELECT id, firstName, middleName, lastName FROM members');
		while ($member = mfa($q)) {
		?>
			<option value='<?=$member['id']?>'  <?=$editRelatedMember == $member['id'] ? 'selected' : ''?>><?=printName($member)?></option>
		<?php
		}
		?>
	</select>
	
	<br><br>

	<input type="hidden" name="action" value="<?=isset($get['edit']) ? 'edit' : 'add'?>">
	<?php if (isset($get['edit'])) { ?>
		<input type="hidden" name="editId" value="<?=$get['edit']?>">
	<?php } ?>
	
	<button type="submit">Save member</button>

</form>