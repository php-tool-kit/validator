<?php

namespace Ptk\Validator;

use DateTimeInterface;
use RuntimeException;

/**
 * Utilitário para validar valores em variáveis PHP, através de uma
 * interface fluente (fluent interface) de configuração de regras.
 *
 * O fluxo básico de uso é:
 *
 * 1. Instanciar Validator;
 * 2. Encadear os métodos das regras desejadas (required(), empty(),
 *    nullable(), type(), is(), lessOrEqual(), less(), greatOrEqual(),
 *    great(), between(), contains(), file(), directory(), exists(),
 *    startswith(), endswith());
 * 3. Chamar validate($valor) para executar as regras configuradas;
 * 4. Consultar o resultado com result(), passed() ou failed().
 *
 * Exemplo de uso:
 *
 * ```php
 * $validator = (new Validator())
 *     ->required()
 *     ->type(Types::STRING)
 *     ->lessOrEqual(20)
 *     ->greatOrEqual(3);
 *
 * if ($validator->validate('everton')) {
 *     echo "Valor válido!";
 * } else {
 *     print_r($validator->failed());
 * }
 * ```
 *
 * @package Ptk\Validator
 * @author  Everton da Rosa <everton3x@gmail.com>
 * @license MIT
 *
 * @phpstan-type RuleResult bool|null
 * @phpstan-type ResultMap array<string, RuleResult>
 */
final class Validator
{
    /**
     * O valor que está sendo validado, definido em validate().
     *
     * @var mixed
     */
    private mixed $data;

    /**
     * Resultado de cada regra configurada: true (passou),
     * false (falhou) ou null (regra não configurada/testada).
     *
     * As chaves são os nomes das regras
     * (ex.: 'required', 'type', 'leq', etc.).
     *
     * @var ResultMap
     */
    private array $result = [];

    /**
     * Configuração da regra required (obrigatoriedade do valor).
     * Null significa que a regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $required = null;

    /**
     * Configuração da regra empty (se o valor pode ser vazio).
     * Null significa que a regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $empty = null;

    /**
     * Configuração da regra nullable (se o valor pode ser nulo).
     * Null significa que a regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $nullable = null;

    /**
     * Tipo esperado do valor, definido pela regra type().
     * Null significa que a regra não foi configurada.
     *
     * @var Types|null
     */
    private ?Types $type = null;

    /**
     * Nome da classe esperada para o valor, definido pela regra is().
     * Null significa que a regra não foi configurada.
     *
     * @var string|null
     */
    private ?string $is = null;

    /**
     * Valor limite superior (inclusivo) da regra lessOrEqual()
     * para comparações numéricas, de comprimento, de tamanho de
     * array e de data/hora.
     *
     * @var int|float|string|array<array-key, mixed>|DateTimeInterface|null
     */
    private null|int|float|string|array|DateTimeInterface $leq = null;

    /**
     * Valor limite superior (exclusivo) da regra less()
     * para comparações numéricas, de comprimento, de tamanho de
     * array e de data/hora.
     *
     * @var int|float|string|array<array-key, mixed>|DateTimeInterface|null
     */
    private null|int|float|string|array|DateTimeInterface $less = null;

    /**
     * Valor limite inferior (inclusivo) da regra greatOrEqual()
     * para comparações numéricas, de comprimento, de tamanho de
     * array e de data/hora.
     *
     * @var int|float|string|array<array-key, mixed>|DateTimeInterface|null
     */
    private null|int|float|string|array|DateTimeInterface $geq = null;

    /**
     * Valor limite inferior (exclusivo) da regra great()
     * para comparações numéricas, de comprimento, de tamanho de
     * array e de data/hora.
     *
     * @var int|float|string|array<array-key, mixed>|DateTimeInterface|null
     */
    private null|int|float|string|array|DateTimeInterface $great = null;

    /**
     * Limite inferior do intervalo configurado pela regra between().
     *
     * @var int|float|string|DateTimeInterface|null
     */
    private null|int|float|string|DateTimeInterface $betweenDown = null;

    /**
     * Limite superior do intervalo configurado pela regra between().
     *
     * @var int|float|string|DateTimeInterface|null
     */
    private null|int|float|string|DateTimeInterface $betweenUp = null;

