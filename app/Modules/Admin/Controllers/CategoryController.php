<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use CodeIgniter\API\ResponseTrait;

class CategoryController extends BaseController
{
    use ResponseTrait;

    /**
     * Return an array of resource objects, themselves in array format
     */
    public function index()
    {
        $model = new CategoryModel();
        $data = $model->select('categories.*, COUNT(services.id) as service_count')
                      ->join('services', 'services.category_id = categories.id', 'left')
                      ->groupBy('categories.id')
                      ->findAll();

        return $this->respond(['data' => $data]);
    }

    /**
     * Return the properties of a resource object
     */
    public function show($id = null)
    {
        $model = new CategoryModel();
        $data = $model->find($id);
        if (!$data) {
            return $this->failNotFound('No category found');
        }
        return $this->respond(['data' => $data]);
    }

    /**
     * Create a new resource object, from "posted" parameters
     */
    public function create()
    {
        $model = new CategoryModel();
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]|is_unique[categories.name]',
            'description' => 'permit_empty|string',
            'status' => 'permit_empty|in_list[active,inactive]'
        ];

        $data = $this->request->getJSON(true);

        if (!$this->validate($rules, $data)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data['status'] = $data['status'] ?? 'active';
        $newCategoryId = $model->insert($data);

        if ($model->errors()) {
            return $this->fail($model->errors());
        }
        
        $createdCategory = $model->find($newCategoryId);

        return $this->respondCreated(['data' => $createdCategory, 'message' => 'Category created successfully']);
    }

    /**
     * Add or update a model resource, from "posted" properties
     */
    public function update($id = null)
    {
        $model = new CategoryModel();
        $rules = [
            'name' => 'permit_empty|min_length[3]|max_length[255]|is_unique[categories.name,id,'.$id.']',
            'description' => 'permit_empty|string',
            'status' => 'permit_empty|in_list[active,inactive]'
        ];

        $data = $this->request->getJSON(true);

        if (!$this->validate($rules, $data)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if (empty($data)) {
            return $this->fail('No data to update.');
        }

        if (!$model->find($id)) {
            return $this->failNotFound('No category found');
        }

        $model->update($id, $data);

        $updatedCategory = $model->find($id);

        return $this->respondUpdated(['data' => $updatedCategory, 'message' => 'Category updated successfully']);
    }

    /**
     * Delete the designated resource object from the model
     */
    public function delete($id = null)
    {
        $model = new CategoryModel();
        if (!$model->find($id)) {
            return $this->failNotFound('No category found');
        }
        
        $model->delete($id);
        
        return $this->respondDeleted(['id' => $id, 'message' => 'Category deleted successfully']);
    }
}
