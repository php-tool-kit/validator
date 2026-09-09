<?php

namespace Ptk\Validator;

use DateTimeInterface;

final class Validator
{
    private mixed $data;

    private array $result = [];

    private ?bool $required = null;

    private ?bool $empty = null;

    private ?bool $nullable = null;

    private ?Types $type = null;

    private ?string $is = null;

    private null|int|float|string|array|DateTimeInterface $leq = null;
    private null|int|float|string|array|DateTimeInterface $less = null;

    private null|int|float|string|array|DateTimeInterface $geq = null;
    private null|int|float|string|array|DateTimeInterface $great = null;

    private null|int|float|string|DateTimeInterface $betweenDown = null;

    private null|int|float|string|DateTimeInterface $betweenUp = null;

    private null|array|string $contains = null;

    private ?bool $file = null;

    private ?bool $directory = null;

    private ?bool $exists = null;

    private ?string $startswith = null;

    private ?string $endswith = null;


    public function __construct()
    {
    }

    public function validate(mixed $data): bool
    {
        $this->data = $data;
        
        if($this->required) $this->checkRequired();
        
        if(!is_null($this->empty) && is_string($this->data)) $this->checkEmptyStr();
        if(!is_null($this->empty) && is_array($this->data)) $this->checkEmptyArray();
        
        if(!is_null($this->nullable)) $this->checkNullable();
        
        if(!is_null($this->type)) $this->checkType();

        if(!is_null($this->is) && is_object($this->data)) $this->checkIs();
        
        if(is_int($this->leq) && is_string($this->data)) $this->checkLeqIntStr();
        if(is_string($this->leq) && is_string($this->data)) $this->checkLeqStrStr();
        if(is_array($this->leq) && is_array($this->data)) $this->checkLeqArrayArray();
        if(is_int($this->leq) && is_array($this->data)) $this->checkLeqIntArray();
        if(is_numeric($this->leq) && is_numeric($this->data)) $this->checkLeqNumeric();
        if(($this->leq instanceof DateTimeInterface) && ($this->data instanceof DateTimeInterface)) $this->checkLeqDateTime();
        
        if(is_int($this->less) && is_string($this->data)) $this->checkLessIntStr();
        if(is_string($this->less) && is_string($this->data)) $this->checkLessStrStr();
        if(is_array($this->less) && is_array($this->data)) $this->checkLessArrayArray();
        if(is_int($this->less) && is_array($this->data)) $this->checkLessIntArray();
        if(is_numeric($this->less) && is_numeric($this->data)) $this->checkLessNumeric();
        if(($this->less instanceof DateTimeInterface) && ($this->data instanceof DateTimeInterface)) $this->checkLessDateTime();

        if(is_int($this->geq) && is_string($this->data)) $this->checkGeqIntStr();
        if(is_string($this->geq) && is_string($this->data)) $this->checkGeqStrStr();
        if(is_array($this->geq) && is_array($this->data)) $this->checkGeqArrayArray();
        if(is_int($this->geq) && is_array($this->data)) $this->checkGeqIntArray();
        if(is_numeric($this->geq) && is_numeric($this->data)) $this->checkGeqNumeric();
        if(($this->geq instanceof DateTimeInterface) && ($this->data instanceof DateTimeInterface)) $this->checkGeqDateTime();

        if(is_int($this->great) && is_string($this->data)) $this->checkGreatIntStr();
        if(is_string($this->great) && is_string($this->data)) $this->checkGreatStrStr();
        if(is_array($this->great) && is_array($this->data)) $this->checkGreatArrayArray();
        if(is_int($this->great) && is_array($this->data)) $this->checkGreatIntArray();
        if(is_numeric($this->great) && is_numeric($this->data)) $this->checkGreatNumeric();
        if(($this->great instanceof DateTimeInterface) && ($this->data instanceof DateTimeInterface)) $this->checkGreatDateTime();

        if(!is_null($this->betweenDown) && !is_null($this->betweenUp) && !is_string($this->data)) $this->checkBetween();
        if(!is_null($this->betweenDown) && !is_null($this->betweenUp) && is_string($this->data)) $this->checkBetweenIntStr();
        if(is_string($this->betweenDown) && is_string($this->betweenUp) && is_string($this->data)) $this->checkBetweenStrStr();

        if(!is_null($this->contains) && is_array($this->data)) $this->checkContainsArray();
        if(!is_null($this->contains) && is_string($this->data)) $this->checkContainsStr();

        if($this->file && is_string($this->data)) $this->checkFile();
        
        if($this->directory && is_string($this->data)) $this->checkDirectory();
        
        if($this->exists && is_string($this->data)) $this->checkExists();
        
        if(is_string($this->startswith) && is_string($this->data)) $this->checkStartsWith();
        
        if(is_string($this->endswith) && is_string($this->data)) $this->checkEndsWith();

        return empty($this->failed());
    }

