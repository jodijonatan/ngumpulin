<?php
// app/controllers/tasks.php
require_once __DIR__ . '/../models/model.php';

class TaskController
{
  protected $model;
  public function __construct()
  {
    $this->model = new Model();
  }

  public function create($title, $description, $due_date, $created_by)
  {
    $this->model->run('INSERT INTO tasks (title, description, due_date, created_by) VALUES (?, ?, ?, ?)', 'sssi', [$title, $description, $due_date ?: null, $created_by]);
    return $this->model->lastInsertId();
  }

  public function update($id, $title, $description, $due_date)
  {
    $this->model->run('UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ?', 'sssi', [$title, $description, $due_date ?: null, $id]);
    return true;
  }

  public function delete($id)
  {
    $this->model->run('DELETE FROM tasks WHERE id = ?', 'i', [$id]);
    return true;
  }

  public function find($id)
  {
    return $this->model->fetchOne('SELECT t.*, u.name as creator_name FROM tasks t JOIN users u ON u.id = t.created_by WHERE t.id = ?', 'i', [$id]);
  }

  public function all($order = 'due_date ASC')
  {
    // safe ordering (basic)
    $sql = 'SELECT t.*, u.name as creator_name FROM tasks t JOIN users u ON u.id = t.created_by ORDER BY ' . $order;
    return $this->model->fetchAll($sql);
  }
}