    /**
     * Valor (string) ou lista de valores (array) que o valor
     * validado deve conter, configurado pela regra contains().
     *
     * @var array<array-key, mixed>|string|null
     */
    private null|array|string $contains = null;

    /**
     * Configuração da regra file (valida se o valor é um caminho
     * de arquivo existente). Null significa que a regra não foi
     * configurada.
     *
     * @var bool|null
     */
    private ?bool $file = null;

    /**
     * Configuração da regra directory (valida se o valor é um
     * caminho de diretório existente). Null significa que a regra
     * não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $directory = null;

    /**
     * Configuração da regra exists (valida se o valor é um caminho
     * que existe no sistema de arquivos). Null significa que a
     * regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $exists = null;

    /**
     * Substring com que o valor (string) deve começar,
     * configurada pela regra startswith(). Null significa que a
     * regra não foi configurada.
     *
     * @var string|null
     */
    private ?string $startswith = null;

    /**
     * Substring com que o valor (string) deve terminar,
     * configurada pela regra endswith(). Null significa que a
     * regra não foi configurada.
     *
     * @var string|null
     */
    private ?string $endswith = null;

    /**
     * Construtor da classe Validator.
     *
     * Não realiza nenhuma configuração inicial; as regras de
     * validação devem ser definidas pelos métodos fluentes
     * (required(), type(), min() etc.) antes de chamar validate().
     */
    public function __construct()
    {
    }

    /**
     * Executa as regras de validação configuradas sobre o valor informado.
     *
     * Armazena o valor a validar na propriedade interna de dados e,
     * para cada regra previamente configurada (e aplicável ao tipo do
     * valor), executa a verificação correspondente, armazenando o
     * resultado de cada regra em result().
     *
     * Regras configuradas com valor falsy (por exemplo, file(false),
     * directory(false) ou exists(false)) não são testadas e permanecem
     * com resultado null em result().
     *
     * @param mixed $data O valor a ser validado.
     *
     * @return bool True se nenhuma regra configurada falhou; caso contrário, false.
     */
    public function validate(mixed $data): bool
    {
        $this->data = $data;

        if ($this->required) $this->checkRequired();
        if (!is_null($this->empty) && is_string($this->data)) $this->checkEmptyStr();
        if (!is_null($this->empty) && is_array($this->data)) $this->checkEmptyArray();
        if (!is_null($this->nullable)) $this->checkNullable();
        if (!is_null($this->type)) $this->checkType();
        if (!is_null($this->is) && is_object($this->data)) $this->checkIs();
        if (is_int($this->leq) && is_string($this->data)) $this->checkLeqIntStr();
        if (is_string($this->leq) && is_string($this->data)) $this->checkLeqStrStr();
        if (is_array($this->leq) && is_array($this->data)) $this->checkLeqArrayArray();
        if (is_int($this->leq) && is_array($this->data)) $this->checkLeqIntArray();
        if (is_numeric($this->leq) && is_numeric($this->data)) $this->checkLeqNumeric();
        if (($this->leq instanceof DateTimeInterface) && ($this->data instanceof DateTimeInterface)) $this->checkLeqDateTime();
        if (is_int($this->less) && is_string($this->data)) $this->checkLessIntStr();
        if (is_string($this->less) && is_string($this->data)) $this->checkLessStrStr();
        if (is_array($this->less) && is_array($this->data)) $this->checkLessArrayArray();
        if (is_int($this->less) && is_array($this->data)) $this->checkLessIntArray();
        if (is_numeric($this->less) && is_numeric($this->data)) $this->checkLessNumeric();
        if (($this->less instanceof DateTimeInterface) && ($this->data instanceof DateTimeInterface)) $this->checkLessDateTime();
        if (is_int($this->geq) && is_string($this->data)) $this->checkGeqIntStr();
        if (is_string($this->geq) && is_string($this->data)) $this->checkGeqStrStr();
        if (is_array($this->geq) && is_array($this->data)) $this->checkGeqArrayArray();
        if (is_int($this->geq) && is_array($this->data)) $this->checkGeqIntArray();
        if (is_numeric($this->geq) && is_numeric($this->data)) $this->checkGeqNumeric();
        if (($this->geq instanceof DateTimeInterface) && ($this->data instanceof DateTimeInterface)) $this->checkGeqDateTime();
        if (is_int($this->great) && is_string($this->data)) $this->checkGreatIntStr();
        if (is_string($this->great) && is_string($this->data)) $this->checkGreatStrStr();
        if (is_array($this->great) && is_array($this->data)) $this->checkGreatArrayArray();
        if (is_int($this->great) && is_array($this->data)) $this->checkGreatIntArray();
        if (is_numeric($this->great) && is_numeric($this->data)) $this->checkGreatNumeric();
        if (($this->great instanceof DateTimeInterface) && ($this->data instanceof DateTimeInterface)) $this->checkGreatDateTime();
        if (!is_null($this->betweenDown) && !is_null($this->betweenUp) && !is_string($this->data)) $this->checkBetween();
        if (!is_null($this->betweenDown) && !is_null($this->betweenUp) && is_string($this->data)) $this->checkBetweenIntStr();
        if (is_string($this->betweenDown) && is_string($this->betweenUp) && is_string($this->data)) $this->checkBetweenStrStr();
        if (!is_null($this->contains) && is_array($this->data)) $this->checkContainsArray();
        if (!is_null($this->contains) && is_string($this->data)) $this->checkContainsStr();
        if ($this->file && is_string($this->data)) $this->checkFile();
        if ($this->directory && is_string($this->data)) $this->checkDirectory();
        if ($this->exists && is_string($this->data)) $this->checkExists();
        if (is_string($this->startswith) && is_string($this->data)) $this->checkStartsWith();
        if (is_string($this->endswith) && is_string($this->data)) $this->checkEndsWith();

        return empty($this->failed());
    }

