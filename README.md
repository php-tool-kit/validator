# Validator

Utilitário para validar valores em variáveis PHP, através de uma interface fluente (fluent interface) de configuração de regras.

> Documentação gerada com auxílio de inteligência artificial

## Requisitos

- PHP >= 8.5.7
- mb_string

## Instalação

```bash
composer require php-tool-kit/validator
```

## Visão geral

O pacote é composto por dois elementos principais, no namespace `Ptk\Validator`:

- **`Validator`**: classe principal, responsável por configurar e executar as regras de validação sobre um valor.
- **`Types`**: enum com os tipos de dados suportados pela regra de verificação de tipo (`type()`).

O fluxo básico de uso é:

1. Instanciar `Validator`;
2. Encadear os métodos das regras desejadas (`required()`, `empty()`, `nullable()`, `type()`, `is()`, `min()`, `max()`, `between()`, `contains()`, `file()`, `directory()`, `exists()`, `startswith()`, `endswith()`);
3. Chamar `validate($valor)` para executar as regras configuradas;
4. Consultar o resultado com `result()`, `passed()` ou `failed()`.

## Exemplo básico

```php
<?php

use Ptk\Validator\Validator;
use Ptk\Validator\Types;

$validator = (new Validator())
    ->required()
    ->type(Types::STRING)
    ->min(3)
    ->max(20);

$ok = $validator->validate('everton');

if ($ok) {
    echo "Valor válido!";
} else {
    print_r($validator->failed());
}
```

## Regras disponíveis

