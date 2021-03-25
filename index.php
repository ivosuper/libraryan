<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once 'includes/BookLibrarian.php';

$librarian =  BookLibrarian::getInstance();
if(isset($_POST['search'])){
    $books = $librarian->getBooks($_POST['search']);
    
} else {
    $books = $librarian->getBooks();
}
//var_dump($books);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Library</title>
    <link rel="stylesheet" href="includes/style.css"/>
</head>
<body>
    <div class="page">
    <h1>Book Library</h1>
    <form action="index.php" method="POST">
        <input name="search"  type="text" placeholder="author"/>
    <button type="submit">Search</button>
    </form>
    <table>
        <tr>
            <th>Book Name</th>
            <th>Author</th>
            <th>Updated</th>
        </tr>
        <?php if($books) {?>
        <?php foreach ($books as $book){?>
        <tr>
            <td><?php echo $book['name'];?></td>
            <td><?php echo $book['author'];?></td>
            <td><?php echo date('F d, Y h:mA', strtotime($book['updated']));?></td>
        </tr>
        <?php } ?>
        <?php } else {?>
        <td colspan="3" ><?php echo 'Author '.$_POST['search'].' Not found!';?></td>
        <?php }?>
    </table>
    </div>
    <script src="includes/app.js"></script>
</body>
</html>