    /**
     * Retorna o resultado de todas as regras configuradas ou não.
     *
     * Cada chave é o nome da regra (ex.: 'required', 'type', 'leq', etc.)
     * e o valor pode ser:
     *
     * - true: a regra passou;
     * - false: a regra falhou;
     * - null: a regra não foi configurada, portanto não foi testada.
     *
     * @return ResultMap O resultado completo das regras.
     */
    public function result(): array
    {
        return $this->result;
    }

    /**
     * Retorna os nomes das regras configuradas que passaram na validação.
     *
     * Regras não configuradas (resultado null) não são incluídas.
     *
     * @return list<string> Lista com os nomes das regras aprovadas.
     */
    public function passed(): array
    {
        /** @var list<string> */
        return array_keys($this->result, true, true);
    }

    /**
     * Retorna os nomes das regras configuradas que falharam na validação.
     *
     * Regras não configuradas (resultado null) não são consideradas
     * falhas e não são incluídas.
     *
     * @return list<string> Lista com os nomes das regras reprovadas.
     */
    public function failed(): array
    {
        /** @var list<string> */
        return array_keys($this->result, false, true);
    }

    /**
     * Define se o valor é obrigatório (não pode ser nulo nem ter
     * comprimento zero).
     *
     * @param bool $required Se true (padrão), o valor é obrigatório;
     *                       se false, a regra não impõe obrigatoriedade.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Verifica a regra de obrigatoriedade (required).
     *
     * O valor falha se for nulo ou vazio (verificação com empty()),
     * ou seja, se for null, '', [], 0, '0', false, etc.
     * O resultado é registrado em result() sob a chave 'required'.
     *
     * @return void
     */
    private function checkRequired(): void
    {
        $this->result['required'] = true;
        if (is_null($this->data) || empty($this->data)) {
            $this->result['required'] = false;
        }
    }

    /**
     * Define se o valor pode ser vazio.
     *
     * Com true (padrão), o valor pode ser vazio, mas não nulo.
     * Com false, o valor não pode ser vazio (nem nulo, quando a
     * verificação é aplicável).
     *
     * A verificação é realizada apenas para valores do tipo string
     * ou array.
     *
     * @param bool $empty Se true, permite valor vazio; se false, não permite.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function empty(bool $empty = true): self
    {
        $this->empty = $empty;
        return $this;
    }

    /**
     * Verifica a regra empty() para valores do tipo string.
     *
     * Falha apenas quando a regra exige valor não vazio
     * (empty(false)) e o valor é uma string vazia ('').
     * O resultado é registrado em result() sob a chave 'empty'.
     *
     * @return void
     */
    private function checkEmptyStr(): void
    {
        $this->result['empty'] = true;
        if (!$this->empty && $this->data === '') {
            $this->result['empty'] = false;
        }
    }

