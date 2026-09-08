<?php

namespace Ptk\Validator;

final class Validator
{
    private mixed $data;

    private array $result = [];

    private ?bool $required = null;
    private ?bool $empty = null;

    private ?bool $nullable = null;

    private ?Types $type = null;

    public function __construct()
    {}

    public function validate(mixed $data): bool
    {
        $this->data = $data;
        $this->checkRequired();
        $this->checkEmpty();
        $this->checkNullable();
        $this->checkType();

        return empty($this->failed());
    }

    public function result(): array
    {
        return $this->result;
    }

    public function passed(): array
    {
        return array_filter($this->result, function($result){
            return $result;
        });
    }
    
    public function failed(): array
    {
        return array_filter($this->result, function($result){
            if(is_null($result)) return false;
            return !$result;
        });
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    private function checkRequired(): void
    {
        $this->result['required'] = null;
        // Não testa
        if(is_null($this->required)) return;

        $this->result['required'] = true;
        // É obrigatório...
        if($this->required === true) {
            // ... então não pode ser nulo.
            if(is_null($this->data)) {
                $this->result['required'] = false;
                return;
            }
            if(mb_strlen($this->data) === 0) {
                $this->result['required'] = false;
            };
            return;
        }

        if($this->required === false) {
            // Não testa porque não é requerido
            return;
        }
    }

    public function empty(bool $empty = true): self
    {
        $this->empty = $empty;
        return $this;
    }

    private function checkEmpty(): void
    {
        $this->result['empty'] = null;
        
        // Não testa
        if(is_null($this->empty)) return;

        $this->result['empty'] = true;
        // Pode ser vazio ...
        if($this->empty === true) {
            // ... mas não pode ser nulo.
            if(is_null($this->data)) {
                $this->result['empty'] = false;
                return;
            }
            return;
        }

        // Não pode ser vazio...
        if($this->empty === false) {
            // ... também não pode ser nulo.
            if(is_null($this->data)) {
                $this->result['empty'] = false;
                return;
            }

            // Se for array
            if(is_array($this->data)){
                $this->result['empty'] = !($this->data === []);
                return;
            }
            // se não for array
            if(mb_strlen($this->data) === 0){
                $this->result['empty'] = false;
                return;
            }
        }
    }

    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    private function checkNullable(): void
    {
        $this->result['nullable'] = null;
        // Não testa
        if(is_null($this->nullable)) return;

        $this->result['nullable'] = true;
        // Pode ser nulo
        if($this->nullable === true) {
            // Não precisa testar
            return;
        }

        // Não pode ser nulo
        if($this->nullable === false) {
            if(is_null($this->data)) {
                $this->result['nullable'] = false;
            }
            return;
        }
    }

    public function type(Types $type): self
    {
        $this->type = $type;
        return $this;
    }

    private function checkType(): void
    {
        $this->result['type'] = null;
        // Não testa
        if(is_null($this->type)) return;

        // Não pode ser nullo
        if(is_null($this->data)) {
            $this->result['type'] = false;
            return;
        }

        // Testa o tipo
        $this->result['type'] = true;
        switch($this->type){
            case Types::BOOL:
                $this->result['type'] = is_bool($this->data);
                return;
            case Types::NUMERIC:
                $this->result['type'] = is_numeric($this->data);
                return;
            case Types::INT:
                $this->result['type'] = is_int($this->data);
                return;
            case Types::FLOAT:
                $this->result['type'] = is_float($this->data);
                return;
            case Types::STRING:
                $this->result['type'] = is_string($this->data);
                return;
            case Types::ARRAY:
                $this->result['type'] = is_array($this->data);
                return;
            case Types::OBJECT:
                $this->result['type'] = is_object($this->data);
                return;
            case Types::RESOURCE:
                $this->result['type'] = is_resource($this->data);
                return;
            case Types::CALLABLE:
                $this->result['type'] = is_callable($this->data);
                return;
            default:
                // Se não for nenhum dos tipos...
                $this->result['type'] = false;
                return;
        }
    }
}