<?php

namespace Ptk\Validator;

use DateTimeInterface;

/**
 * Classe responsável por validar valores (dados) a partir de um conjunto
 * de regras configuráveis, utilizando uma interface fluente (fluent
 * interface / method chaining).
 *
 * O fluxo de uso típico é:
 * 1. Instanciar a classe;
 * 2. Configurar as regras desejadas através dos métodos públicos
 *    (required(), empty(), nullable(), type(), is(), min(), max(),
 *    between(), contains(), file(), directory(), exists(), startswith()
 *    e endswith());
 * 3. Chamar validate() passando o dado a ser validado;
 * 4. Consultar o resultado através de result(), passed() ou failed().
 *
 * Cada regra configurada é armazenada internamente e, ao chamar
 * validate(), cada regra é testada pelo seu respectivo método
 * checkXxx(), que grava o resultado no array $result, indexado pelo
 * nome da regra: true (passou), false (falhou) ou null (regra não
 * configurada e, portanto, não testada).
 *
 * Exemplo geral:
 *
 * ```php
 * $validator = (new Validator())
 *     ->required()
 *     ->type(Types::STRING)
 *     ->min(3)
 *     ->max(20);
 *
 * if ($validator->validate('everton')) {
 *     echo "Valor válido!";
 * } else {
 *     print_r($validator->failed()); // ['max' => false], por exemplo
 * }
 * ```
 *
 * @package Ptk\Validator
 */
final class Validator
{
    /**
     * Armazena o dado que está sendo validado na chamada atual de
     * validate(). É preenchido por validate() e consumido pelos métodos
     * checkXxx() durante a execução das regras.
     *
     * @var mixed
     */
    private mixed $data;

    /**
     * Armazena o resultado de cada regra testada, indexado pelo nome da
     * regra (ex.: 'required', 'empty', 'type', etc.). Cada valor pode
     * ser true (passou), false (falhou) ou null (regra não configurada,
     * portanto não testada).
     *
     * Exemplo de conteúdo após uma validação:
     *
     * ```php
     * [
     *     'required' => true,
     *     'type'     => true,
     *     'min'      => null, // regra não configurada
     *     'max'      => false,
     * ]
     * ```
     *
     * @var array<string, ?bool>
     */
    private array $result = [];

    /**
     * Define se o dado é obrigatório (não pode ser nulo ou vazio).
     * Configurado pelo método required(). Null significa que a regra
     * não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $required = null;

    /**
     * Define se o dado pode ser vazio. Configurado pelo método empty().
     * Null significa que a regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $empty = null;

    /**
     * Define se o dado pode ser nulo. Configurado pelo método
     * nullable(). Null significa que a regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $nullable = null;

    /**
     * Define o tipo de dado esperado, de acordo com o enum Types.
     * Configurado pelo método type(). Null significa que a regra não
     * foi configurada.
     *
     * @var Types|null
     */
    private ?Types $type = null;

    /**
     * Define o nome da classe que o dado deve ser instância (comparação
     * exata, sem considerar herança). Configurado pelo método is().
     * Null significa que a regra não foi configurada.
     *
     * @var string|null
     */
    private ?string $is = null;

    /**
     * Valor mínimo aceito para o dado (numérico, comprimento de string
     * ou data/hora). Configurado pelo método min(). Null significa que
     * a regra não foi configurada.
     *
     * @var int|float|string|DateTimeInterface|null
     */
    private null|int|float|string|DateTimeInterface $min = null;

    /**
     * Valor máximo aceito para o dado (numérico, comprimento de string
     * ou data/hora). Configurado pelo método max(). Null significa que
     * a regra não foi configurada.
     *
     * @var int|float|string|DateTimeInterface|null
     */
    private null|int|float|string|DateTimeInterface $max = null;

    /**
     * Limite inferior do intervalo aceito ao usar between(). Null
     * significa que a regra não foi configurada.
     *
     * @var int|float|string|DateTimeInterface|null
     */
    private null|int|float|string|DateTimeInterface $betweenDown = null;

