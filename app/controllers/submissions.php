<?php
// app/controllers/submissions.php
require_once __DIR__ . '/../models/model.php';

class SubmissionController
{
  protected $model;
  public function __construct()
  {
    $this->model = new Model();
  }

  // Insert or update submission (unique task_id + user_id constraint handled)
  public function submit($task_id, $user_id, $file_path, $comment = null)
  {
    // Check existing
    $existing = $this->model->fetchOne('SELECT * FROM submissions WHERE task_id = ? AND user_id = ?', 'ii', [$task_id, $user_id]);
    if ($existing) {
      $this->model->run('UPDATE submissions SET file_path = ?, comment = ?, submitted_at = NOW(), grade = NULL, feedback = NULL WHERE id = ?', 'ss i', [$file_path, $comment, $existing['id']]);
      return $existing['id'];
    } else {
      $this->model->run('INSERT INTO submissions (task_id, user_id, file_path, comment) VALUES (?, ?, ?, ?)', 'iiss', [$task_id, $user_id, $file_path, $comment]);
      return $this->model->lastInsertId();
    }
  }

  public function getByTask($task_id)
  {
    return $this->model->fetchAll('SELECT s.*, u.name as student_name, t.title as task_title FROM submissions s JOIN users u ON u.id = s.user_id JOIN tasks t ON t.id = s.task_id WHERE s.task_id = ? ORDER BY s.submitted_at DESC', 'i', [$task_id]);
  }

  public function getAll()
  {
    return $this->model->fetchAll('SELECT s.*, u.name as student_name, t.title as task_title FROM submissions s JOIN users u ON u.id = s.user_id JOIN tasks t ON t.id = s.task_id ORDER BY s.submitted_at DESC');
  }

  public function grade($id, $grade, $feedback = null)
  {
    $this->model->run('UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?', 'ssi', [$grade, $feedback, $id]);
    return true;
  }

  public function getByUserTask($user_id, $task_id)
  {
    return $this->model->fetchOne('SELECT * FROM submissions WHERE user_id = ? AND task_id = ?', 'ii', [$user_id, $task_id]);
  }
}
