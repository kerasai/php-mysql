<?php

namespace Kerasai\MySql;

/**
 * Class Connection.
 */
class Connection {

  /**
   * The PDO connection.
   *
   * @var \PDO
   */
  protected $pdo;

  protected $prefix = '';

  /**
   * Prepared statements.
   *
   * @var \PDOStatement[]
   */
  protected $stmts = [];

  /**
   * Connection constructor.
   *
   * @param array $config
   *   Configuration array.
   */
  public function __construct(array $config) {
    $this->verifyConfig($config);

    // Add defaults.
    $config += [
      'driver' => 'mysql',
      'host' => 'localhost',
      'port' => 3306,
      'prefix' => '',
    ];

    $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $options = [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];
    $this->pdo = new \PDO($dsn, $config['user'], $config['password'], $options);
    $this->prefix = $config['prefix'];
  }

  /**
   * Verifies database configuration.
   *
   * @param array $config
   *   Configuration array.
   */
  protected function verifyConfig(array $config) {
    $required = ['user', 'password', 'dbname'];
    foreach ($required as $property) {
      if (empty($config[$property])) {
        throw new \Exception(sprintf('Database configuration missing required property "%s".', $property));
      }
    }
  }

  /**
   * Execute a query.
   *
   * @param string $query
   *   The query.
   * @param array $params
   *   Parameters for the query.
   *
   * @return \PDOStatement
   *   The executed statement.
   */
  public function execute($query, array $params = []) {
    $stmt = $this->prepare($query);
    $stmt->execute($params);
    return $stmt;
  }

  /**
   * Get a row of data.
   *
   * @param string $query
   *   The query.
   * @param array $params
   *   Parameters for the query.
   *
   * @return array
   *   A row of data.
   */
  public function getRow($query, array $params = []) {
    $result = $this->execute($query, $params);
    return $result->fetch();
  }

  /**
   * Get all rows of data.
   *
   * @param string $query
   *   The query.
   * @param array $params
   *   Parameters for the query.
   *
   * @return array
   *   All rows of data for the query.
   */
  public function getRows($query, array $params = []) {
    $result = $this->execute($query, $params);
    return $result->fetchAll();
  }

  /**
   * Get a column of data.
   *
   * @param string $query
   *   The query.
   * @param array $params
   *   Parameters for the query.
   *
   * @return array
   *   A column of data.
   */
  public function getCol($query, array $params = []) {
    $result = $this->execute($query, $params);
    $col = array();
    while ($val = $result->fetchColumn()) {
      $col[] = $val;
    }
    return $col;
  }

  /**
   * Get the first field from the first result.
   *
   * @param string $query
   *   The query.
   * @param array $params
   *   Parameters for the query.
   *
   * @return string
   *   A field of data.
   */
  public function getField($query, array $params = []) {
    $result = $this->execute($query, $params);
    return $result->fetchColumn();
  }

  /**
   * Get the ID of the last inserted record.
   *
   * @return string
   *   The ID of the last inserted row.
   */
  public function lastId() {
    return $this->pdo->lastInsertId();
  }

  /**
   * Gets placeholders for a query.
   *
   * @param array $values
   *   An array of values.
   *
   * @return string
   *   A string of placeholders for the values.
   */
  public function getPlaceholders(array $values) {
    $placeholders = array_fill(0, count($values), '?');
    return implode(' , ', $placeholders);
  }

  /**
   * Transform query into a prepared statement.
   *
   * @param string $query
   *   The query.
   *
   * @return \PDOStatement
   *   The prepared statement.
   */
  protected function prepare($query) {
    $query = $this->prefixQuery($query);
    if (!isset($this->stmts[$query])) {
      $this->stmts[$query] = $this->pdo->prepare($query);
    }
    return $this->stmts[$query];
  }

  /**
   * Prefix the query tables.
   *
   * @param string $query
   *   The query.
   *
   * @return string
   *   The query with tables prefixed.
   */
  protected function prefixQuery($query) {
    return strtr($query, ['{' => $this->prefix, '}' => '']);
  }

}
