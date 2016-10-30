<html>
<?php

require 'vendor/autoload.php';
$db = new PDO('sqlite:./store.db');

$bonus  = 0;
$sides  = 0;
$amount = 0;
$error  = '';
$roll   = 0;

$room = preg_replace('/[^A-Za-z0-9]/', '', filter_input(INPUT_GET, 'room'));
$user = preg_replace('/[^A-Za-z0-9]/', '', filter_input(INPUT_GET, 'user'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT);
  $bonus =  filter_input(INPUT_POST, 'bonus', FILTER_VALIDATE_INT);
  $sides =  filter_input(INPUT_POST, 'sides', FILTER_VALIDATE_INT);

  if (
    !is_int($amount)  ||
    !is_int($bonus)   ||
    !is_int($sides)   ||
    !strlen($user)    ||
    !strlen($room)    ||
    strlen($user) > 8 ||
    strlen($room) > 8
  ) {
    $error = 'Invalid Input';
  } else {

    $roll = $bonus;
    for ($i = 0; $i < $amount; ++$i) {
      $roll += mt_rand(1, $sides);
    }
    
    $statement = $db->prepare('INSERT INTO roll'
      . ' (room, user, roll) VALUES'
      . ' (:room, :user, :roll)');

    $statement->bindParam(':room', $room);
    $statement->bindParam(':user', $user);
    $statement->bindParam(':roll', $roll);

    $statement->execute();
  }
}

?>

<head>
  <meta charset='utf-8' />
  <title>Dice Roller</title>
</head>

<body>
<form method="get">
  <input
    type="text"
    name="room"
    value="<?php
      echo "$room"
    ?>"
  ></input><label for="room">room</label><br>

  <input
    type="text"
    name="user"
    value="<?php
      echo "$user"
    ?>"
  ></input><label for="user">user</label><br>
  <input type="submit"></input>
</form>

<form method="post">
  <input 
    type="number"
    name="sides"
    value="<?php echo $sides? $sides:'20' ?>"
  ></input>
  <label for="sides">sides</label><br />

  <input
    type="number"
    name="amount"
    value="<?php echo $amount? $amount:'1' ?>"
  ></input>
  <label for="amount">amount</label>
  <br />

  <input type="number" name="bonus"
    value="<?php echo $bonus? $bonus:'0' ?>"
  ></input>
  <label for="bonus">bonus</label><br />

  <input type="submit"></input>
</form>
<ul>
<?php

$statement = $db->prepare('SELECT user, roll FROM roll'
  . ' WHERE room = :room ORDER BY id DESC');

$statement->bindParam(':room', $room);
$statement->execute();

$result = $statement->fetchAll();
foreach ($result as $row) {
  $row_user = $row[0];
  $row_roll = $row[1];
  echo "<li>$row_user - $row_roll</li>";
}

?>
</ul>
</body>
</body>
