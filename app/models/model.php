<?php
// app/models/model.php
// Simple Model wrapper untuk koneksi dan helper DB
require_once __DIR__ . '/../config/koneksi.php';

class Model
{
  protected $db;

  public function __construct()
  {
    global $mysqli;
    $this->db = $mysqli;
  }

  // execute prepared query, return stmt
  public function run($sql, $types = null, $params = [])
  {
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) {
      throw new Exception('DB Prepare Error: ' . $this->db->error);
    }
    if ($types !== null && !empty($params)) {
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
  }

  // fetch one row
  public function fetchOne($sql, $types = null, $params = [])
  {
    $stmt = $this->run($sql, $types, $params);
    $res = $stmt->get_result();
    return $res->fetch_assoc();
  }

  // fetch all
  public function fetchAll($sql, $types = null, $params = [])
  {
    $stmt = $this->run($sql, $types, $params);
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
  }

  // last insert id
  public function lastInsertId()
  {
    return $this->db->insert_id;
  }
}
