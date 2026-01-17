<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\ServiceModel;
use CodeIgniter\API\ResponseTrait;

class ServiceController extends BaseController
{
    use ResponseTrait;

    /**
     * Get all services for a specific category
     */
    public function index($categoryId = null)
    {
        // Bypass ServiceModel's select to ensure all fields are retrieved
        $results = $this->db->table('services')
                            ->where('category_id', $categoryId)
                            ->get()
                            ->getResult(); // Get results as objects

        // TEMPORARY DIAGNOSTIC: Dump results to browser to inspect category_id
        echo "<pre>";
        var_dump($results);
        echo "</pre>";
        die(); // Halt execution to prevent further processing and just show this output
    }

    /**
     * Create a new service for a specific category
     */
    public function create($categoryId = null)
    {
        $model = new ServiceModel();
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|string',
            'status' => 'permit_empty|in_list[active,inactive]'
        ];

        $data = $this->request->getJSON(true);

        if (!$this->validate($rules, $data)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data['category_id'] = $categoryId;
        $data['status'] = $data['status'] ?? 'active';
        
        $newServiceId = $model->insert($data);

        if ($model->errors()) {
            return $this->fail($model->errors());
        }
        
        $createdService = $model->find($newServiceId);

        return $this->respondCreated(['data' => $createdService, 'message' => 'Service created successfully']);
    }

    /**
     * Update a service by its own ID
     */
    public function update($id = null)
    {
        $model = new ServiceModel();
        $rules = [
            'name' => 'permit_empty|min_length[3]|max_length[255]',
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
            return $this->failNotFound('No service found');
        }

        $model->update($id, $data);

        $updatedService = $model->find($id);

        return $this->respondUpdated(['data' => $updatedService, 'message' => 'Service updated successfully']);
    }

    /**
     * Delete a service by its own ID
     */
    public function delete($id = null)
    {
        $model = new ServiceModel();
        if (!$model->find($id)) {
            return $this->failNotFound('No service found');
        }
        
        $model->delete($id);
        
        return $this->respondDeleted(['id' => $id, 'message' => 'Service deleted successfully']);
    }
}
