<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Admin\Models\CoverageModel;

class CoverageController extends BaseController
{
    protected $coverageModel;

    public function __construct()
    {
        $this->coverageModel = new CoverageModel();
    }

    public function index()
    {
        $data = $this->coverageModel->orderBy('id', 'DESC')->findAll();

        return $this->response->setJSON([
            'status' => 200,
            'data'   => $data
        ]);
    }

    public function create()
    {
        $input = $this->request->getJSON(true);

        $rules = [
            'name' => 'required|min_length[2]',
        ];

        if (!$this->validateData($input, $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'   => 422,
                'messages' => $this->validator->getErrors()
            ]);
        }

        if (!$this->coverageModel->insert($input)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 500,
                'message' => 'Failed to create coverage'
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 201,
            'message' => 'Coverage created'
        ]);
    }

    public function update($id)
    {
        $coverage = $this->coverageModel->find($id);

        if (!$coverage) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Coverage not found'
            ]);
        }

        $input = $this->request->getJSON(true);

        if (!$this->coverageModel->update($id, $input)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 500,
                'message' => 'Failed to update coverage'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 200,
            'message' => 'Coverage updated'
        ]);
    }

    public function delete($id)
    {
        $coverage = $this->coverageModel->find($id);

        if (!$coverage) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'message' => 'Coverage not found'
            ]);
        }

        if (!$this->coverageModel->delete($id)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 500,
                'message' => 'Failed to delete coverage'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 200,
            'message' => 'Coverage deleted'
        ]);
    }
}