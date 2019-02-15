<?php

namespace Kerasai\MySql;

/**
 * Class ConnectionFactory.
 */
class ConnectionFactory {

  protected $config = [];
  protected $connections = [];

  /**
   * ConnectionFactory constructor.
   *
   * @param array $config
   *   Configuration array.
   */
  public function __construct(array $config = []) {
    $this->config = $config;
  }

  /**
   * Get a connection.
   *
   * @param string $name
   *   The name of the connection.
   *
   * @return \Kerasai\MySql\Connection
   *   The connection.
   *
   * @throws \Exception
   */
  public function getConnection($name) {
    if (empty($this->connections[$name])) {
      $this->connections[$name] = new Connection($this->getConfig($name));
    }
    return $this->connections[$name];
  }

  /**
   * Gets configuration for the specified connection.
   *
   * @param string $name
   *   The connection name.
   *
   * @return array
   *   The connection configuration.
   *
   * @throws \Exception
   */
  public function getConfig($name) {
    if (!array_key_exists($name, $this->config)) {
      throw new \Exception(sprintf('No configuration available for connection "%s".', $name));
    }
    return $this->config[$name];
  }

  /**
   * Set configuration for a connection.
   *
   * @param string $name
   *   The connection name.
   * @param array $config
   *   The connection configuration.
   *
   * @return $this
   */
  public function setConfig($name, array $config) {
    $this->config[$name] = $config;
    return $this;
  }

}