    public function result(): array
    {
        return $this->result;
    }

    public function passed(): array
    {
        return array_keys($this->result, true, true);
    }
    
    public function failed(): array
    {
        return array_keys($this->result, false, true);
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    private function checkRequired(): void
    {   
        $this->result['required'] = true;
        if(is_null($this->data) || empty($this->data)){
            $this->result['required'] = false;
        }
    }

    public function empty(bool $empty = true): self
    {
        $this->empty = $empty;
        return $this;
    }

    private function checkEmptyStr(): void
    {
        $this->result['empty'] = true;

        if(!$this->empty && $this->data === '') {
            $this->result['empty'] = false;
        }
    }
    private function checkEmptyArray(): void
    {
        $this->result['empty'] = true;

        if(!$this->empty && $this->data === []) {
            $this->result['empty'] = false;
            return;
        }

    }

    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    private function checkNullable(): void
    {
        $this->result['nullable'] = true;
        if(!$this->nullable && is_null($this->data)) {
            $this->result['nullable'] = false;
        }
    }

    public function type(Types $type): self
    {
        $this->type = $type;
        return $this;
    }

    private function checkType(): void
    {
        switch ($this->type) {
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
        }
    }

    public function is(string $className): self
    {
        $this->is = $className;
        return $this;
    }

    private function checkIs(): void
    {
        $this->result['is'] = get_class($this->data) === $this->is;
    }

    public function lessOrEqual(int|float|string|array|DateTimeInterface $value): self
    {
        $this->leq = $value;
        return $this;
    }

    private function checkLeqIntStr(): void
    {
        $this->result['leq'] = (mb_strlen($this->data) <= $this->leq);
    }
    
    private function checkLeqStrStr(): void
    {
        $this->result['leq'] = (mb_strlen($this->data) <= mb_strlen($this->leq));
    }
    private function checkLeqIntArray(): void
    {
        $this->result['leq'] = (sizeof($this->data) <= $this->leq);
    }
    
    private function checkLeqNumeric(): void
    {
        $this->result['leq'] = ($this->data <= $this->leq);
    }
    private function checkLeqArrayArray(): void
    {
        $this->result['leq'] = (sizeof($this->data) <= sizeof($this->leq));
    }
    
    private function checkLeqDateTime(): void
    {
        $this->result['leq'] = ($this->data <= $this->leq);
    }

    public function less(int|float|string|array|DateTimeInterface $value): self
    {
        $this->less = $value;
        return $this;
    }

    private function checkLessIntStr(): void
    {
        $this->result['less'] = (mb_strlen($this->data) < $this->less);
    }
    
    private function checkLessNumeric(): void
    {
        $this->result['less'] = ($this->data < $this->less);
    }
    
    private function checkLessStrStr(): void
    {
        $this->result['less'] = (mb_strlen($this->data) < mb_strlen($this->less));
    }
    private function checkLessIntArray(): void
    {
        $this->result['less'] = (sizeof($this->data) < $this->less);
    }
    private function checkLessArrayArray(): void
    {
        $this->result['less'] = (sizeof($this->data) < sizeof($this->less));
    }

    private function checkLessDateTime(): void
    {
        $this->result['less'] = ($this->data <= $this->less);
    }

    public function greatOrEqual(int|float|string|array|DateTimeInterface $value): self
    {
        $this->geq = $value;
        return $this;
    }

    private function checkGeqIntStr(): void
    {
        $this->result['geq'] = (mb_strlen($this->data) >= $this->geq);
    }
    private function checkGeqNumeric(): void
    {
        $this->result['geq'] = ($this->data >= $this->geq);
    }
    
    private function checkGeqStrStr(): void
    {
        $this->result['geq'] = (mb_strlen($this->data) >= mb_strlen($this->geq));
    }
    private function checkGeqIntArray(): void
    {
        $this->result['geq'] = (sizeof($this->data) >= $this->geq);
    }
    private function checkGeqArrayArray(): void
    {
        $this->result['geq'] = (sizeof($this->data) >= sizeof($this->geq));
    }
    
    private function checkGeqDateTime(): void
    {
        $this->result['geq'] = ($this->data >= $this->geq);
    }

    public function great(int|float|string|array|DateTimeInterface $value): self
    {
        $this->great = $value;
        return $this;
    }

    private function checkGreatIntStr(): void
    {
        $this->result['great'] = (mb_strlen($this->data) > $this->great);
    }
    private function checkGreatNumeric(): void
    {
        $this->result['great'] = ($this->data > $this->great);
    }
    
    private function checkGreatStrStr(): void
    {
        $this->result['great'] = (mb_strlen($this->data) > mb_strlen($this->great));
    }
    private function checkGreatIntArray(): void
    {
        $this->result['great'] = (sizeof($this->data) > $this->great);
    }
    private function checkGreatArrayArray(): void
    {
        $this->result['great'] = (sizeof($this->data) > sizeof($this->great));
    }
    
    private function checkGreatDateTime(): void
    {
        $this->result['great'] = ($this->data > $this->great);
    }

    public function between(int|float|string|DateTimeInterface $down, int|float|string|DateTimeInterface $up): self
    {
        if (gettype($down) !== gettype($up)) {
            throw new \RuntimeException('Type of $down is differente of type of $up.');
        }

        $this->betweenDown = $down;
        $this->betweenUp = $up;
        return $this;
    }

    private function checkBetween(): void
    {
        $this->result['between'] = (($this->data >= $this->betweenDown) && ($this->data <= $this->betweenUp));
    }
    
    private function checkBetweenIntStr(): void
    {
        $this->result['between'] = ((mb_strlen($this->data) >= $this->betweenDown) && (mb_strlen($this->data) <= $this->betweenUp));
    }
    
    private function checkBetweenStrStr(): void
    {
        $this->result['between'] = ((mb_strlen($this->data) >= mb_strlen($this->betweenDown)) && (mb_strlen($this->data) <= mb_strlen($this->betweenUp)));
    }

    public function contains(string|array $contains): self
    {
        $this->contains = $contains;
        return $this;
    }

    private function checkContainsArray(): void
    {
        $this->result['contains'] = in_array($this->contains, $this->data);
    }
    
    private function checkContainsStr(): void
    {
        $this->result['contains'] = str_contains($this->data, $this->contains);
    }

    public function file(bool $file = true): self
    {
        $this->file = $file;
        return $this;
    }

    private function checkFile(): void
    {
        $this->result['file'] = is_file($this->data);
    }

    public function directory(bool $directory = true): self
    {
        $this->directory = $directory;
        return $this;
    }

    private function checkDirectory(): void
    {
        $this->result['directory'] = is_dir($this->data);
    }

    public function exists(bool $exists = true): self
    {
        $this->exists = $exists;
        return $this;
    }

    private function checkExists(): void
    {
        $this->result['exists'] = file_exists($this->data);
    }

    public function startswith(string $substr): self
    {
        $this->startswith = $substr;
        return $this;
    }

    private function checkStartsWith(): void
    {
        $this->result['startswith'] = str_starts_with($this->data, $this->startswith);
    }
    
    public function endswith(string $substr): self
    {
        $this->endswith = $substr;
        return $this;
    }

    private function checkEndsWith(): void
    {
        $this->result['endswith'] = str_ends_with($this->data, $this->endswith);
    }
}