| Método | Descrição |
| --- | --- |
| `required(bool $required = true)` | Define se o valor é obrigatório (não pode ser nulo nem ter comprimento zero). |
| `empty(bool $empty = true)` | Define se o valor pode ser vazio (mas não nulo, quando `true`); se `false`, o valor não pode ser nulo nem vazio. |
| `nullable(bool $nullable = true)` | Define se o valor pode ser nulo. |
| `type(Types $type)` | Define o tipo esperado do valor, de acordo com o enum `Types`. |
| `is(string $className)` | Define que o valor deve ser uma instância da classe informada — comparação **exata** de classe (não usa `instanceof`); veja [Observações importantes](#observações-importantes). |
| `min(int\|float\|string\|DateTimeInterface $min)` | Define o valor mínimo aceito (número, comprimento de string ou data/hora). |
| `max(int\|float\|string\|DateTimeInterface $max)` | Define o valor máximo aceito (número, comprimento de string ou data/hora). |
| `between($down, $up)` | Define um intervalo (inclusivo) aceito para o valor. `$down` e `$up` devem ser do mesmo tipo, caso contrário uma `RuntimeException` é lançada imediatamente. |
| `contains(string\|array $contains)` | Define um valor (ou lista de valores) que o valor validado deve conter. |
| `file(bool $file)` | Valida se o valor (um caminho) é um arquivo existente. |
| `directory(bool $directory)` | Valida se o valor (um caminho) é um diretório existente. |
| `exists(bool $exists)` | Valida se o valor (um caminho) existe no sistema de arquivos. |
| `startswith(string $substr)` | Valida se o valor começa com a substring informada. |
| `endswith(string $substr)` | Valida se o valor termina com a substring informada. |

### Exemplos por regra

**required()** — o valor não pode ser nulo nem ter comprimento zero:

```php
(new Validator())->required()->validate('abc');  // true
(new Validator())->required()->validate(null);   // false
(new Validator())->required()->validate('');     // false
```

**empty()** — com `true`, o valor pode ser vazio, mas não nulo; com `false`, não pode ser nulo nem vazio:

```php
(new Validator())->empty()->validate('');    // true
(new Validator())->empty()->validate(null);  // false
(new Validator())->empty(false)->validate([]);  // false
(new Validator())->empty(false)->validate('ok'); // true
```

**nullable()** — controla se o valor pode ser nulo:

```php
(new Validator())->nullable()->validate(null);      // true
(new Validator())->nullable(false)->validate(null); // false
```

**type()** — verifica o tipo do valor com o enum `Types`:

```php
(new Validator())->type(Types::INT)->validate(10);    // true
(new Validator())->type(Types::INT)->validate('10');  // false
(new Validator())->type(Types::NUMERIC)->validate('1.5'); // true
```

**is()** — verifica a classe do objeto (comparação exata):

```php
(new Validator())->is(DateTime::class)->validate(new DateTime()); // true
```

**min() / max() / between()** — números comparados por valor, strings comparadas por comprimento (`mb_strlen`) e datas comparadas entre si:

```php
(new Validator())->min(10)->validate(15);            // true
(new Validator())->min(10)->max(20)->validate(25);   // false
(new Validator())->min(3)->validate('abcd');         // true (comprimento 4 >= 3)
(new Validator())->max('eeee')->validate('ab');      // true (2 <= 4)
(new Validator())->between(1, 10)->validate(5);      // true (inclusivo)
(new Validator())->between(new DateTime('2020-01-01'), new DateTime('2030-12-31'))
    ->validate(new DateTime());                      // true
```

**contains()** — com string, verifica substring; com array, verifica se o valor está presente na lista:

```php
(new Validator())->contains('@')->validate('user@example.com');   // true
(new Validator())->contains(['a', 'b', 'c'])->validate('b');      // true
```

**file() / directory() / exists()** — verificam caminhos no sistema de arquivos:

```php
(new Validator())->file()->validate('/etc/hosts');       // true
(new Validator())->directory()->validate('/var/log');    // true
(new Validator())->exists()->validate('/etc/hosts');     // true
(new Validator())->exists()->validate('/caminho/inexistente'); // false
```

**startswith() / endswith()** — verificam o início e o fim de strings:

```php
(new Validator())->startswith('https://')->validate('https://proton.me'); // true
(new Validator())->endswith('.pdf')->validate('relatorio.pdf');           // true
```

## Tipos suportados (enum `Types`)

| Caso | Verificação nativa correspondente |
| --- | --- |
| `Types::BOOL` | `is_bool()` |
| `Types::NUMERIC` | `is_numeric()` |
| `Types::INT` | `is_int()` |
| `Types::FLOAT` | `is_float()` |
| `Types::STRING` | `is_string()` |
| `Types::ARRAY` | `is_array()` |
| `Types::OBJECT` | `is_object()` |
| `Types::RESOURCE` | `is_resource()` |
| `Types::CALLABLE` | `is_callable()` |

## Consultando o resultado

Após chamar `validate()`, é possível consultar o resultado de três formas:

- **`result(): array`** — retorna o resultado de todas as regras configuradas ou não. Cada chave é o nome da regra (ex.: `required`, `type`, `min`, etc.) e o valor pode ser `true` (passou), `false` (falhou) ou `null` (regra não configurada, portanto não testada).
- **`passed(): array`** — retorna apenas as regras cujo resultado foi `true`.
- **`failed(): array`** — retorna apenas as regras cujo resultado foi `false` (regras não configuradas, com valor `null`, não são consideradas falhas).

```php
$validator->validate($valor);

$validator->result();  // resultado completo
$validator->passed();  // apenas o que passou
$validator->failed();  // apenas o que falhou
```

Exemplo:

```php
$validator = (new Validator())->required()->min(3);
$validator->validate('ab');

print_r($validator->result());
// ['required' => true, 'empty' => null, 'nullable' => null, ..., 'min' => false, ...]

print_r($validator->passed()); // ['required']
print_r($validator->failed()); // ['min']
```

## Observações importantes

- **Restrição de `is()`**: a validação compara o nome da classe do valor **exatamente** com o nome informado (via `get_class()`), e não usa `instanceof`. Isso significa que instâncias de **subclasses** da classe informada **falham** na validação — só passam objetos cuja classe seja idêntica a `$className`.

  ```php
  class Animal {}
  class Cachorro extends Animal {}

  (new Validator())->is(Animal::class)->validate(new Cachorro());
  // false, pois Cachorro !== Animal, mesmo sendo subclasse
  ```

- **`between()` lança exceção em tempo de configuração**: `$down` e `$up` precisam ser exatamente do mesmo tipo (`gettype($down) === gettype($up)`). Se forem de tipos diferentes, uma `\RuntimeException` é lançada **imediatamente ao chamar `between()`**, antes mesmo de `validate()` ser executado — portanto esse erro não aparece em `result()`/`failed()`, e sim como uma exceção não capturada caso não seja tratada.

  ```php
  (new Validator())->between(1, '10'); // lança RuntimeException, tipos diferentes (int vs string)
  (new Validator())->between(1, 10);   // OK, ambos int
  ```

- Os métodos `file()`, `directory()` e `exists()` só executam a verificação quando o parâmetro é `true`; passar `false` não gera nenhum teste (o resultado permanece `null` em `result()`), e não representa uma validação negativa.

- **Comparações de `min()`, `max()` e `between()` são sensíveis ao tipo do dado**: dado numérico é comparado por valor, string é comparada pelo comprimento (`mb_strlen`) e valores de data/hora são comparados entre si. Um dado `null` falha nas regras de tipo e nas verificações de comprimento.

- **`contains()` com array não usa comparação estrita**: a verificação usa `in_array()` sem comparação estrita, portanto `'1'` e `1` são considerados equivalentes.

## Testes e qualidade de código

O `composer.json` do projeto já define alguns scripts úteis:

```bash
composer test    # executa os testes com Pest e checagem de cobertura de tipos
composer static  # executa a análise estática com PHPStan
composer code    # corrige e verifica o código conforme os padrões PSR-1, PSR-2 e PSR-12
```

## Autor

- **Everton da Rosa** — everton3x@gmail.com — [everton3x.github.io](https://everton3x.github.io)

## Licença

MIT
