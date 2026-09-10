<?php

namespace Ptk\Validator;

/**
 * Enumeração dos tipos de dados suportados pela classe Validator para
 * validação de tipo através do método Validator::type().
 *
 * Cada caso representa um tipo (ou categoria de tipo) que pode ser
 * testado usando as funções nativas do PHP correspondentes
 * (is_bool(), is_numeric(), is_int(), is_float(), is_string(),
 * is_array(), is_object(), is_resource() e is_callable()).
 *
 * Exemplo de uso:
 *
 * ```php
 * $validator = (new Validator())->type(Types::INT);
 * $validator->validate(10); // true
 * $validator->validate('10'); // false (is_int() não aceita string numérica)
 * ```
 *
 * Exemplo com casos que aceitam strings numéricas:
 *
 * ```php
 * // Types::NUMERIC aceita int, float e string numérica,
 * // pois usa is_numeric():
 * (new Validator())->type(Types::NUMERIC)->validate('3.14'); // true
 * ```
 *
 * @package Ptk\Validator
 */
enum Types
{
    /**
     * Representa o tipo booleano (verificado com is_bool()).
     *
     * Exemplo:
     *
     * ```php
     * (new Validator())->type(Types::BOOL)->validate(true);   // true
     * (new Validator())->type(Types::BOOL)->validate('true'); // false
     * ```
     */
    case BOOL;

    /**
     * Representa um valor numérico, seja inteiro, float ou uma string
     * numérica (verificado com is_numeric()).
     *
     * Exemplo:
     *
     * ```php
     * (new Validator())->type(Types::NUMERIC)->validate(10);    // true
     * (new Validator())->type(Types::NUMERIC)->validate('1.5'); // true
     * (new Validator())->type(Types::NUMERIC)->validate('a1');  // false
     * ```
     */
    case NUMERIC;

    /**
     * Representa o tipo inteiro (verificado com is_int()).
     *
     * Exemplo:
     *
     * ```php
     * (new Validator())->type(Types::INT)->validate(7);   // true
     * (new Validator())->type(Types::INT)->validate(7.0); // false
     * ```
     */
    case INT;

    /**
     * Representa o tipo ponto flutuante (verificado com is_float()).
     *
     * Exemplo:
     *
     * ```php
     * (new Validator())->type(Types::FLOAT)->validate(1.5); // true
     * (new Validator())->type(Types::FLOAT)->validate(1);   // false
     * ```
     */
    case FLOAT;

    /**
     * Representa o tipo string (verificado com is_string()).
     *
     * Exemplo:
     *
     * ```php
     * (new Validator())->type(Types::STRING)->validate('texto'); // true
     * (new Validator())->type(Types::STRING)->validate(123);     // false
     * ```
     */
    case STRING;

    /**
     * Representa o tipo array (verificado com is_array()).
     *
     * Exemplo:
     *
     * ```php
     * (new Validator())->type(Types::ARRAY)->validate([1, 2]); // true
     * (new Validator())->type(Types::ARRAY)->validate('[]');   // false
     * ```
     */
    case ARRAY;
    
    /**
     * Representa o tipo objeto (verificado com is_object()).
     *
     * Exemplo:
     *
     * ```php
     * (new Validator())->type(Types::OBJECT)->validate(new stdClass()); // true
     * (new Validator())->type(Types::OBJECT)->validate('obj');          // false
     * ```
     */
    case OBJECT;

    /**
     * Representa o tipo resource (verificado com is_resource()).
     *
     * Exemplo:
     *
     * ```php
     * $handle = fopen('php://memory', 'r');
     * (new Validator())->type(Types::RESOURCE)->validate($handle); // true
     * ```
     */
    case RESOURCE;

    /**
     * Representa um valor que pode ser chamado como função/método
     * (verificado com is_callable()).
     *
     * Exemplo:
     *
     * ```php
     * (new Validator())->type(Types::CALLABLE)->validate('strlen'); // true
     * (new Validator())->type(Types::CALLABLE)->validate('nao-existe-function-xyz'); // false
     * ```
     */
    case CALLABLE;
}