    /**
     * Limite superior do intervalo aceito ao usar between(). Null
     * significa que a regra não foi configurada.
     *
     * @var int|float|string|DateTimeInterface|null
     */
    private null|int|float|string|DateTimeInterface $betweenUp = null;

    /**
     * Valor (ou lista de valores) que o dado deve conter. Configurado
     * pelo método contains(). Null significa que a regra não foi
     * configurada.
     *
     * @var array<mixed>|string|null
     */
    private null|array|string $contains = null;

    /**
     * Define se o dado (um caminho) deve ser validado como um arquivo
     * existente. Configurado pelo método file(). Null significa que a
     * regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $file = null;

    /**
     * Define se o dado (um caminho) deve ser validado como um diretório
     * existente. Configurado pelo método directory(). Null significa
     * que a regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $directory = null;

    /**
     * Define se o dado (um caminho) deve ser validado quanto à sua
     * existência no sistema de arquivos. Configurado pelo método
     * exists(). Null significa que a regra não foi configurada.
     *
     * @var bool|null
     */
    private ?bool $exists = null;

    /**
     * Substring que o dado deve conter no início. Configurado pelo
     * método startswith(). Null significa que a regra não foi
     * configurada.
     *
     * @var string|null
     */
    private ?string $startswith = null;

    /**
     * Substring que o dado deve conter no final. Configurado pelo
     * método endswith(). Null significa que a regra não foi
     * configurada.
     *
     * @var string|null
     */
    private ?string $endswith = null;


    /**
     * Construtor da classe Validator.
     *
     * Não recebe parâmetros. As regras de validação devem ser
     * configuradas através dos métodos fluentes disponíveis antes de
     * chamar validate().
     *
     * Exemplo:
     *
     * ```php
     * $validator = new Validator();
     * ```
     */
    public function __construct()
    {
    }

    /**
     * Executa todas as validações configuradas sobre o dado informado.
     *
     * Armazena o dado internamente, executa cada método checkXxx()
     * responsável por testar uma regra específica e retorna se todas as
     * regras configuradas passaram (isto é, se failed() retornar um
     * array vazio).
     *
     * Exemplo:
     *
     * ```php
     * $validator = (new Validator())->required()->type(Types::INT);
     *
     * var_dump($validator->validate(42));  // true
     * var_dump($validator->validate('a')); // false (falhou a regra type)
     * var_dump($validator->failed());      // ['type' => false]
     * ```
     *
     * @param mixed $data Dado a ser validado.
     * @return bool Retorna true se todas as regras configuradas
     *              passaram (nenhuma falha em failed()), false caso
     *              contrário.
     */
    public function validate(mixed $data): bool
    {
        $this->data = $data;
        $this->checkRequired();
        $this->checkEmpty();
        $this->checkNullable();
        $this->checkType();
        $this->checkIs();
        $this->checkMin();
        $this->checkMax();
        $this->checkBetween();
        $this->checkContains();
        $this->checkFile();
        $this->checkDirectory();
        $this->checkExists();
        $this->checkStartsWith();
        $this->checkEndsWith();

        return empty($this->failed());
    }

    /**
     * Retorna o resultado completo da última validação executada.
     *
     * O array retornado é indexado pelo nome de cada regra (ex.:
     * 'required', 'empty', 'nullable', 'type', 'is', 'min', 'max',
     * 'between', 'contains', 'file', 'directory', 'exists',
     * 'startswith', 'endswith') e cada valor pode ser true (passou),
     * false (falhou) ou null (regra não configurada/não testada).
     *
     * Exemplo:
     *
     * ```php
     * $validator = (new Validator())->required()->validate('abc');
     * print_r($validator->result());
     * // ['required' => true, 'empty' => null, 'nullable' => null, ...]
     * ```
     *
     * @return array<string, ?bool> Array associativo com o resultado de cada regra.
     */
    public function result(): array
    {
        return $this->result;
    }

