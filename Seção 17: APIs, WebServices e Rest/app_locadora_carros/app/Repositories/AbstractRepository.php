<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    public function __construct(Model $model) {
        $this->model = $model;
    }

    public function selectAtributosRegistrosRelacionados($atributos)
    {
        $this->model = $this->model->with($atributos);
        // a query esta sendo montada
    }

    // melhorar, ajustar
    public function filtro($filtros)
    {
        $this->model = $this->model->where($filtros);
        // a query esta sendo montada
    }

    public function selectAtributos($atributos)
    {
        $this->model = $this->model->selectRaw($atributos);
        // a query esta sendo montada
    }

    public function getResultado()
    {
        // chamado quando a query esta montada
        return $this->model->get();
    }
}