    /**
     * Verifica a regra empty() para valores do tipo array.
     *
     * Falha apenas quando a regra exige valor não vazio
     * (empty(false)) e o valor é um array vazio ([]).
     * O resultado é registrado em result() sob a chave 'empty'.
     *
     * @return void
     */
    private function checkEmptyArray(): void
    {
        $this->result['empty'] = true;
        if (!$this->empty && $this->data === []) {
            $this->result['empty'] = false;
            return;
        }
    }

    /**
     * Define se o valor pode ser nulo.
     *
     * @param bool $nullable Se true (padrão), permite valor nulo;
     *                       se false, o valor não pode ser nulo.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * Verifica a regra nullable.
     *
     * Falha apenas quando valores nulos não são permitidos
     * (nullable(false)) e o valor é nulo.
     * O resultado é registrado em result() sob a chave 'nullable'.
     *
     * @return void
     */
    private function checkNullable(): void
    {
        $this->result['nullable'] = true;
        if (!$this->nullable && is_null($this->data)) {
            $this->result['nullable'] = false;
        }
    }

    /**
     * Define o tipo esperado do valor, de acordo com o enum Types.
     *
     * O tipo é verificado pelas funções nativas do PHP
     * (is_bool(), is_numeric(), is_int(), etc.), de acordo com o
     * caso do enum informado.
     *
     * @param Types $type O tipo esperado do valor.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function type(Types $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Verifica o tipo do valor conforme o caso do enum Types
     * configurado em type().
     *
     * Usa as funções nativas do PHP correspondentes a cada caso
     * (is_bool(), is_numeric(), is_int(), is_float(), is_string(),
     * is_array(), is_object(), is_resource() e is_callable()).
     * O resultado é registrado em result() sob a chave 'type'.
     *
     * @return void
     */
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

    /**
     * Define que o valor deve ser uma instância da classe informada.
     *
     * A comparação é feita de forma exata (via get_class()), e não
     * com instanceof: instâncias de subclasses da classe informada
     * falham na validação.
     *
     * A verificação só é executada se o valor for um objeto.
     *
     * @param class-string $className O nome completo da classe esperada.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function is(string $className): self
    {
        $this->is = $className;
        return $this;
    }

    /**
     * Verifica se a classe do valor é exatamente igual à classe
     * configurada em is().
     *
     * A comparação é feita entre o retorno de get_class() do valor
     * e o nome da classe configurado, sem considerar herança
     * (não usa instanceof). O resultado é registrado em result()
     * sob a chave 'is'.
     *
     * @return void
     */
    private function checkIs(): void
    {
        if(is_object($this->data)) $this->result['is'] = get_class($this->data) === $this->is;
    }

    /**
     * Define um valor limite superior inclusivo (menor ou igual).
     *
     * A comparação depende do tipo do valor validado: números são
     * comparados por valor, strings pelo comprimento (mb_strlen),
     * arrays pelo número de elementos e datas/horas entre si.
     *
     * @param int|float|string|array<array-key, mixed>|DateTimeInterface $value O valor limite superior inclusivo.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function lessOrEqual(int|float|string|array|DateTimeInterface $value): self
    {
        $this->leq = $value;
        return $this;
    }

    /**
     * Verifica a regra lessOrEqual() quando o limite é int e o
     * valor é string: compara o comprimento da string (mb_strlen)
     * com o limite (<=).
     *
     * @return void
     */
    private function checkLeqIntStr(): void
    {
        if(is_string($this->data)) $this->result['leq'] = (mb_strlen($this->data) <= $this->leq);
    }

