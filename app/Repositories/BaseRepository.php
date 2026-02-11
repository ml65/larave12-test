<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Найти запись по ID
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Создать новую запись
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Обновить запись
     */
    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Удалить запись
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Получить все записи
     */
    public function all(): Collection
    {
        return $this->model->all();
    }
}
