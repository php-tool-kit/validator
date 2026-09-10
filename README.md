# PHP Tool Kit - Validator

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.5.7-777BB4?logo=php)](https://www.php.net/)

Biblioteca PHP para validação de dados através de uma **interface fluente** (fluent interface). Permite encadear regras de validação de forma legível e consultar o resultado de cada regra individualmente.

## Índice

- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Uso Básico](#uso-básico)
- [Regras de Validação](#regras-de-validação)
  - [Obrigatoriedade e Nulidade](#obrigatoriedade-e-nulidade)
  - [Tipos](#tipos)
  - [Classes](#classes)
  - [Limites e Intervalos](#limites-e-intervalos)
  - [Conteúdo](#conteúdo)
  - [Sistema de Arquivos](#sistema-de-arquivos)
  - [Strings](#strings)
- [Consultando Resultados](#consultando-resultados)
- [Desenvolvimento](#desenvolvimento)
- [Licença](#licença)

## Requisitos

- PHP >= 8.5.7
- Extensão `mbstring`

## Instalação

Via Composer:

```bash
composer require php-tool-kit/validator
```

## Uso Básico

```php
<?php
use Ptk\Validator\Validator;
use Ptk\Validator\Types;

$validator = (new Validator())
    ->required()
    ->type(Types::STRING)
    ->greatOrEqual(3)
    ->lessOrEqual(20);

if ($validator->validate('everton')) {
    echo "Valor válido!";
} else {
    print_r($validator->failed());
}
```

## Regras de Validação

Todas as regras são encadeáveis e retornam a própria instância (`self`).

### Obrigatoriedade e Nulidade

#### `required(bool $required = true)`

Define se o valor é obrigatório. Falha se o valor for nulo ou "vazio" (conforme `empty()` do PHP).

```php
(new Validator())->required()->validate('oi');  // true
(new Validator())->required()->validate('');    // false
(new Validator())->required()->validate(null);  // false
```

#### `empty(bool $empty = true)`

Define se o valor **pode** ser vazio. Aplicável apenas a `string` e `array`.

```php
(new Validator())->empty(false)->validate('');   // false
(new Validator())->empty(false)->validate('ok'); // true
(new Validator())->empty(true)->validate([]);    // true
```

#### `nullable(bool $nullable = true)`

Define se o valor pode ser `null`.

```php
(new Validator())->nullable(false)->validate(null); // false
(new Validator())->nullable(true)->validate(null);  // true
```

### Tipos

#### `type(Types $type)`

Valida o tipo do valor usando as funções nativas do PHP. Os tipos disponíveis estão no enum `Ptk\Validator\Types`:

| Tipo | Função PHP | Descrição |
|------|------------|-----------|
| `Types::BOOL` | `is_bool()` | Booleano |
| `Types::NUMERIC` | `is_numeric()` | Numérico (int, float ou string numérica) |
| `Types::INT` | `is_int()` | Inteiro |
| `Types::FLOAT` | `is_float()` | Ponto flutuante |
| `Types::STRING` | `is_string()` | String |
| `Types::ARRAY` | `is_array()` | Array |
| `Types::OBJECT` | `is_object()` | Objeto |
| `Types::RESOURCE` | `is_resource()` | Resource |
| `Types::CALLABLE` | `is_callable()` | Chamável (função/método) |

```php
use Ptk\Validator\Types;

(new Validator())->type(Types::INT)->validate(10);       // true
(new Validator())->type(Types::INT)->validate('10');      // false
(new Validator())->type(Types::NUMERIC)->validate('3.14');// true
(new Validator())->type(Types::STRING)->validate('oi');   // true
```

### Classes

#### `is(string $className)`

Valida se o valor é uma instância **exata** da classe informada (usa `get_class()`, não `instanceof`). Aplicável apenas a objetos.

```php
(new Validator())->is(stdClass::class)->validate(new stdClass()); // true
(new Validator())->is(stdClass::class)->validate(new Exception()); // false
```

### Limites e Intervalos

As regras de comparação adaptam-se ao tipo do valor validado:

- **Números** (`int`/`float`): comparados por valor
- **Strings**: comparadas pelo comprimento (`mb_strlen`)
- **Arrays**: comparados pelo número de elementos (`sizeof`)
- **DateTimeInterface**: comparadas cronologicamente

#### `lessOrEqual($value)` — menor ou igual (`<=`)

```php
(new Validator())->lessOrEqual(10)->validate(5);     // true
(new Validator())->lessOrEqual(5)->validate('oi');   // true (strlen = 2)
(new Validator())->lessOrEqual(3)->validate([1, 2]); // true
```

#### `less($value)` — estritamente menor (`<`)

```php
(new Validator())->less(10)->validate(9); // true
```

> **Obs.**: para `DateTimeInterface`, a comparação é inclusiva (`<=`).

#### `greatOrEqual($value)` — maior ou igual (`>=`)

```php
(new Validator())->greatOrEqual(3)->validate('everton'); // true (strlen = 7)
```

#### `great($value)` — estritamente maior (`>`)

```php
(new Validator())->great(5)->validate(10); // true
```

#### `between($down, $up)` — intervalo inclusivo

Os limites devem ser do **mesmo tipo**, caso contrário uma `RuntimeException` é lançada.

```php
(new Validator())->between(1, 10)->validate(5);          // true
(new Validator())->between(3, 20)->validate('everton');  // true (strlen = 7)
(new Validator())->between('a', 'zzz')->validate('oi');  // true (compara comprimentos)
```

### Conteúdo

#### `contains(string|array $contains)`

- Se o valor for `string`: verifica substring (`str_contains`).
- Se o valor for `array`: verifica presença com `in_array` (comparação não estrita).

```php
(new Validator())->contains('ton')->validate('everton'); // true
(new Validator())->contains('foo')->validate(['foo', 'bar']); // true
```

### Sistema de Arquivos

As regras abaixo só executam quando o valor validado é uma `string` (caminho).

#### `file(bool $file = true)`

Valida se o caminho é um arquivo existente (`is_file`).

```php
(new Validator())->file()->validate('/etc/hosts'); // true (se existir)
```

#### `directory(bool $directory = true)`

Valida se o caminho é um diretório existente (`is_dir`).

```php
(new Validator())->directory()->validate('/tmp'); // true
```

#### `exists(bool $exists = true)`

Valida se o caminho existe no sistema de arquivos (`file_exists`).

```php
(new Validator())->exists()->validate('/algum/caminho');
```

### Strings

#### `startswith(string $substr)`

Valida se a string começa com a substring informada (`str_starts_with`).

```php
(new Validator())->startswith('php')->validate('php-tool-kit'); // true
```

#### `endswith(string $substr)`

Valida se a string termina com a substring informada (`str_ends_with`).

```php
(new Validator())->endswith('.md')->validate('README.md'); // true
```

## Consultando Resultados

Após chamar `validate($valor)`, você pode consultar os resultados:

### `result(): array`

Retorna o estado de **todas** as regras, configuradas ou não:

- `true` — regra passou
- `false` — regra falhou
- `null` — regra não foi configurada

```php
$validator = (new Validator())->required()->type(Types::INT);
$validator->validate('abc');
print_r($validator->result());
// ['required' => true, 'type' => false]
```

### `passed(): array`

Lista apenas os nomes das regras que **passaram**.

```php
$validator->passed(); // ['required']
```

### `failed(): array`

Lista apenas os nomes das regras que **falharam**.

```php
$validator->failed(); // ['type']
```

### Retorno de `validate()`

O método `validate()` retorna `true` se **nenhuma** regra configurada falhou, e `false` caso contrário.

## Desenvolvimento

### Instalando dependências

```bash
composer install
```

### Scripts disponíveis

| Comando | Descrição |
|---------|-----------|
| `composer test` | Executa testes com Pest + cobertura + type-coverage |
| `composer static` | Análise estática com PHPStan |
| `composer code` | Corrige e verifica PSR-1/PSR-2/PSR-12 |
| `composer fix-code` | Aplica correções automáticas (PHP_CodeSniffer) |
| `composer psr-code` | Verifica conformidade com PSR |

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

## Autor

**Everton da Rosa** — <everton3x@gmail.com>  
<https://everton3x.github.io>

## Links

- [Homepage](https://php-tool-kit.github.io/validator)
- [Repositório](https://github.com/php-tool-kit/validator)
- [Issues](https://github.com/php-tool-kit/validator/issues)

---

Documentação criada com auxílio de I.A.