    /**
     * Verifica a regra lessOrEqual() quando o limite e o valor são
     * strings: compara o comprimento do valor (mb_strlen) com o
     * comprimento do limite (<=).
     *
     * @return void
     */
    private function checkLeqStrStr(): void
    {
        if(is_string($this->leq) && is_string($this->data)) $this->result['leq'] = (mb_strlen($this->data) <= mb_strlen($this->leq));
    }

    /**
     * Verifica a regra lessOrEqual() quando o limite é int e o
     * valor é array: compara o número de elementos do array com o
     * limite (<=).
     *
     * @return void
     */
    private function checkLeqIntArray(): void
    {
        if(is_array($this->data)) $this->result['leq'] = (sizeof($this->data) <= $this->leq);
    }

    /**
     * Verifica a regra lessOrEqual() quando o limite e o valor são
     * numéricos: compara os valores diretamente (<=).
     *
     * @return void
     */
    private function checkLeqNumeric(): void
    {
        $this->result['leq'] = ($this->data <= $this->leq);
    }

    /**
     * Verifica a regra lessOrEqual() quando o limite e o valor são
     * arrays: compara o número de elementos do valor com o número
     * de elementos do limite (<=).
     *
     * @return void
     */
    private function checkLeqArrayArray(): void
    {
        if(is_array($this->leq) && is_array($this->data)) $this->result['leq'] = (sizeof($this->data) <= sizeof($this->leq));
    }

    /**
     * Verifica a regra lessOrEqual() quando o limite e o valor são
     * instâncias de DateTimeInterface: compara as datas/horas
     * entre si (<=).
     *
     * @return void
     */
    private function checkLeqDateTime(): void
    {
        $this->result['leq'] = ($this->data <= $this->leq);
    }

    /**
     * Define um valor limite superior exclusivo (estritamente menor).
     *
     * A comparação depende do tipo do dado: números são comparados
     * por valor, strings pelo comprimento (mb_strlen), arrays pelo
     * número de elementos e datas/horas entre si.
     *
     * Obs.: para valores do tipo data/hora, a verificação utiliza
     * "<=" (menor ou igual), e não estritamente "<".
     *
     * @param int|float|string|array<array-key, mixed>|DateTimeInterface $value O valor limite superior exclusivo.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function less(int|float|string|array|DateTimeInterface $value): self
    {
        $this->less = $value;
        return $this;
    }

    /**
     * Verifica a regra less() quando o limite é int e o valor é
     * string: compara o comprimento da string (mb_strlen) com o
     * limite (<).
     *
     * @return void
     */
    private function checkLessIntStr(): void
    {
        if(is_string($this->data)) $this->result['less'] = (mb_strlen($this->data) < $this->less);
    }

    /**
     * Verifica a regra less() quando o limite e o valor são
     * numéricos: compara os valores diretamente (<).
     *
     * @return void
     */
    private function checkLessNumeric(): void
    {
        $this->result['less'] = ($this->data < $this->less);
    }

    /**
     * Verifica a regra less() quando o limite e o valor são
     * strings: compara o comprimento do valor (mb_strlen) com o
     * comprimento do limite (<).
     *
     * @return void
     */
    private function checkLessStrStr(): void
    {
        if(is_string($this->less) && is_string($this->data)) $this->result['less'] = (mb_strlen($this->data) < mb_strlen($this->less));
    }

    /**
     * Verifica a regra less() quando o limite é int e o valor é
     * array: compara o número de elementos do array com o limite (<).
     *
     * @return void
     */
    private function checkLessIntArray(): void
    {
        if(is_array($this->data)) $this->result['less'] = (sizeof($this->data) < $this->less);
    }

    /**
     * Verifica a regra less() quando o limite e o valor são arrays:
     * compara o número de elementos do valor com o número de
     * elementos do limite (<).
     *
     * @return void
     */
    private function checkLessArrayArray(): void
    {
        if(is_array($this->less) && is_array($this->data)) $this->result['less'] = (sizeof($this->data) < sizeof($this->less));
    }

    /**
     * Verifica a regra less() quando o limite e o valor são
     * instâncias de DateTimeInterface: compara as datas/horas
     * (utilizando "<=", ou seja, inclusivo).
     *
     * @return void
     */
    private function checkLessDateTime(): void
    {
        $this->result['less'] = ($this->data <= $this->less);
    }

