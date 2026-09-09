<?php
// @codeCoverageIgnoreStart
namespace Ptk\Validator;

/**
 * Enumeração dos tipos de dados suportados pela classe Validator para
 * validação de tipo através do método Validator::type().
 *
 * Cada caso representa um tipo (ou categoria de tipo) que pode ser testado
 * usando as funções nativas do PHP correspondentes (is_bool, is_numeric,
 * is_int, is_float, is_string, is_array, is_object, is_resource e
 * is_callable).
 *
 * Documentação gerada com auxílio de inteligência artificial
 *
 * @package Ptk\Validator
 */
enum Types
{
    /**
     * Representa o tipo booleano (verificado com is_bool()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case BOOL;

    /**
     * Representa um valor numérico, seja inteiro, float ou uma string
     * numérica (verificado com is_numeric()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case NUMERIC;

    /**
     * Representa o tipo inteiro (verificado com is_int()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case INT;

    /**
     * Representa o tipo ponto flutuante (verificado com is_float()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case FLOAT;

    /**
     * Representa o tipo string (verificado com is_string()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case STRING;

    /**
     * Representa o tipo array (verificado com is_array()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case ARRAY;

    /**
     * Representa o tipo objeto (verificado com is_object()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case OBJECT;

    /**
     * Representa o tipo resource (verificado com is_resource()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case RESOURCE;

    /**
     * Representa um valor que pode ser chamado como função/método
     * (verificado com is_callable()).
     *
     * Documentação gerada com auxílio de inteligência artificial
     */
    case CALLABLE;
}
// @codeCoverageIgnoreEnd