    /**
     * Retorna apenas as regras que passaram na última validação.
     *
     * Retorna os nomes (chaves) das regras cujo resultado foi
     * estritamente true. Regras não configuradas (null) não aparecem.
     *
     * Exemplo:
     *
     * ```php
     * $validator = (new Validator())->required()->min(3)->validate('abc');
     * print_r($validator->passed()); // ['required', 'min']
     * ```
     *
     * @return array<string> Array com os nomes das regras que passaram.
     */
    public function passed(): array
    {
        return array_keys($this->result, true, true);
    }
    
    /**
     * Retorna apenas as regras que falharam na última validação.
     *
     * Retorna os nomes (chaves) das regras cujo resultado foi
     * estritamente false. Regras não configuradas (valor null) não são
     * consideradas falhas e não aparecem no retorno.
     *
     * Exemplo:
     *
     * ```php
     * $validator = (new Validator())->type(Types::INT)->validate('texto');
     * print_r($validator->failed()); // ['type']
     * ```
     *
     * @return array<string> Array com os nomes das regras que falharam.
     */
    public function failed(): array
    {
        return array_keys($this->result, false, true);
    }

    /**
     * Configura a regra de obrigatoriedade do dado.
     *
     * Quando $required for true, o dado não poderá ser nulo nem ter
     * comprimento zero (mb_strlen() === 0). Quando $required for
     * false, nenhuma verificação adicional é realizada.
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->required()->validate('abc'); // true
     * (new Validator())->required()->validate(null);  // false
     * (new Validator())->required()->validate('');    // false
     * ```
     *
     * @param bool $required Se o dado é obrigatório. Padrão: true.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Testa a regra de obrigatoriedade configurada em $required.
     *
     * Armazena o resultado em $this->result['required']. Se a regra não
     * foi configurada (null), o resultado permanece null. Se
     * $required for true, o dado não pode ser nulo nem ter comprimento
     * (mb_strlen) igual a zero.
     *
     * @return void
     */
    private function checkRequired(): void
    {
        $this->result['required'] = null;
        // Não testa
        if (is_null($this->required)) {
            return;
        }

        $this->result['required'] = true;
        // É obrigatório...
        if ($this->required === true) {
            // ... então não pode ser nulo.
            if (is_null($this->data)) {
                $this->result['required'] = false;
                return;
            }
            // @phpstan-ignore argument.type
            if (mb_strlen($this->data) === 0) {
                $this->result['required'] = false;
            };
            return;
        }
    }

    /**
     * Configura a regra que define se o dado pode ser vazio.
     *
     * Quando $empty for true, o dado pode ser vazio, mas não pode ser
     * nulo. Quando $empty for false, o dado não pode ser nulo nem
     * vazio (array vazio ou string/valor com comprimento zero).
     *
     * Exemplos:
     *
     * ```php
     * // Com empty(true): o dado pode ser vazio, mas não nulo.
     * (new Validator())->empty()->validate('');    // true
     * (new Validator())->empty()->validate(null);  // false
     *
     * // Com empty(false): o dado não pode ser nulo nem vazio.
     * (new Validator())->empty(false)->validate([]);   // false
     * (new Validator())->empty(false)->validate('');   // false
     * (new Validator())->empty(false)->validate('ok'); // true
     * ```
     *
     * @param bool $empty Se o dado pode ser vazio. Padrão: true.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function empty(bool $empty = true): self
    {
        $this->empty = $empty;
        return $this;
    }

    /**
     * Testa a regra configurada em $empty.
     *
     * Armazena o resultado em $this->result['empty']. Se a regra não
     * foi configurada (null), o resultado permanece null. Quando
     * $empty for true, apenas verifica se o dado não é nulo. Quando
     * $empty for false, verifica também se o dado é um array vazio ou
     * possui comprimento (mb_strlen) igual a zero.
     *
     * @return void
     */
    private function checkEmpty(): void
    {
        $this->result['empty'] = null;
        
        // Não testa
        if (is_null($this->empty)) {
            return;
        }

        $this->result['empty'] = true;
        // Pode ser vazio ...
        if ($this->empty === true) {
            // ... mas não pode ser nulo.
            if (is_null($this->data)) {
                $this->result['empty'] = false;
                return;
            }
            return;
        }

        // Não pode ser vazio...
            // ... também não pode ser nulo.
        if (is_null($this->data)) {
            $this->result['empty'] = false;
            return;
        }

            // Se for array
        if (is_array($this->data)) {
            $this->result['empty'] = !($this->data === []);
            return;
        }
            // se não for array
            // @phpstan-ignore argument.type
        if (mb_strlen($this->data) === 0) {
            $this->result['empty'] = false;
            return;
        }
    }