    /**
     * Define um valor limite inferior inclusivo (maior ou igual).
     *
     * A comparação depende do tipo do dado: números são comparados
     * por valor, strings pelo comprimento (mb_strlen), arrays pelo
     * número de elementos e datas/horas entre si.
     *
     * @param int|float|string|array<array-key, mixed>|DateTimeInterface $value O valor limite inferior inclusivo.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function greatOrEqual(int|float|string|array|DateTimeInterface $value): self
    {
        $this->geq = $value;
        return $this;
    }

    /**
     * Verifica a regra greatOrEqual() quando o limite é int e o
     * valor é string: compara o comprimento da string (mb_strlen)
     * com o limite (>=).
     *
     * @return void
     */
    private function checkGeqIntStr(): void
    {
        if(is_string($this->data)) $this->result['geq'] = (mb_strlen($this->data) >= $this->geq);
    }

    /**
     * Verifica a regra greatOrEqual() quando o limite e o valor são
     * numéricos: compara os valores diretamente (>=).
     *
     * @return void
     */
    private function checkGeqNumeric(): void
    {
        $this->result['geq'] = ($this->data >= $this->geq);
    }

    /**
     * Verifica a regra greatOrEqual() quando o limite e o valor são
     * strings: compara o comprimento do valor (mb_strlen) com o
     * comprimento do limite (>=).
     *
     * @return void
     */
    private function checkGeqStrStr(): void
    {
        if(is_string($this->geq) && is_string($this->data)) $this->result['geq'] = (mb_strlen($this->data) >= mb_strlen($this->geq));
    }

    /**
     * Verifica a regra greatOrEqual() quando o limite é int e o
     * valor é array: compara o número de elementos do array com o
     * limite (>=).
     *
     * @return void
     */
    private function checkGeqIntArray(): void
    {
        if(is_array($this->data)) $this->result['geq'] = (sizeof($this->data) >= $this->geq);
    }

    /**
     * Verifica a regra greatOrEqual() quando o limite e o valor são
     * arrays: compara o número de elementos do valor com o número
     * de elementos do limite (>=).
     *
     * @return void
     */
    private function checkGeqArrayArray(): void
    {
        if(is_array($this->geq) && is_array($this->data)) $this->result['geq'] = (sizeof($this->data) >= sizeof($this->geq));
    }

    /**
     * Verifica a regra greatOrEqual() quando o limite e o valor são
     * instâncias de DateTimeInterface: compara as datas/horas
     * entre si (>=).
     *
     * @return void
     */
    private function checkGeqDateTime(): void
    {
        $this->result['geq'] = ($this->data >= $this->geq);
    }

    /**
     * Define um valor limite inferior exclusivo (estritamente maior).
     *
     * A comparação depende do tipo do dado: números são comparados
     * por valor, strings pelo comprimento (mb_strlen), arrays pelo
     * número de elementos e datas/horas entre si.
     *
     * @param int|float|string|array<array-key, mixed>|DateTimeInterface $value O valor limite inferior exclusivo.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function great(int|float|string|array|DateTimeInterface $value): self
    {
        $this->great = $value;
        return $this;
    }

    /**
     * Verifica a regra great() quando o limite é int e o valor é
     * string: compara o comprimento da string (mb_strlen) com o
     * limite (>).
     *
     * @return void
     */
    private function checkGreatIntStr(): void
    {
        if(is_string($this->data)) $this->result['great'] = (mb_strlen($this->data) > $this->great);
    }

    /**
     * Verifica a regra great() quando o limite e o valor são
     * numéricos: compara os valores diretamente (>).
     *
     * @return void
     */
    private function checkGreatNumeric(): void
    {
        $this->result['great'] = ($this->data > $this->great);
    }

    /**
     * Verifica a regra great() quando o limite e o valor são
     * strings: compara o comprimento do valor (mb_strlen) com o
     * comprimento do limite (>).
     *
     * @return void
     */
    private function checkGreatStrStr(): void
    {
        if(is_string($this->great) && is_string($this->data)) $this->result['great'] = (mb_strlen($this->data) > mb_strlen($this->great));
    }

