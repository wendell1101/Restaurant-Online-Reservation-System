<?php
class Category extends Connection
{
    private $data;
    private $errors = [];
    private static $fields = ['name'];
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $sql = "SELECT * FROM categories";
        $stmt = $this->conn->query($sql);
        $stmt->execute();
        return $categories = $stmt->fetchAll();
    }

    public function create($data)
    {
        $this->data = $data;
        $this->validate();
        $this->checkIfHasError();
    }
    //Error handling
    // Validate category name
    public function validate()
    {
        foreach (self::$fields as $field) {
            if (!array_key_exists($field, $this->data)) {
                trigger_error("$field must not be empty");
                return;
            }

            $this->validateCategoryName();

            return $this->errors;
        }
    }

    private function validateCategoryName()
    {
        // check if empty
        $val = $this->data['name'];
        if (empty($val)) {
            $this->addError('name', 'Category name must not be empty');
        }
    }
    //add error

    private function addError($key, $val)
    {
        $this->errors[$key] = $val;
    }

    //Check if no more errors then insert data
    private function checkIfHasError()
    {
        if (!array_filter($this->errors)) {
            $name = $this->data['name'];
            $slug = slugify($name);
            $sql = "INSERT INTO categories (name, slug) VALUES (:name, :slug)";
            $stmt = $this->conn->prepare($sql);
            $run = $stmt->execute(['name' => $name, 'slug' => $slug]);
            if ($run) {
                message('success', 'A new category has been created');
                redirect('categories.php');
            }
        }
    }

    // delete category
    public function delete($id)
    {
        $sql = "DELETE FROM categories WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $deleted = $stmt->execute(['id' => $id]);
        if ($deleted) {
            message('success', 'A new category has been deleted');
            redirect('categories.php');
        } else {
            echo 'error in delete';
        }
    }
    // get single category
    public function getCategory($id)
    {
        $sql = "SELECT * FROM categories WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch();
        return $category;
    }

    //update category
    public function update($data)
    {
        $this->data = $data;
        $this->validate();
        $this->updateCategory();
    }
    private function updateCategory()
    {
        $name = $this->data['name'];
        $slug = slugify($name);
        $id = $this->data['id'];
        if (!array_filter($this->errors)) {
            $sql = "UPDATE categories set name=:name, slug=:slug WHERE id=:id";
            $stmt = $this->conn->prepare($sql);
            $updated = $stmt->execute(['name' => $name, 'slug' => $slug, 'id' => $id]);
            if ($updated) {
                message('success', 'A new category has been updated');
                redirect('categories.php');
            }
        }
    }
}
