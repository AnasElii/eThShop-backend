<?php //Db Connection 

class Database
{
    private $host = "localhost";
    private $port = "3306";
    private $data = "users";
    private $user = "root";
    private $pass = "1996010203anAs--x";
    private $chrs = "utf8mb4";
    private $opts =
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->data;charset=$this->chrs", $this->user, $this->pass, $this->opts);
        } catch (Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'type' => 'connection',
                'message' => 'Erro!: ' . $ex->getMessage()
            ]);
            die();
        }
    }

    public final function execQuery($q, $array = [])
    {
        try {
            $query = $this->pdo->prepare($q);
            $query->execute($array);

            // Check if the query was an INSERT, UPDATE, or DELETE query
            $isInsertOrUpdateOrDelete = (stripos($q, 'INSERT') === 0) || (stripos($q, 'UPDATE') === 0) || (stripos($q, 'DELETE') === 0);

            if ($isInsertOrUpdateOrDelete) {
                return $query->rowCount(); // Return the number of affected rows
            } else {
                return $query->fetchAll(); // Return the result for SELECT queries
            }

            // return $query->fetchAll();
        } catch (Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'type' => 'query',
                'message' => 'Erro!: ' . $ex->getMessage()
            ]);
            die();
        }
    }

    public final function Query($q, $array = [])
    {
        try {
            $query = $this->pdo->prepare($q);
            $query->execute($array);
            return $query;
        } catch (Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'type' => 'query',
                'message' => 'Erro!: ' . $ex->getMessage()
            ]);
            die();
        }
    }

    public final function RowCount($q, $array = [])
    {
        try {
            
            $query = $this->pdo->prepare($q);
            $query->execute($array);
            return $query->rowCount();

        } catch (Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'type' => 'query',
                'message' => 'Erro!: ' . $ex->getMessage()
            ]);
            die();
        }
    }

    public final function execBindQuery($q, $array=[]){
        try {
            
            $query = $this->pdo->prepare($q);
            if ($query === false) {
                // Handle prepare error, e.g., invalid SQL
                return false;
            }

            // Bind parameters if $array is not empty
            if (!empty($array)) {
                foreach ($array as $key => &$value) {
                    // Assuming $placeholder is the placeholder name in your query, e.g., :idUser
                    $query->bindParam($key, $value);
                }
            }
            $query->execute();
            $id = $this->pdo->lastInsertId();

            return $id;

        } catch (Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'type' => 'query',
                'message' => 'Erro!: ' . $ex->getMessage()
            ]);
            die();
        }
    }

}