    /**
     * Verifica a regra great() quando o limite é int e o valor é
     * array: compara o número de elementos do array com o limite (>).
     *
     * @return void
     */
    private function checkGreatIntArray(): void
    {
        if(is_array($this->data)) $this->result['great'] = (sizeof($this->data) > $this->great);
    }

    /**
     * Verifica a regra great() quando o limite e o valor são arrays:
     * compara o número de elementos do valor com o número de
     * elementos do limite (>).
     *
     * @return void
     */
    private function checkGreatArrayArray(): void
    {
        if(is_array($this->great) && is_array($this->data)) $this->result['great'] = (sizeof($this->data) > sizeof($this->great));
    }

    /**
     * Verifica a regra great() quando o limite e o valor são
     * instâncias de DateTimeInterface: compara as datas/horas
     * (utilizando ">", ou seja, exclusivo).
     *
     * @return void
     */
    private function checkGreatDateTime(): void
    {
        $this->result['great'] = ($this->data > $this->great);
    }

    /**
     * Define um intervalo (inclusivo) aceito para o valor.
     *
     * Os limites $down e $up devem ser exatamente do mesmo tipo
     * (verificado via gettype()). Se os tipos forem diferentes,
     * uma RuntimeException é lançada imediatamente, no momento da
     * configuração (antes mesmo de validate() ser executado).
     *
     * @param int|float|string|DateTimeInterface $down O limite inferior do intervalo (inclusivo).
     * @param int|float|string|DateTimeInterface $up   O limite superior do intervalo (inclusivo).
     *
     * @throws RuntimeException Se $down e $up forem de tipos diferentes.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function between(int|float|string|DateTimeInterface $down, int|float|string|DateTimeInterface $up): self
    {
        if (gettype($down) !== gettype($up)) {
            throw new \RuntimeException('Type of $down is differente of type of $up.');
        }
        $this->betweenDown = $down;
        $this->betweenUp = $up;
        return $this;
    }

    /**
     * Verifica a regra between() quando o valor não é string:
     * compara o valor com os limites do intervalo de forma
     * inclusiva (>= $down e <= $up).
     *
     * @return void
     */
    private function checkBetween(): void
    {
        $this->result['between'] = (($this->data >= $this->betweenDown) && ($this->data <= $this->betweenUp));
    }

    /**
     * Verifica a regra between() quando o valor é string e os
     * limites são numéricos: compara o comprimento da string
     * (mb_strlen) com os limites do intervalo de forma inclusiva.
     *
     * @return void
     */
    private function checkBetweenIntStr(): void
    {
        if(is_string($this->data)) $this->result['between'] = ((mb_strlen($this->data) >= $this->betweenDown) && (mb_strlen($this->data) <= $this->betweenUp));
    }

    /**
     * Verifica a regra between() quando o valor e os limites são
     * strings: compara o comprimento do valor (mb_strlen) com o
     * comprimento dos limites do intervalo de forma inclusiva.
     *
     * @return void
     */
    private function checkBetweenStrStr(): void
    {
        if(is_string($this->betweenDown) && is_string($this->betweenUp) && is_string($this->data)) $this->result['between'] = ((mb_strlen($this->data) >= mb_strlen($this->betweenDown)) && (mb_strlen($this->data) <= mb_strlen($this->betweenUp)));
    }

    /**
     * Define um valor (ou lista de valores) que o valor validado
     * deve conter.
     *
     * Com string, verifica se o valor contém a substring informada
     * (str_contains()). Com array, verifica se o valor informado
     * está presente na lista validada, usando in_array() sem
     * comparação estrita (portanto '1' e 1 são considerados
     * equivalentes).
     *
     * @param string|array<array-key, mixed> $contains A substring exigida ou a lista de valores aceitos.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function contains(string|array $contains): self
    {
        $this->contains = $contains;
        return $this;
    }

    /**
     * Verifica a regra contains() quando o valor é um array:
     * verifica se o valor (ou lista) configurado está presente no
     * array validado, usando in_array() sem comparação estrita.
     * O resultado é registrado em result() sob a chave 'contains'.
     *
     * @return void
     */
    private function checkContainsArray(): void
    {
        if(is_array($this->data)) $this->result['contains'] = in_array($this->contains, $this->data);
    }