    /**
     * Configura a regra que define se o dado pode ser nulo.
     *
     * Quando $nullable for true, o dado pode ser nulo (a regra passa
     * sem teste adicional). Quando $nullable for false, o dado não
     * pode ser nulo.
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->nullable()->validate(null);      // true
     * (new Validator())->nullable(false)->validate(null); // false
     * (new Validator())->nullable(false)->validate('x');  // true
     * ```
     *
     * @param bool $nullable Se o dado pode ser nulo. Padrão: true.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * Testa a regra configurada em $nullable.
     *
     * Armazena o resultado em $this->result['nullable']. Se a regra não
     * foi configurada (null), o resultado permanece null. Quando
     * $nullable for false, o dado não pode ser nulo.
     *
     * @return void
     */
    private function checkNullable(): void
    {
        $this->result['nullable'] = null;
        // Não testa
        if (is_null($this->nullable)) {
            return;
        }

        $this->result['nullable'] = true;
        // Pode ser nulo
        if ($this->nullable === true) {
            // Não precisa testar
            return;
        }

        // Não pode ser nulo
        if (is_null($this->data)) {
            $this->result['nullable'] = false;
        }
    }

    /**
     * Configura a regra de verificação de tipo do dado, de acordo com
     * o enum Types.
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->type(Types::INT)->validate(10);      // true
     * (new Validator())->type(Types::INT)->validate('10');    // false
     * (new Validator())->type(Types::STRING)->validate('a');  // true
     * (new Validator())->type(Types::ARRAY)->validate([]);    // true
     * (new Validator())->type(Types::BOOL)->validate(null);   // false (dado nulo falha sempre)
     * ```
     *
     * @param Types $type Tipo esperado para o dado.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function type(Types $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Testa a regra de tipo configurada em $type.
     *
     * Armazena o resultado em $this->result['type']. Se a regra não foi
     * configurada (null), o resultado permanece null. O dado não pode
     * ser nulo. A verificação é feita usando a função nativa do PHP
     * correspondente ao caso do enum Types informado (is_bool,
     * is_numeric, is_int, is_float, is_string, is_array, is_object,
     * is_resource ou is_callable).
     *
     * @return void
     */
    private function checkType(): void
    {
        $this->result['type'] = null;
        // Não testa
        if (is_null($this->type)) {
            return;
        }

        // Não pode ser nullo
        if (is_null($this->data)) {
            $this->result['type'] = false;
            return;
        }

        // Testa o tipo
        $this->result['type'] = true;
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
            // @codeCoverageIgnoreStart
            default:
                // Se não for nenhum dos tipos...
                $this->result['type'] = false;
                return;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Configura a regra que verifica se o dado é uma instância da
     * classe informada.
     *
     * Atenção: a comparação é EXATA (o nome da classe do dado deve ser
     * idêntico ao informado). Instâncias de subclasses da classe
     * informada NÃO passam na validação. Para verificar herança, use
     * `assert($dado instanceof MinhaClasse)` ou outra abordagem.
     *
     * Exemplos:
     *
     * ```php
     * class Animal {}
     * class Cachorro extends Animal {}
     *
     * (new Validator())->is(Animal::class)->validate(new Animal());  // true
     * (new Validator())->is(Animal::class)->validate(new Cachorro()); // false!
     * (new Validator())->is(DateTime::class)->validate(new DateTime()); // true
     * ```
     *
     * @param string $className Nome completo (com namespace) da classe
     *                           esperada.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function is(string $className): self
    {
        $this->is = $className;
        return $this;
    }

    /**
     * Testa a regra configurada em $is.
     *
     * Armazena o resultado em $this->result['is']. Se a regra não foi
     * configurada (null), o resultado permanece null. Utiliza
     * get_class() sobre o dado e compara com o nome de classe
     * informado (comparação exata, sem considerar subclasses).
     *
     * @return void
     */
    private function checkIs(): void
    {
        $this->result['is'] = null;
        // Não testa
        if (is_null($this->is)) {
            return;
        }

        // Testa se a variável é uma classe com o mesmo nome
        // @phpstan-ignore argument.type
        $this->result['is'] = get_class($this->data) === $this->is;
    }

    /**
     * Configura o valor mínimo aceito para o dado.
     *
     * Pode ser um número (comparado diretamente), uma string (quando o
     * dado também é string, compara o comprimento do dado com o
     * comprimento ou o valor numérico do limite) ou uma data/hora
     * (DateTimeInterface, comparada diretamente).
     *
     * Exemplos:
     *
     * ```php
     * // Dado numérico: compara o valor.
     * (new Validator())->min(10)->validate(15); // true
     * (new Validator())->min(10)->validate(5);  // false
     *
     * // Dado string: compara o comprimento com o valor do limite.
     * (new Validator())->min(3)->validate('abcd');  // true (4 >= 3)
     * (new Validator())->min(3)->validate('ab');    // false (2 < 3)
     * (new Validator())->min('xyz')->validate('abcdef'); // true (6 >= 3)
     *
     * // Dado data/hora: compara as datas.
     * $hoje = new DateTimeImmutable();
     * (new Validator())->min(new DateTimeImmutable('2000-01-01'))
     *     ->validate($hoje); // true
     * ```
     *
     * @param int|float|string|DateTimeInterface $min Valor mínimo
     *        aceito.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function min(int|float|string|DateTimeInterface $min): self
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Testa a regra de valor mínimo configurada em $min.
     *
     * Armazena o resultado em $this->result['min']. Se a regra não foi
     * configurada (null), o resultado permanece null. Se o dado for
     * numérico, compara o valor diretamente; se for string, compara o
     * comprimento (mb_strlen); caso contrário, assume comparação de
     * data/hora.
     *
     * @return void
     */
    private function checkMin(): void
    {
        $this->result['min'] = null;
        // Não testa
        if (is_null($this->min)) {
            return;
        }

        // Se for int ou float
        if (is_numeric($this->data)) {
            $this->result['min'] = !($this->data < $this->min);
            return;
        }

        // Se for string
        if (is_string($this->data)) {
            if(is_string($this->min)){
                $this->result['min'] = !(mb_strlen($this->data) < mb_strlen($this->min));
            }else{
                $this->result['min'] = !(mb_strlen($this->data) < $this->min);
            }
            return;
        }

        // Se for date-time
        $this->result['min'] = !($this->data < $this->min);
        return;
    }

    /**
     * Configura o valor máximo aceito para o dado.
     *
     * Pode ser um número (comparado diretamente), uma string (quando o
     * dado também é string, compara o comprimento do dado com o
     * comprimento ou o valor numérico do limite) ou uma data/hora
     * (DateTimeInterface, comparada diretamente).
     *
     * Exemplos:
     *
     * ```php
     * // Dado numérico: compara o valor.
     * (new Validator())->max(100)->validate(50);  // true
     * (new Validator())->max(100)->validate(150); // false
     *
     * // Dado string: compara o comprimento.
     * (new Validator())->max(5)->validate('abc');  // true (3 <= 5)
     * (new Validator())->max(2)->validate('abc');  // false (3 > 2)
     * (new Validator())->max('abcd')->validate('ab'); // true (2 <= 4)
     *
     * // Dado data/hora: compara as datas.
     * (new Validator())->max(new DateTimeImmutable('2030-01-01'))
     *     ->validate(new DateTimeImmutable()); // true
     * ```
     *
     * @param int|float|string|DateTimeInterface $max Valor máximo
     *        aceito.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function max(int|float|string|DateTimeInterface $max): self
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Testa a regra de valor máximo configurada em $max.
     *
     * Armazena o resultado em $this->result['max']. Se a regra não foi
     * configurada (null), o resultado permanece null. Se o dado for
     * numérico, compara o valor diretamente; se for string, compara o
     * comprimento (mb_strlen); caso contrário, assume comparação de
     * data/hora.
     *
     * @return void
     */
    private function checkMax(): void
    {
        $this->result['max'] = null;
        // Não testa
        if (is_null($this->max)) {
            return;
        }

        // Se for int ou float
        if (is_numeric($this->data)) {
            $this->result['max'] = !($this->data > $this->max);
            return;
        }

        // Se for string
        if (is_string($this->data)) {
            if(is_string($this->max)){
                $this->result['max'] = !(mb_strlen($this->data) > mb_strlen($this->max));
            }else{
                $this->result['max'] = !(mb_strlen($this->data) > $this->max);
            }
            return;
        }

        // Se for date-time
        $this->result['max'] = !($this->data > $this->max);
        return;
    }

    /**
     * Configura o intervalo (inclusivo) aceito para o dado.
     *
     * $down e $up devem ser exatamente do mesmo tipo (verificado com
     * gettype()); caso contrário, uma RuntimeException é lançada
     * IMEDIATAMENTE ao chamar between(), antes mesmo de validate()
     * ser executado.
     *
     * Os limites são inclusivos: valores iguais aos limites passam na
     * validação.
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->between(1, 10)->validate(5);   // true
     * (new Validator())->between(1, 10)->validate(10);  // true (inclusivo)
     * (new Validator())->between(1, 10)->validate(11);  // false
     *
     * // Dado string: compara o comprimento com os limites.
     * (new Validator())->between(2, 5)->validate('abc'); // true (3)
     *
     * // Erro em tempo de configuração:
     * (new Validator())->between(1, '10'); // lança RuntimeException
     * ```
     *
     * @param int|float|string|DateTimeInterface $down Limite inferior
     *        do intervalo.
     * @param int|float|string|DateTimeInterface $up Limite superior do
     *        intervalo.
     * @return self Instância atual, para encadeamento de métodos.
     * @throws \RuntimeException Se o tipo de $down for diferente do
     *         tipo de $up.
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
     * Testa a regra de intervalo configurada em $betweenDown e
     * $betweenUp.
     *
     * Armazena o resultado em $this->result['between']. Se a regra não
     * foi configurada (algum dos limites for null), o resultado
     * permanece null. Se o dado for numérico, compara o valor
     * diretamente; se for string, compara o comprimento (mb_strlen);
     * caso contrário, assume comparação de data/hora.
     *
     * @return void
     */
    private function checkBetween(): void
    {
        $this->result['between'] = null;
        // Não testa
        if (is_null($this->betweenDown) || is_null($this->betweenUp)) {
            return;
        }

        // Se for int ou float
        if (is_numeric($this->data)) {
            $this->result['between'] = !(($this->data < $this->betweenDown) || ($this->data > $this->betweenUp));
            return;
        }

        // Se for string
        if (is_string($this->data)) {
            if(is_string($this->betweenDown) && is_string($this->betweenUp)){
                $this->result['between'] = !((mb_strlen($this->data) < mb_strlen($this->betweenDown)) || (mb_strlen($this->data) > mb_strlen($this->betweenUp)));
            }else{
                $this->result['between'] = !((mb_strlen($this->data) < $this->betweenDown) || (mb_strlen($this->data) > $this->betweenUp));
            }
            return;
        }

        // Se for date-time
        $this->result['between'] = !(($this->data < $this->betweenDown) || ($this->data > $this->betweenUp));
        return;
    }

    /**
     * Configura a regra de contenção: um valor (ou lista de valores)
     * que o dado deve conter.
     *
     * Se $contains for um array, o dado deve estar presente nesse
     * array (verificado com in_array, sem comparação estrita). Se
     * $contains for uma string, o dado deve conter essa substring
     * (verificado com str_contains).
     *
     * Exemplos:
     *
     * ```php
     * // String: o dado deve conter a substring.
     * (new Validator())->contains('@')->validate('user@example.com'); // true
     * (new Validator())->contains('foo')->validate('bar');            // false
     *
     * // Array: o dado deve estar presente na lista.
     * (new Validator())->contains(['a', 'b', 'c'])->validate('b'); // true
     * (new Validator())->contains(['a', 'b', 'c'])->validate('z'); // false
     * ```
     *
     * @param string|array<mixed> $contains Valor ou lista de valores que o
     *        dado deve conter.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function contains(string|array $contains): self
    {
        $this->contains = $contains;
        return $this;
    }

    /**
     * Testa a regra de contenção configurada em $contains.
     *
     * Armazena o resultado em $this->result['contains']. Se a regra não
     * foi configurada (null), o resultado permanece null. Quando
     * $contains é array, verifica se o dado está no array (in_array);
     * caso contrário, verifica se o dado contém a substring informada
     * (str_contains).
     *
     * @return void
     */
    private function checkContains(): void
    {
        $this->result['contains'] = null;
        // Não testa
        if (is_null($this->contains)) {
            return;
        }

        // Se é array
        if (is_array($this->contains)) {
            $this->result['contains'] = in_array($this->data, $this->contains);
            return;
        }

        // se string
        if(is_string($this->data)){
            $this->result['contains'] = str_contains($this->data, $this->contains);
        }
    }

    /**
     * Configura a regra que verifica se o dado (um caminho) é um
     * arquivo existente.
     *
     * A verificação só é realizada quando $file for true. Ao passar
     * false, nenhuma verificação é executada (o resultado permanece
     * null em result()) — a regra NÃO faz validação negativa.
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->file()->validate('/etc/hosts'); // true (é arquivo)
     * (new Validator())->file()->validate('/tmp');       // false (é diretório)
     * (new Validator())->file()->validate('/no/existe'); // false (não existe)
     * (new Validator())->file(false)->validate('/etc');  // null (não testado)
     * ```
     *
     * @param bool $file Se deve validar o dado como um arquivo
     *        existente.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function file(bool $file = true): self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Testa a regra configurada em $file.
     *
     * Armazena o resultado em $this->result['file']. Se a regra não
     * foi configurada (null) ou for false, o resultado permanece null.
     * Utiliza is_file() para verificar se o caminho informado em
     * $data corresponde a um arquivo existente.
     *
     * @return void
     */
    private function checkFile(): void
    {
        $this->result['file'] = null;
        // Não testa
        if (is_null($this->file) || $this->file === false) {
            return;
        }

        // @phpstan-ignore argument.type
        $this->result['file'] = is_file($this->data);
    }

    /**
     * Configura a regra que verifica se o dado (um caminho) é um
     * diretório existente.
     *
     * A verificação só é realizada quando $directory for true. Passar
     * false não gera nenhum teste (o resultado permanece null em
     * result()).
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->directory()->validate('/var/log'); // true (é diretório)
     * (new Validator())->directory()->validate('/etc/hosts'); // false (é arquivo)
     * (new Validator())->directory(false)->validate('/etc'); // null (não testado)
     * ```
     *
     * @param bool $directory Se deve validar o dado como um diretório
     *        existente.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function directory(bool $directory = true): self
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * Testa a regra configurada em $directory.
     *
     * Armazena o resultado em $this->result['directory']. Se a regra
     * não foi configurada (null) ou for false, o resultado permanece
     * null. Utiliza is_dir() para verificar se o caminho informado em
     * $data corresponde a um diretório existente.
     *
     * @return void
     */
    private function checkDirectory(): void
    {
        $this->result['directory'] = null;
        // Não testa
        if (is_null($this->directory) || $this->directory === false) {
            return;
        }

        // @phpstan-ignore argument.type
        $this->result['directory'] = is_dir($this->data);
    }

    /**
     * Configura a regra que verifica se o dado (um caminho) existe no
     * sistema de arquivos.
     *
     * A verificação só é realizada quando $exists for true. Passar
     * false não gera nenhum teste (o resultado permanece null em
     * result()). file_exists() considera arquivos e diretórios, então
     * esta regra engloba os casos de file() e directory().
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->exists()->validate('/etc/hosts'); // true
     * (new Validator())->exists()->validate('/tmp');       // true (diretório)
     * (new Validator())->exists()->validate('/no/nope');   // false
     * ```
     *
     * @param bool $exists Se deve validar a existência do caminho
     *        informado.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function exists(bool $exists = true): self
    {
        $this->exists = $exists;
        return $this;
    }

    /**
     * Testa a regra configurada em $exists.
     *
     * Armazena o resultado em $this->result['exists']. Se a regra não
     * foi configurada (null) ou for false, o resultado permanece null.
     * Utiliza file_exists() para verificar se o caminho informado em
     * $data existe (seja arquivo ou diretório).
     *
     * @return void
     */
    private function checkExists(): void
    {
        $this->result['exists'] = null;
        // Não testa
        if (is_null($this->exists) || $this->exists === false) {
            return;
        }

        // @phpstan-ignore argument.type
        $this->result['exists'] = file_exists($this->data);
    }

    /**
     * Configura a substring que o dado deve conter no início.
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->startswith('https://')->validate('https://proton.me'); // true
     * (new Validator())->startswith('https://')->validate('http://example.com'); // false
     * ```
     *
     * @param string $substr Substring esperada no início do dado.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function startswith(string $substr): self
    {
        $this->startswith = $substr;
        return $this;
    }

    /**
     * Testa a regra configurada em $startswith.
     *
     * Armazena o resultado em $this->result['startswith']. Se a regra
     * não foi configurada (null), o resultado permanece null. Utiliza
     * str_starts_with() para verificar se o dado começa com a
     * substring informada.
     *
     * @return void
     */
    private function checkStartsWith(): void
    {
        $this->result['startswith'] = null;
        // Não testa
        if (is_null($this->startswith)) {
            return;
        }
        // @phpstan-ignore argument.type
        $this->result['startswith'] = str_starts_with($this->data, $this->startswith);
    }
    
    /**
     * Configura a substring que o dado deve conter no final.
     *
     * Exemplos:
     *
     * ```php
     * (new Validator())->endswith('.pdf')->validate('relatorio.pdf'); // true
     * (new Validator())->endswith('.pdf')->validate('foto.jpg');      // false
     * ```
     *
     * @param string $substr Substring esperada no final do dado.
     * @return self Instância atual, para encadeamento de métodos.
     */
    public function endswith(string $substr): self
    {
        $this->endswith = $substr;
        return $this;
    }

    /**
     * Testa a regra configurada em $endswith.
     *
     * Armazena o resultado em $this->result['endswith']. Se a regra
     * não foi configurada (null), o resultado permanece null. Utiliza
     * str_ends_with() para verificar se o dado termina com a substring
     * informada.
     *
     * @return void
     */
    private function checkEndsWith(): void
    {
        $this->result['endswith'] = null;
        // Não testa
        if (is_null($this->endswith)) {
            return;
        }
        // Testa
        // @phpstan-ignore argument.type
        $this->result['endswith'] = str_ends_with($this->data, $this->endswith);
    }
}