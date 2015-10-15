<?php
/**
 * Lauper Computing
 * User: Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * Date: 02/10/14
 * Time: 14:12
 */

namespace Lpc\LpcSermons\Utility;


use \PDO;

/**
 * Class Db
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package
 * @subpackage
 */
class Db {

    /** @var null|PDO  */
    public $host = null;

    /**
     * init the PDO object
     */
    public function __construct() {}

	/**
	 * sets the configuration and connect to db
	 * @param $host
	 * @param $db
	 * @param $username
	 * @param $password
	 */
	public function connect($host, $db, $username, $password) {
		try {
			$host = new PDO('mysql:host='.$host.';dbname='.$db, $username, $password);
			$host->exec("set names utf8");
			$this->host = $host;
		} catch (\PDOException $e) {
			$error =  'Connection failed: ' . $e->getMessage() . ' (Err: ' . $e->getCode() . ')<br>';
			echo $error;
			exit();
		}
	}

    /**
     * cleanup
     * be nice and handle the clutter
     */
    public function __destruct() {
        // disconnecct
        $this->host = null;
    }

    /**
     * execute a prepared statement
     * @param $sql string
     * @param array $params for bindings => array(name, value) (the ":" will be added if missing by name)
     * @return \PDOStatement e.g. for select add fetchAll(\PDO::FETCH_ASSOC)
     */
    public function exec($sql, $params = array()) {

        // check if params starts with ":". Else add it
        foreach ($params as $key => $val) {
            if (substr($key, 0, 1) !== ':') {
                unset($params[$key]);
                $params[':' . $key] = $val;
            } else {
                break;
            }
        }

        $query = $this->host->prepare($sql);
        $query->execute($params);
        if ($query->errorCode() > 0) {
			echo '<b>Error:</b><br>';
            var_dump($query->errorInfo());
        }
        return $query;
    }

    /**
     * manage insert/update
     * @param $table string tablename
     * @param $params array(name, value)
     * @param string $updateWhere if set then update with this where as string
     * @return int inserted id
     */
    public function insert($table, $params, $updateWhere = '') {
        if (strlen($updateWhere) == 0) {
            // insert
            $isNew = true;
            $fields = array_keys($params);
            $sql = '
              INSERT INTO ' . $table .'
                (' . implode(', ', $fields) . ')
              VALUES
                (:' . implode(', :', $fields) . ')
            ;';
        } else {
            // update
            $isNew = false;
            $updateParts = array();
            foreach ($params as $key => $val) {
                $updateParts[] = $key . '= :' . $key;
            }
            if (count($updateParts) > 0) {
                // cut last ,
                $upatePartString = implode(', ', $updateParts);
                $sql = '
                  UPDATE ' . $table .'
                  SET ' . $upatePartString . '
                  ' . $updateWhere . '
                ;';
            }
        }

        $newId = 0;
        if (strlen($sql) > 0) {
			// convert all text to utf8
			foreach ($params as $key => $val) {
				$params[$key] = utf8_decode($val);
			}

            $this->exec($sql, $params);
            if ($isNew) {
                $newId = $this->host->lastInsertId();
            }
        }

        return $newId;
    }


}
