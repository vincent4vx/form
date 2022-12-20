<?php

namespace Bench\Fixtures;

class SimpleFormVanilla
{
    public ?array $errors = null;
    public ?SimpleForm $value = null;

    public function submit(array $data): self
    {
        $value = new SimpleForm();
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'This value should not be blank.';
        } elseif (strlen($data['first_name']) < 2) {
            $errors['first_name'] = 'This value is too short. It should have 2 characters or more.';
        } else {
            $value->firstName = (string) $data['first_name'];
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'This value should not be blank.';
        } elseif (strlen($data['last_name']) < 2) {
            $errors['last_name'] = 'This value is too short. It should have 2 characters or more.';
        } else {
            $value->lastName = (string) $data['last_name'];
        }

        if (isset($data['age'])) {
            $value->age = (int) $data['age'];
        }

        $this->errors = $errors;
        $this->value = $value;

        return $this;
    }

    public function valid(): bool
    {
        return $this->errors === [];
    }
}
