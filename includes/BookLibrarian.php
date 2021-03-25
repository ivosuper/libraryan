<?php

// General singleton class.
class BookLibrarian {

    // Hold the class instance.
    private static $instance = null;
    private $dbconn;
    private $books;

    // The constructor is private
    // to prevent initiation with outer code.
    private function __construct() {
        // The expensive process (e.g.,db connection) goes here.
        // Connecting, selecting database
        $this->dbconn = pg_connect("host=" . HOST . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASSWORD) or die('Could not connect: ' . pg_last_error());

        $this->importBooksInDB();
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new BookLibrarian();
        }

        return self::$instance;
    }

    public function getBooks($author = false) {


        // Performing SQL query
        if ($author) {
            $query = "SELECT * FROM books WHERE lower(author) LIKE '%" . strtolower(trim($author)) . "%' ;";
        } else {
            $query = 'SELECT * FROM books;';
        }

        $result = pg_query($query) or die('Query failed: ' . pg_last_error());

        $books = pg_fetch_all($result);

        // Free resultset
        pg_free_result($result);

        // Closing connection
        pg_close($this->dbconn);
        return $books;
    }

    private function importBooksInDB() {
        $this->getBooksFromXML();

        foreach ($this->books as $book) {


            // Performing Insert SQL query
  
            $query = "SELECT * FROM books WHERE name='".$book['name']."' AND author='".$book['author']."' LIMIT 1";
            //$query = "SELECT * FROM books WHERE name='ooooo' AND author='".$book['author']."' LIMIT 1";
            $result = pg_query($query) or die('Query failed: ' . pg_last_error());
            $book_exist = pg_fetch_assoc($result);
            if($book_exist===false){
                $query = "INSERT INTO books (name , author) VALUES ('".$book['name']."','".$book['author']."')";
                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
            } else {
                $query = "UPDATE books SET updated = NOW() WHERE name='".$book['name']."' AND author='".$book['author']."'";
                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
            }

        }
    }

    private function getDirContents($dir, &$results = array()) {
        if(!is_dir($dir)){
            echo 'This Folder '.$dir.' do not exist';
            die();
        }
        $files = scandir($dir);
        
      
        

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                if (pathinfo($path, PATHINFO_EXTENSION) == 'xml') {
                    $results[] = $path;
                }
            } else if ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
            }
        }

        return $results;
    }

    public function getBooksFromXML() {
        $files = $this->getDirContents(DIRECTORY_XML);
      
       
        foreach ($files as $file) {

            $string = file_get_contents($file);
            
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($string);
            //WE CAN HANDLE THIS ERRORS MUCH BETTER LIKE A MESSAGES IN THE HTML PAGE
            if ($xml === false) {
                // oh no
                $errors = libxml_get_errors();
                // do something with them
                
                foreach ($errors as $error){
                    //print_r($error);
                    echo $file."<br>";
                    echo $error->message;
                    
                }
                echo '<br>WE CAN HANDLE THIS ERRORS MUCH BETTER LIKE A MESSAGES IN THE HTML PAGE';
                continue;
            }

            foreach ($xml->book as $book) {
                $this->books[] = [
                    'author' => trim((string) $book->author),
                    'name' => trim((string) $book->name)];
            }
        }
        $this->books = array_map("unserialize", array_unique(array_map("serialize", $this->books)));
    }

}