    /**
     * Verifica a regra contains() quando o valor é uma string:
     * verifica se o valor contém a substring configurada
     * (str_contains()). O resultado é registrado em result() sob a
     * chave 'contains'.
     *
     * @return void
     */
    private function checkContainsStr(): void
    {
        if(is_string($this->contains) && is_string($this->data)) $this->result['contains'] = str_contains($this->data, $this->contains);
    }

    /**
     * Define se o valor (um caminho) deve ser validado como um
     * arquivo existente.
     *
     * A verificação só é executada quando o parâmetro é true;
     * passar false não gera nenhum teste (o resultado permanece
     * null em result()) e não representa uma validação negativa.
     *
     * @param bool $file Se true (padrão), valida se o caminho é um arquivo existente.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function file(bool $file = true): self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Verifica a regra file: valida se o valor (um caminho) é um
     * arquivo existente, usando is_file(). O resultado é registrado
     * em result() sob a chave 'file'.
     *
     * @return void
     */
    private function checkFile(): void
    {
        if(is_string($this->data)) $this->result['file'] = is_file($this->data);
    }

    /**
     * Define se o valor (um caminho) deve ser validado como um
     * diretório existente.
     *
     * A verificação só é executada quando o parâmetro é true;
     * passar false não gera nenhum teste (o resultado permanece
     * null em result()) e não representa uma validação negativa.
     *
     * @param bool $directory Se true (padrão), valida se o caminho é um diretório existente.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function directory(bool $directory = true): self
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * Verifica a regra directory: valida se o valor (um caminho) é
     * um diretório existente, usando is_dir(). O resultado é
     * registrado em result() sob a chave 'directory'.
     *
     * @return void
     */
    private function checkDirectory(): void
    {
        if(is_string($this->data)) $this->result['directory'] = is_dir($this->data);
    }

    /**
     * Define se o valor (um caminho) deve existir no sistema de
     * arquivos.
     *
     * A verificação só é executada quando o parâmetro é true;
     * passar false não gera nenhum teste (o resultado permanece
     * null em result()) e não representa uma validação negativa.
     *
     * @param bool $exists Se true (padrão), valida se o caminho existe.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function exists(bool $exists = true): self
    {
        $this->exists = $exists;
        return $this;
    }

    /**
     * Verifica a regra exists: valida se o valor (um caminho)
     * existe no sistema de arquivos, usando file_exists(). O
     * resultado é registrado em result() sob a chave 'exists'.
     *
     * @return void
     */
    private function checkExists(): void
    {
        if(is_string($this->data)) $this->result['exists'] = file_exists($this->data);
    }

    /**
     * Define que o valor (uma string) deve começar com a substring
     * informada.
     *
     * A verificação só é executada se o valor validado for uma
     * string.
     *
     * @param string $substr A substring com a qual o valor deve começar.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function startswith(string $substr): self
    {
        $this->startswith = $substr;
        return $this;
    }

    /**
     * Verifica a regra startswith: valida se o valor (uma string)
     * começa com a substring informada, usando
     * str_starts_with(). O resultado é registrado em result()
     * sob a chave 'startswith'.
     *
     * @return void
     */
    private function checkStartsWith(): void
    {
        if(is_string($this->startswith) && is_string($this->data)) $this->result['startswith'] = str_starts_with($this->data, $this->startswith);
    }

    /**
     * Define que o valor (uma string) deve terminar com a substring
     * informada.
     *
     * A verificação só é executada se o valor validado for uma
     * string.
     *
     * @param string $substr A substring com a qual o valor deve terminar.
     *
     * @return self Retorna a própria instância para permitir encadeamento de regras.
     */
    public function endswith(string $substr): self
    {
        $this->endswith = $substr;
        return $this;
    }

    /**
     * Verifica a regra endswith: valida se o valor (uma string)
     * termina com a substring informada, usando
     * str_ends_with(). O resultado é registrado em result()
     * sob a chave 'endswith'.
     *
     * @return void
     */
    private function checkEndsWith(): void
    {
        if(is_string($this->endswith) && is_string($this->data)) $this->result['endswith'] = str_ends_with($this->data, $this->endswith);
    }
}